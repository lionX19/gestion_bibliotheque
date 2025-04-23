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
$errors = [];
$message = '';

try {
    // Récupérer les informations de l'emprunt
    $stmt = $conn->prepare("
        SELECT e.*, l.titre as livre_titre, a.nom as adherent_nom, a.prenom as adherent_prenom 
        FROM emprunts e
        JOIN livres l ON e.livre_id = l.id
        JOIN adherents a ON e.adherent_id = a.id
        WHERE e.id = :id AND e.date_retour IS NULL
    ");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $emprunt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$emprunt) {
        $_SESSION['message'] = "Emprunt non trouvé ou déjà retourné.";
        header('Location: index.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $date_retour = $_POST['date_retour'] ?? date('Y-m-d');

        if (empty($date_retour)) {
            $errors['date_retour'] = 'Veuillez spécifier une date de retour.';
        }

        if (empty($errors)) {
            $sql = "UPDATE emprunts SET date_retour = :date_retour WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':date_retour', $date_retour);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Retour du livre enregistré avec succès !";
                header('Location: index.php');
                exit;
            } else {
                $message = "Erreur lors de l'enregistrement du retour.";
            }
        } else {
            $message = "Veuillez corriger les erreurs dans le formulaire.";
        }
    }
} catch (PDOException $e) {
    error_log("Erreur PDO lors de la mise à jour : " . $e->getMessage());
    $_SESSION['message'] = "Une erreur est survenue lors de l'enregistrement du retour.";
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retour de Livre - Bibliothèque</title>
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
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-undo me-2"></i>Retour de Livre</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message) && empty($errors)): ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php elseif (!empty($message) && !empty($errors)): ?>
                            <div class="alert alert-warning"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h4>Détails de l'emprunt</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Livre :</strong> <?php echo htmlspecialchars($emprunt['livre_titre']); ?></p>
                                    <p><strong>Adhérent :</strong> <?php echo htmlspecialchars($emprunt['adherent_prenom'] . ' ' . $emprunt['adherent_nom']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Date d'emprunt :</strong> <?php echo date('d/m/Y', strtotime($emprunt['date_emprunt'])); ?></p>
                                    <p><strong>Date de retour prévue :</strong> <?php echo date('d/m/Y', strtotime($emprunt['date_retour_prevue'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <form action="edit.php?id=<?php echo $id; ?>" method="post">
                            <div class="mb-3">
                                <label for="date_retour" class="form-label">Date de retour <span class="text-danger">*</span></label>
                                <input type="date" class="form-control <?php echo isset($errors['date_retour']) ? 'is-invalid' : ''; ?>"
                                    id="date_retour" name="date_retour"
                                    value="<?php echo isset($_POST['date_retour']) ? $_POST['date_retour'] : date('Y-m-d'); ?>" required>
                                <?php if (isset($errors['date_retour'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['date_retour']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="index.php" class="btn btn-secondary me-2">Annuler</a>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer le retour</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>