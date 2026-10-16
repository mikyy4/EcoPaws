<?php

require_once __DIR__ . '/../includes/bootstrap.php';

$action  = $_GET['action'] ?? 'poll';
$bottles = load_bottles();

if ($action === 'push') {
    if (!hash_equals(SCANNER_KEY, (string)($_REQUEST['key'] ?? ''))) json_out(['ok' => false, 'error' => 'bad key'], 403);
    $given = $_REQUEST['idx'] ?? null;
    $idx = (is_numeric($given) && isset($bottles[(int)$given]))
        ? (int)$given
        : scan_match((float)($_REQUEST['len'] ?? 0), trim((string)($_REQUEST['color'] ?? '')));
    scan_save($idx);
    json_out(['ok' => true, 'idx' => $idx]);
}

if ($action === 'simulate') {
    if (!SCANNER_DEMO || $_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false], 403);
    $v = $_POST['idx'] ?? 'random';
    $idx = ($v === 'random' || !isset($bottles[(int)$v])) ? array_rand($bottles) : (int)$v;
    scan_save($idx);
    json_out(['ok' => true, 'idx' => $idx]);
}

// poll
if (!open_tx()) json_out(['found' => false]);
$idx = scan_take();
json_out($idx === null ? ['found' => false] : ['found' => true, 'idx' => $idx]);
