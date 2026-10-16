<?php
/**
 * Smart scanner bridge: matches a sensor reading to an entry of data/bottles.json
 * and passes readings from the hardware (api/scan.php?action=push) to the deposit page (poll).
 */

/** Index of the "unrecognized item" entry in bottles.json (used when nothing matches). */
function scan_unknown_idx(): int {
    foreach (load_bottles() as $i => $b) if ($b['cls'] === 'Unknown') return $i;
    return 0;
}

/** Match a sensor reading (bottle length in cm + optional color) to the closest known bottle. */
function scan_match(float $len, string $color = ''): int {
    $best = null; $bestD = INF;
    foreach (load_bottles() as $i => $b) {
        if (!is_numeric($b['len'])) continue;                             // skip the "Other (not on the list)" rows
        if ($color !== '' && stripos($b['color'], $color) === false) continue;
        $d = abs((float)$b['len'] - $len);
        if ($d < $bestD) { $bestD = $d; $best = $i; }
    }
    return ($best !== null && $bestD <= SCAN_TOLERANCE_CM) ? $best : scan_unknown_idx();
}

function scan_save(int $idx): void {
    file_put_contents(SCAN_FILE, json_encode(['idx' => $idx, 't' => time()]), LOCK_EX);
}

/** Returns the newest reading once (then clears it), or null when there is none / it is stale. */
function scan_take(): ?int {
    if (!is_file(SCAN_FILE)) return null;
    $r = json_decode((string)file_get_contents(SCAN_FILE), true);
    @unlink(SCAN_FILE);                                                  // each reading is used once
    if (!$r || time() - (int)$r['t'] > SCAN_MAX_AGE_SECONDS || !isset(load_bottles()[(int)$r['idx']])) return null;
    return (int)$r['idx'];
}
