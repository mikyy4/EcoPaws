<?php
require_once __DIR__ . '/../includes/bootstrap.php';
begin_tx();                       // closes any unfinished transaction first
redirect('deposit.php');
