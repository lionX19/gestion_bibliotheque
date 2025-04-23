<?php
session_start();
require_once '../db/db.php';
require_once '../includes/auth.php';

requireLogin();

$errors = [];
$message = '';

// Récupérer la liste des livres disponibles
$stmtLivres = $conn->prepare("
    SELECT l.*, 
           (l.exemplaires - COUNT(e.id)) as exemplaires_disponibles
    FROM livres l
    LEFT JOIN emprunts e ON l.id = e.livre_id AND e.date_retour_effective  IS NULL
    GROUP BY l.id
    HAVING exemplaires_disponibles > 0
");
$stmtLivres->execute();
$livres = $stmtLivres->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des adhérents
$stmtAdherents = $conn->prepare("SELECT * FROM adherents ORDER BY nom, prenom");
$stmtAdherents->execute();
$adherents = $stmtAdherents->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $livre_id = isset($_POST['livre_id']) ? (int)$_POST['livre_id'] : 0;
    $adherent_id = isset($_POST['adherent_id']) ? (int)$_POST['adherent_id'] : 0;
    $date_emprunt = $_POST['date_emprunt'] ?? date('Y-m-d');
    $date_retour_prevue = $_POST['date_retour_prevue'] ?? '';

    // Validation
    if (empty($livre_id)) {
        $errors['livre_id'] = 'Veuillez sélectionner un livre.';
    }
    if (empty($adherent_id)) {
        $errors['adherent_id'] = 'Veuillez sélectionner un adhérent.';
    }
    if (empty($date_retour_prevue)) {
        $errors['date_retour_prevue'] = 'Veuillez spécifier une date de retour prévue.';
    }

    // Vérifier si le livre est disponible
    if (empty($errors)) {
        $stmtCheck = $conn->prepare("
            SELECT l.*, 
                   (l.exemplaires - COUNT(e.id)) as exemplaires_disponibles
            FROM livres l
            LEFT JOIN emprunts e ON l.id = e.livre_id AND e.date_retour_effective  IS NULL
            WHERE l.id = :livre_id
            GROUP BY l.id
        ");
        $stmtCheck->bindParam(':livre_id', $livre_id);
        $stmtCheck->execute();
        $livre = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$livre || $livre['exemplaires_disponibles'] <= 0) {
            $errors['livre_id'] = 'Ce livre n\'est pas disponible.';
        }
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO emprunts (livre_id, adherent_id, date_emprunt, date_retour_prevue) 
                    VALUES (:livre_id, :adherent_id, :date_emprunt, :date_retour_prevue)";
            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':livre_id', $livre_id);
            $stmt->bindParam(':adherent_id', $adherent_id);
            $stmt->bindParam(':date_emprunt', $date_emprunt);
            $stmt->bindParam(':date_retour_prevue', $date_retour_prevue);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Emprunt enregistré avec succès !";
                header('Location: index.php');
                exit;
            } else {
                $message = "Erreur lors de l'enregistrement de l'emprunt.";
            }
        } catch (PDOException $e) {
            error_log("Erreur PDO lors de l'insertion : " . $e->getMessage());
            $message = "Erreur base de données. Veuillez réessayer.";
        }
    } else {
        $message = "Veuillez corriger les erreurs dans le formulaire.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvel Emprunt - Bibliothèque</title>
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
                        <h5 class="mb-0"><i class="fas fa-book-reader me-2"></i>Nouvel Emprunt</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message) && empty($errors)): ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php elseif (!empty($message) && !empty($errors)): ?>
                            <div class="alert alert-warning"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <form action="create.php" method="post">
                            <!-- Sélection du livre -->
                            <div class="mb-3">
                                <label for="livre_id" class="form-label">Livre <span class="text-danger">*</span></label>
                                <select class="form-select <?php echo isset($errors['livre_id']) ? 'is-invalid' : ''; ?>" id="livre_id" name="livre_id" required>
                                    <option value="">Sélectionnez un livre</option>
                                    <?php foreach ($livres as $livre): ?>
                                        <option value="<?php echo $livre['id']; ?>" <?php echo (isset($_POST['livre_id']) && $_POST['livre_id'] == $livre['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($livre['titre']); ?>
                                            (<?php echo $livre['exemplaires_disponibles']; ?> exemplaire(s) disponible(s))
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['livre_id'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['livre_id']; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Sélection de l'adhérent -->
                            <div class="mb-3">
                                <label for="adherent_id" class="form-label">Adhérent <span class="text-danger">*</span></label>
                                <select class="form-select <?php echo isset($errors['adherent_id']) ? 'is-invalid' : ''; ?>" id="adherent_id" name="adherent_id" required>
                                    <option value="">Sélectionnez un adhérent</option>
                                    <?php foreach ($adherents as $adherent): ?>
                                        <option value="<?php echo $adherent['id']; ?>" <?php echo (isset($_POST['adherent_id']) && $_POST['adherent_id'] == $adherent['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($adherent['prenom'] . ' ' . $adherent['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['adherent_id'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['adherent_id']; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Date d'emprunt -->
                            <div class="mb-3">
                                <label for="date_emprunt" class="form-label">Date d'emprunt</label>
                                <input type="date" class="form-control" id="date_emprunt" name="date_emprunt"
                                    value="<?php echo isset($_POST['date_emprunt']) ? $_POST['date_emprunt'] : date('Y-m-d'); ?>">
                            </div>

                            <!-- Date de retour prévue -->
                            <div class="mb-3">
                                <label for="date_retour_prevue" class="form-label">Date de retour prévue <span class="text-danger">*</span></label>
                                <input type="date" class="form-control <?php echo isset($errors['date_retour_prevue']) ? 'is-invalid' : ''; ?>"
                                    id="date_retour_prevue" name="date_retour_prevue"
                                    value="<?php echo $_POST['date_retour_prevue'] ?? ''; ?>" required>
                                <?php if (isset($errors['date_retour_prevue'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['date_retour_prevue']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="index.php" class="btn btn-secondary me-2">Annuler</a>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer l'emprunt</button>
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