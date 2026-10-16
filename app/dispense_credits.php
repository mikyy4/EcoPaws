<?php
// Start a "credits-only" visit: dispense saved food credits.
require_once __DIR__ . '/../includes/bootstrap.php';
if (pool_total() < CREDITS_PER_PORTION) redirect('credits.php');
begin_tx();
redirect('choose.php');
