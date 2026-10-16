<?php
/**
 * Transaction + reward logic shared by every step of the recycling flow.
 *
 * Food credits: 1 credit is stored per valid bottle, and 1 credit = 1 food portion (CREDITS_PER_PORTION).
 * Credits that are not used (user pressed NOT NOW, or took fewer portions than earned) stay
 * 'stored' and form ONE shared pool in the machine. Anyone can use the pool on a later visit.
 * Dental sticks are a personal reward: 1 stick per <per> valid bottles deposited in THIS visit (<per> = bottles_per_stick).
 */

// ---------- Transaction lifecycle ----------

/** The current OPEN transaction of this browser session, or null. */
function open_tx(): ?array {
    if (empty($_SESSION['tx'])) return null;
    return query("SELECT * FROM transactions WHERE id = ? AND status = 'open'", [$_SESSION['tx']])->fetch() ?: null;
}

/** Same as open_tx() but sends the visitor back to the welcome screen when there is none. */
function require_open_tx(): array {
    return open_tx() ?? redirect('index.php');
}

/** Mark the session's unfinished transaction as cancelled. */
function cancel_open_tx(): void {
    if (!empty($_SESSION['tx'])) {
        query("UPDATE transactions SET status='cancelled', completed_at=NOW() WHERE id=? AND status='open'", [$_SESSION['tx']]);
    }
}

/** Cancel any unfinished transaction and open a fresh one. */
function begin_tx(): int {
    cancel_open_tx();
    db()->exec("INSERT INTO transactions (status) VALUES ('open')");
    return $_SESSION['tx'] = (int)db()->lastInsertId();
}

// ---------- Reward math ----------

/** All stored (unclaimed) credits in the machine. */
function pool_total(): int {
    return (int)scalar("SELECT COALESCE(SUM(credits),0) FROM food_credits WHERE status='stored'");
}

/** [bottles counted toward the next reward, percent of the progress bar] */
function reward_progress(int $valid, int $per): array {
    $toward = ($valid > 0 && $valid % $per === 0) ? $per : $valid % $per;
    return [$toward, (int)round($toward / $per * 100)];
}

function tx_numbers(array $tx): array {
    ['per' => $per, 'g' => $g] = reward_rules();
    $n     = (int)$tx['valid_bottles'];
    $mine  = (int)scalar("SELECT COALESCE(SUM(credits),0) FROM food_credits WHERE transaction_id=? AND status='stored'", [$tx['id']]);
    $total = pool_total();                          // everything stored in the machine (incl. this visit)
    return [
        'valid'    => $n,
        'rejected' => (int)$tx['invalid_attempts'],
        'per'      => $per,                         // bottles per dental stick
        'cpp'      => CREDITS_PER_PORTION,          // credits per food portion
        'g'        => $g,
        'due'      => intdiv($n, $per),             // dental sticks earned this visit
        'credits'  => $mine,                        // credits from this visit
        'pool'     => max(0, $total - $mine),       // saved by earlier users
        'total'    => $total,
        'avail'    => intdiv($total, CREDITS_PER_PORTION),   // portions the credits can pay for
        'left'     => $total % CREDITS_PER_PORTION,  // credits not yet enough for another portion
    ];
}

/** A "credits-only" visit: came from the Saved credits page, no bottle inserted. */
function credits_only(array $tx): bool {
    return (int)$tx['valid_bottles'] === 0 && (int)$tx['invalid_attempts'] === 0;
}

/** Where the Back/gate buttons of the reward pages should go. */
function reward_back(array $tx): string {
    return credits_only($tx) ? 'cancel.php?to=credits' : 'summary.php';
}

function food_type_valid($type): bool { return $type === 'dog' || $type === 'cat'; }
function food_label(string $type): string { return $type === 'cat' ? 'Cat food' : 'Dog food'; }

/** How many portions of $type can be dispensed right now (limited by credits AND food stock). */
function max_portions(string $type, array $s): int {
    $stock = (int)scalar('SELECT quantity FROM inventory WHERE item_key=?', [$type . '_food']);
    return max(0, min($s['avail'], intdiv($stock, $s['g'])));
}

/**
 * Finish an open transaction.
 *   $type = 'dog' | 'cat', $wanted = N  -> YES: dispense up to N portions (+ dental sticks)
 *   $type = null                        -> NOT NOW: dispense dental sticks only, KEEP all credits stored
 * Returns false when the transaction was already finished (double-submit / back button).
 */
function finalize_tx(array $tx, ?string $type = null, int $wanted = 0): bool {
    ['per' => $per, 'g' => $g] = reward_rules();
    $due = intdiv((int)$tx['valid_bottles'], $per);

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $lock = $pdo->prepare('SELECT status FROM transactions WHERE id=? FOR UPDATE');
        $lock->execute([$tx['id']]);
        if ($lock->fetchColumn() !== 'open') { $pdo->rollBack(); return false; }

        $inv = $pdo->query('SELECT item_key, quantity FROM inventory FOR UPDATE')->fetchAll(PDO::FETCH_KEY_PAIR);

        // Dental sticks are given either way
        $sticks = min($due, (int)($inv['dental_stick'] ?? 0));
        if ($sticks > 0) {
            $pdo->prepare('INSERT INTO reward_log (transaction_id, dental_sticks) VALUES (?,?)')->execute([$tx['id'], $sticks]);
            $pdo->prepare("UPDATE inventory SET quantity = quantity - ? WHERE item_key='dental_stick'")->execute([$sticks]);
        }

        // Food is only dispensed when the user said YES
        $portions = 0;
        if (food_type_valid($type) && $wanted > 0) {
            $foodKey   = $type . '_food';
            $available = (int)($inv[$foodKey] ?? 0);
            $total     = (int)$pdo->query("SELECT COALESCE(SUM(credits),0) FROM food_credits WHERE status='stored' FOR UPDATE")->fetchColumn();
            $portions  = max(0, min($wanted, intdiv($total, CREDITS_PER_PORTION), intdiv($available, $g)));
            if ($portions > 0) {
                $pdo->prepare('INSERT INTO feeding_log (transaction_id, food_type, portions, grams) VALUES (?,?,?,?)')
                    ->execute([$tx['id'], $type, $portions, $portions * $g]);
                $pdo->prepare('UPDATE inventory SET quantity = quantity - ? WHERE item_key = ?')->execute([$portions * $g, $foodKey]);
                // spend this visit's credits first, then the oldest saved credits
                $pdo->prepare("UPDATE food_credits SET status='claimed', claimed_at=NOW()
                               WHERE status='stored' ORDER BY (transaction_id = ?) DESC, id ASC LIMIT " . (int)($portions * CREDITS_PER_PORTION))
                    ->execute([$tx['id']]);
            }
        }

        $pdo->prepare("UPDATE transactions SET status='completed', completed_at=NOW(),
                       dental_sticks=?, food_portions=?, food_type=? WHERE id=?")
            ->execute([$sticks, $portions, food_type_valid($type) ? $type : null, $tx['id']]);
        $pdo->commit();
        return true;
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $ex;
    }
}

/**
 * Record one scanned item on an open transaction.
 * Returns ['ok'=>false,'error'=>...] or ['ok'=>true,'valid'=>bool,'reason'=>?string,'entry_id'=>int,'counts'=>[...]].
 */
function record_bottle(array $tx, int $idx): array {
    $bottles = load_bottles();
    if (!isset($bottles[$idx])) return ['ok' => false, 'error' => 'The scanner could not read the item. Please try again.'];

    $b     = $bottles[$idx];
    $valid = ($b['reason'] === null);
    $pdo   = db();

    if ($valid) {   // bottle bin full? (staff must retrieve the bottles)
        $bin = $pdo->query("SELECT quantity, capacity FROM inventory WHERE item_key='bottle_bin'")->fetch();
        if ($bin && $bin['quantity'] >= $bin['capacity']) {
            return ['ok' => false, 'error' => 'The bottle bin is full. Please inform the barangay staff.'];
        }
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT INTO bottle_entries (transaction_id, category, item_name, size, bottle_class, color, is_valid, reject_reason)
                       VALUES (?,?,?,?,?,?,?,?)')
            ->execute([$tx['id'], $b['cat'], $b['name'], $b['size'], $b['cls'], $b['color'], $valid ? 1 : 0, $b['reason']]);
        $entryId = (int)$pdo->lastInsertId();

        if ($valid) {
            $pdo->prepare('UPDATE transactions SET valid_bottles = valid_bottles + 1 WHERE id=?')->execute([$tx['id']]);
            $pdo->exec("UPDATE inventory SET quantity = quantity + 1 WHERE item_key='bottle_bin'");
            $pdo->prepare("INSERT INTO food_credits (transaction_id, entry_id, credits, status) VALUES (?,?,1,'stored')")
                ->execute([$tx['id'], $entryId]);
        } else {
            $pdo->prepare('UPDATE transactions SET invalid_attempts = invalid_attempts + 1 WHERE id=?')->execute([$tx['id']]);
        }
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $ex;
    }

    $t = query('SELECT valid_bottles, invalid_attempts FROM transactions WHERE id=?', [$tx['id']])->fetch();
    $per = reward_rules()['per'];
    $n = (int)$t['valid_bottles'];
    [$toward, $pct] = reward_progress($n, $per);

    return ['ok' => true, 'valid' => $valid, 'reason' => $b['reason'], 'entry_id' => $entryId,
            'counts' => ['valid' => $n, 'rejected' => (int)$t['invalid_attempts'], 'toward' => $toward, 'per' => $per, 'pct' => $pct]];
}
