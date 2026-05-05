<?php
require 'c:/xampp/htdocs/template/vendor/autoload.php';
use WebAuthn\WebAuthn;
$webauthn = new WebAuthn('test', 'localhost', ['packed']);
$args = $webauthn->getGetArgs();
echo json_encode($args);
