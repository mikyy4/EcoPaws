<?php
// YES path: dispense food (clamped to credits + stock) plus dental sticks.
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('summary.php');
$tx = require_open_tx();

$type = $_POST['food_type'] ?? '';
$p    = (int)($_POST['portions'] ?? 0);
if (!food_type_valid($type)) redirect('choose.php');
if ($p < 1) redirect('portions.php?food_type=' . $type);

finalize_tx($tx, $type, $p);
redirect('dispensing.php');
