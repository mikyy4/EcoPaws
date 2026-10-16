<?php

require_once __DIR__ . '/../includes/bootstrap.php';

$ajax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../app/deposit.php');

$tx = open_tx();
if (!$tx) {
    if ($ajax) json_out(['ok' => false, 'error' => 'Your session has ended. Please tap Start again.']);
    redirect('../app/index.php');
}

$idx = filter_input(INPUT_POST, 'idx', FILTER_VALIDATE_INT);
$result = record_bottle($tx, $idx === false || $idx === null ? -1 : $idx);

if ($ajax) {
    json_out($result['ok']
        ? ['ok' => true, 'valid' => $result['valid'], 'reason' => $result['reason'], 'counts' => $result['counts']]
        : ['ok' => false, 'error' => $result['error']]);
}

if (!$result['ok']) { flash_set($result['error'], 'bad'); redirect('../app/deposit.php'); }
if ($result['valid']) {
    $b = load_bottles()[$idx];
    flash_set('Accepted: ' . $b['name'] . ' (' . $b['size'] . '). Insert the next bottle or press DONE.');
    redirect('../app/deposit.php');
}
redirect('../app/invalid.php?id=' . $result['entry_id']);
