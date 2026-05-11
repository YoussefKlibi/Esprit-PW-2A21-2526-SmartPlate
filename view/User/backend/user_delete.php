<?php
require_once __DIR__ . '/../../../controller/User/UserController.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $userC = new UserController();
    $userC->deleteUser($id);
}

// Redirection vers la liste après suppression
header("Location: user_list.php");
exit();
?>
