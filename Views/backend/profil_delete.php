<?php
include '../../Controllers/ProfilController.php';

$profilC = new ProfilController();

if (isset($_GET['id'])) {
    $profilC->deleteProfil($_GET['id']);
}

header("Location: profil_list.php");
exit();
?>
