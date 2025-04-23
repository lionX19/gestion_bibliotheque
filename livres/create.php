<?php
session_start();
require_once '../db/db.php';
require_once '../includes/auth.php';

requireLogin();

$errors = [];
$livre = [
    'titre' => '',
    'auteur' => '',
    'code' => '',
    'date_publication' => '',
    'exemplaires' => 1
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération et nettoyage des données
    $livre['titre'] = clean_input($_POST['titre']);
    $livre['auteur'] = clean_input($_POST['auteur']);
    $livre['code'] = clean_input($_POST['code']);
    $livre['date_publication'] = clean_input($_POST['date_publication']);
    $livre['exemplaires'] = (int)$_POST['exemplaires'];

    // Validation des données
    if (empty($livre['titre'])) {
        $errors[] = "Le titre est obligatoire.";
    }

    if (empty($livre['auteur'])) {
        $errors[] = "L'auteur est obligatoire.";
    }

    if (empty($livre['code'])) {
        $errors[] = "Le code est obligatoire.";
    } else {
        // Vérifier si le code existe déjà
        $stmt = $conn->prepare("SELECT COUNT(*) FROM livres WHERE code = :code");
        $stmt->bindParam(':code', $livre['code']);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Ce code de livre existe déjà.";
        }
    }

    if ($livre['exemplaires'] < 1) {
        $livre['exemplaires'] = 1;
    }

    // Traitement de l'image
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors[] = "Le format de l'image n'est pas autorisé. Formats acceptés : " . implode(', ', $allowed);
        } else {
            $new_filename = uniqid() . '.' . $ext;
            $upload_dir = '../assets/images/livres/';

            // Créer le dossier s'il n'existe pas
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $image_path = $upload_dir . $new_filename;

            if (!move_uploaded_file($tmp, $image_path)) {
                $errors[] = "Erreur lors du téléchargement de l'image.";
                $image_path = '';
            }
        }
    }

    // Si aucune erreur, insérer dans la base de données
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO livres (titre, auteur, code, date_publication, exemplaires, image) 
                                   VALUES (:titre, :auteur, :code, :date_publication, :exemplaires, :image)");

            $stmt->bindParam(':titre', $livre['titre']);
            $stmt->bindParam(':auteur', $livre['auteur']);
            $stmt->bindParam(':code', $livre['code']);
            $stmt->bindParam(':date_publication', $livre['date_publication']);
            $stmt->bindParam(':exemplaires', $livre['exemplaires']);
            $stmt->bindParam(':image', $image_path);

            $stmt->execute();

            $_SESSION['message'] = "Le livre a été ajouté avec succès.";
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un livre - Bibliothèque</title>
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
                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Ajouter un nouveau livre</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="titre" class="form-label">Titre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="titre" name="titre" value="<?php echo htmlspecialchars($livre['titre']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="auteur" class="form-label">Auteur <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="auteur" name="auteur" value="<?php echo htmlspecialchars($livre['auteur']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($livre['code']); ?>" required>
                                        <div class="form-text">Un code unique pour identifier le livre (ex: ISBN).</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_publication" class="form-label">Date de publication</label>
                                        <input type="date" class="form-control" id="date_publication" name="date_publication" value="<?php echo htmlspecialchars($livre['date_publication']); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="exemplaires" class="form-label">Nombre d'exemplaires</label>
                                        <input type="number" class="form-control" id="exemplaires" name="exemplaires" min="1" value="<?php echo $livre['exemplaires']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Image de couverture</label>
                                        <input type="file" class="form-control" id="image" name="image">
                                        <div class="form-text">Formats acceptés : JPG, JPEG, PNG, GIF.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="index.php" class="btn btn-secondary me-2">Annuler</a>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>