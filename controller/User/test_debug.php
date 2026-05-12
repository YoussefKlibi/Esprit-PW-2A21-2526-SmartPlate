<?php
session_start();
$_GET['action'] = 'registerChallenge';
$_SESSION['user_email'] = 'ilyesgaied32@gmail.com'; // Admin user in his DB usually
require 'c:/xampp/htdocs/template/Controllers/WebAuthnController.php';
