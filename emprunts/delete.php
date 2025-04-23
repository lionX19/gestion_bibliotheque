<?php
session_start();
require_once '../db/db.php';
require_once '../includes/auth.php';

requireLogin();

// Vérifier si l'ID est présent et valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "ID d'emprunt invalide.";
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

try {
    // Vérifier si l'emprunt existe et n'est pas déjà retourné
    $stmt = $conn->prepare("SELECT * FROM emprunts WHERE id = :id AND date_retour IS NULL");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $emprunt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$emprunt) {
        $_SESSION['message'] = "Emprunt non trouvé ou déjà retourné.";
        header('Location: index.php');
        exit;
    }

    // Supprimer l'emprunt
    $stmt = $conn->prepare("DELETE FROM emprunts WHERE id = :id");
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Emprunt supprimé avec succès.";
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression de l'emprunt.";
    }
} catch (PDOException $e) {
    error_log("Erreur PDO lors de la suppression : " . $e->getMessage());
    $_SESSION['message'] = "Une erreur est survenue lors de la suppression.";
}

// Rediriger vers la liste des emprunts
header('Location: index.php');
exit;
