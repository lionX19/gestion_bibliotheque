<?php
// Inclure le fichier d'authentification de base
require_once __DIR__ . '/auth.php';

// Vérifier si l'utilisateur est un administrateur
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Rediriger si l'utilisateur n'est pas un administrateur
function requireAdmin() {
    requireLogin(); // D'abord vérifier si l'utilisateur est connecté
    
    if (!isAdmin()) {
        // Rediriger vers une page d'erreur ou d'accès refusé
        header("Location: " . getBaseUrl() . "access_denied.php");
        exit;
    }
}
?>