<?php
require_once __DIR__ . '/../includes/bootstrap.php';
cancel_open_tx();
unset($_SESSION['tx']);
redirect(($_GET['to'] ?? '') === 'credits' ? 'credits.php' : 'index.php');
