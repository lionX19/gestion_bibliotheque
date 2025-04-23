<?php
session_start();
require_once '../db/db.php';
require_once '../includes/auth.php';

requireLogin();

// Vérifier si l'ID est présent et valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "ID d'adhérent invalide.";
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

try {
    // Récupérer les informations de l'adhérent avant suppression
    $stmt = $conn->prepare("SELECT photo_profil FROM adherents WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $adherent = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$adherent) {
        $_SESSION['message'] = "Adhérent non trouvé.";
        header('Location: index.php');
        exit;
    }

    // Supprimer la photo de profil si elle existe
    if ($adherent['photo_profil'] && file_exists('../' . $adherent['photo_profil'])) {
        unlink('../' . $adherent['photo_profil']);
    }

    // Supprimer l'adhérent de la base de données
    $stmt = $conn->prepare("DELETE FROM adherents WHERE id = :id");
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Adhérent supprimé avec succès.";
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression de l'adhérent.";
    }
} catch (PDOException $e) {
    error_log("Erreur PDO lors de la suppression : " . $e->getMessage());
    $_SESSION['message'] = "Une erreur est survenue lors de la suppression.";
}

// Rediriger vers la liste des adhérents
header('Location: index.php');
exit;
