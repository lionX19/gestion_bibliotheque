<?php
session_start();
require_once '../db/db.php';
require_once '../includes/auth.php';

requireLogin();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "ID de livre non spécifié.";
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

try {
    // Vérifier si le livre existe
    $stmt = $conn->prepare("SELECT image FROM livres WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $livre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$livre) {
        $_SESSION['message'] = "Livre non trouvé.";
        header('Location: index.php');
        exit;
    }

    // Vérifier si le livre est emprunté
    $stmt = $conn->prepare("SELECT COUNT(*) FROM emprunts WHERE livre_id = :id AND date_retour_effective IS NULL");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    if ($stmt->fetchColumn() > 0) {
        $_SESSION['message'] = "Ce livre ne peut pas être supprimé car il est actuellement emprunté.";
        header('Location: index.php');
        exit;
    }

    // Supprimer le livre
    $stmt = $conn->prepare("DELETE FROM livres WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    // Supprimer l'image associée si elle existe
    if (!empty($livre['image']) && file_exists($livre['image'])) {
        unlink($livre['image']);
    }

    $_SESSION['message'] = "Le livre a été supprimé avec succès.";
} catch (PDOException $e) {
    $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
}

header('Location: index.php');
exit;
