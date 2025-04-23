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
    // Récupérer les informations de l'adhérent
    $stmt = $conn->prepare("SELECT * FROM adherents WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $adherent = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$adherent) {
        $_SESSION['message'] = "Adhérent non trouvé.";
        header('Location: index.php');
        exit;
    }

    // Formater la date d'inscription
    $date_inscription = new DateTime($adherent['date_inscription']);
    $date_inscription_formatted = $date_inscription->format('d/m/Y');
} catch (PDOException $e) {
    error_log("Erreur PDO lors de la récupération des données : " . $e->getMessage());
    $_SESSION['message'] = "Une erreur est survenue lors de la récupération des données.";
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'Adhérent - Bibliothèque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Détails de l'Adhérent</h5>
                        <div>
                            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm me-2">
                                <i class="fas fa-edit me-1"></i>Modifier
                            </a>
                            <a href="index.php" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Retour
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Photo de profil -->
                            <div class="col-md-4 text-center mb-4">
                                <?php if (!empty($adherent['photo_profil'])): ?>
                                    <img src="../<?php echo htmlspecialchars($adherent['photo_profil']); ?>"
                                        alt="Photo de profil"
                                        class="img-thumbnail mb-3"
                                        style="max-width: 200px;">
                                <?php else: ?>
                                    <img src="../assets/images/default.png"
                                        alt="Photo par défaut"
                                        class="img-thumbnail mb-3"
                                        style="max-width: 200px;">
                                <?php endif; ?>
                            </div>

                            <!-- Informations de l'adhérent -->
                            <div class="col-md-8">
                                <div class="mb-4">
                                    <h4 class="mb-3"><?php echo htmlspecialchars($adherent['prenom'] . ' ' . $adherent['nom']); ?></h4>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong><i class="fas fa-envelope me-2"></i>Email :</strong><br>
                                                <?php echo htmlspecialchars($adherent['email']); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong><i class="fas fa-phone me-2"></i>Téléphone :</strong><br>
                                                <?php echo htmlspecialchars($adherent['telephone'] ?? 'Non renseigné'); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <p class="mb-2">
                                                <strong><i class="fas fa-map-marker-alt me-2"></i>Adresse :</strong><br>
                                                <?php echo nl2br(htmlspecialchars($adherent['adresse'] ?? 'Non renseignée')); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong><i class="fas fa-calendar-alt me-2"></i>Date d'inscription :</strong><br>
                                                <?php echo $date_inscription_formatted; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong><i class="fas fa-id-card me-2"></i>ID Adhérent :</strong><br>
                                                <?php echo $id; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>