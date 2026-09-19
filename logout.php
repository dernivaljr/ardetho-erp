<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';

realizarLogout();

header('Location: login.php');
exit;
