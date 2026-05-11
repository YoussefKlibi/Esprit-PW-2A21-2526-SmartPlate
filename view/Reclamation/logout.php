<?php
require_once __DIR__ . '/../config/auth.php';

// Déconnexion et redirection vers l'accueil public
logoutCurrentUser('index.php');
// logoutCurrentUser() appelle exit après la redirection
