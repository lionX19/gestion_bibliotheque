<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../index.php");
        exit;
    }
}

// Chemin relatif vers la racine du projet
function getBaseUrl() {
    // Déterminer le nombre de dossiers de profondeur
    $path = $_SERVER['PHP_SELF'];
    $depth = substr_count($path, '/') - 1;
    
    if ($depth <= 0) {
        return './';
    }
    
    return str_repeat('../', $depth);
}

// Obtenir l'ID de l'utilisateur connecté
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Obtenir le nom d'utilisateur
function getUsername() {
    return $_SESSION['username'] ?? 'Invité';
}
?>