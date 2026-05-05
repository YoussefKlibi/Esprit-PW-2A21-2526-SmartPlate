<?php
require 'UserController.php';
$c = new UserController();
$res = $c->logConnection(47, '127.0.0.1', 'test_agent', 'Success');
print_r($res);
