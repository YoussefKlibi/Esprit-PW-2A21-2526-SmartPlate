<?php
session_start();
$_SESSION['user_email'] = 'ilyesgaied32@gmail.com';
$_GET['id'] = 45;
$_GET['action'] = 'ban';
require 'user_ban.php';
