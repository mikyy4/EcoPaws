<?php
// NOT NOW path: finish the transaction, keep the earned food credits stored.
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('summary.php');
finalize_tx(require_open_tx(), null);
redirect('dispensing.php');
