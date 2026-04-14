<?php
require_once __DIR__ . '/../config/auth.php';

logoutCurrentUser();
header('Location: login.php');
exit;
