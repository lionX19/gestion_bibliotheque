<?php
session_start();
require_once '../db/db.php';
require_once '../includes/auth.php';

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$nom = $prenom = $email = $telephone = $adresse = '';
$errors = [];
$message = '';

// Vérifier si l'adhérent existe
$stmt = $conn->prepare("SELECT * FROM adherents WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$adherent = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adherent) {
    $_SESSION['message'] = "Adhérent non trouvé.";
    header('Location: index.php');
    exit;
}

// Récupérer les données existantes
$nom = $adherent['nom'];
$prenom = $adherent['prenom'];
$email = $adherent['email'];
$telephone = $adherent['telephone'];
$adresse = $adherent['adresse'];
$photo_profil_path = $adherent['photo_profil'];

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyer et valider les entrées
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');

    // Validation
    if (empty($nom)) {
        $errors['nom'] = 'Le nom est requis.';
    }
    if (empty($prenom)) {
        $errors['prenom'] = 'Le prénom est requis.';
    }
    if (empty($email)) {
        $errors['email'] = 'L\'email est requis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'L\'email n\'est pas valide.';
    } else {
        // Vérifier si l'email existe déjà pour un autre adhérent
        $stmtCheck = $conn->prepare("SELECT id FROM adherents WHERE email = :email AND id != :id");
        $stmtCheck->bindParam(':email', $email);
        $stmtCheck->bindParam(':id', $id);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            $errors['email'] = 'Cet email est déjà utilisé par un autre adhérent.';
        }
    }

    // Gestion de l'upload de la photo de profil
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/adherents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileExtension = pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if (in_array(strtolower($fileExtension), $allowedExtensions)) {
            if ($_FILES['photo_profil']['size'] <= $maxFileSize) {
                $newFileName = uniqid('adherent_', true) . '.' . $fileExtension;
                $uploadFilePath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $uploadFilePath)) {
                    // Supprimer l'ancienne photo si elle existe
                    if ($photo_profil_path && file_exists('../' . $photo_profil_path)) {
                        unlink('../' . $photo_profil_path);
                    }
                    $photo_profil_path = 'uploads/adherents/' . $newFileName;
                } else {
                    $errors['photo_profil'] = 'Erreur lors du déplacement du fichier.';
                }
            } else {
                $errors['photo_profil'] = 'Le fichier est trop volumineux (max 5MB).';
            }
        } else {
            $errors['photo_profil'] = 'Type de fichier non autorisé (jpg, jpeg, png, gif).';
        }
    } elseif (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] != UPLOAD_ERR_NO_FILE) {
        $errors['photo_profil'] = 'Erreur lors de l\'upload du fichier: ' . $_FILES['photo_profil']['error'];
    }

    // Si pas d'erreurs, mettre à jour dans la base de données
    if (empty($errors)) {
        try {
            $sql = "UPDATE adherents SET 
                    nom = :nom, 
                    prenom = :prenom, 
                    email = :email, 
                    telephone = :telephone, 
                    adresse = :adresse, 
                    photo_profil = :photo_profil 
                    WHERE id = :id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telephone', $telephone);
            $stmt->bindParam(':adresse', $adresse);
            $stmt->bindParam(':photo_profil', $photo_profil_path);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Adhérent modifié avec succès !";
                header('Location: index.php');
                exit;
            } else {
                $message = "Erreur lors de la modification de l'adhérent.";
            }
        } catch (PDOException $e) {
            error_log("Erreur PDO lors de la mise à jour : " . $e->getMessage());
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
    <title>Modifier un Adhérent - Bibliothèque</title>
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
                        <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Modifier un Adhérent</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message) && empty($errors)): ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php elseif (!empty($message) && !empty($errors)): ?>
                            <div class="alert alert-warning"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <form action="edit.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
                            <!-- Champ Nom -->
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php echo isset($errors['nom']) ? 'is-invalid' : ''; ?>" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                                <?php if (isset($errors['nom'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['nom']; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Champ Prénom -->
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php echo isset($errors['prenom']) ? 'is-invalid' : ''; ?>" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>" required>
                                <?php if (isset($errors['prenom'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['prenom']; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Champ Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Champ Téléphone -->
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control <?php echo isset($errors['telephone']) ? 'is-invalid' : ''; ?>" id="telephone" name="telephone" value="<?php echo htmlspecialchars($telephone); ?>">
                                <?php if (isset($errors['telephone'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['telephone']; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Champ Adresse -->
                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea class="form-control <?php echo isset($errors['adresse']) ? 'is-invalid' : ''; ?>" id="adresse" name="adresse" rows="3"><?php echo htmlspecialchars($adresse); ?></textarea>
                                <?php if (isset($errors['adresse'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['adresse']; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Champ Photo de Profil -->
                            <div class="mb-3">
                                <label for="photo_profil" class="form-label">Photo de profil</label>
                                <?php if ($photo_profil_path): ?>
                                    <div class="mb-2">
                                        <img src="../<?php echo htmlspecialchars($photo_profil_path); ?>" alt="Photo actuelle" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                <?php endif; ?>
                                <input class="form-control <?php echo isset($errors['photo_profil']) ? 'is-invalid' : ''; ?>" type="file" id="photo_profil" name="photo_profil">
                                <div class="form-text">Formats acceptés: JPG, JPEG, PNG, GIF. Taille max: 5MB.</div>
                                <?php if (isset($errors['photo_profil'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['photo_profil']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="index.php" class="btn btn-secondary me-2">Annuler</a>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer les modifications</button>
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