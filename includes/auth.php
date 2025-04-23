<?php
// Vérifie si l'utilisateur est connecté
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Redirige vers la page de connexion si non connecté
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

// Vérifie si l'utilisateur est un administrateur
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}
