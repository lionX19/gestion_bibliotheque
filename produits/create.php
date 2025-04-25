<?php
require_once '../includes/auth.php';
requireLogin();

require_once '../db/db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données
    $reference = $_POST['reference'] ?? '';
    $nom = $_POST['name'] ?? '';
    $categorie = $_POST['category'] ?? '';
    $prix = floatval($_POST['price'] ?? 0);
    $quantite = intval($_POST['quantity'] ?? 0);
    $seuil = intval($_POST['threshold'] ?? 0);
    
    // Validation
    if (empty($reference)) {
        $errors[] = "La référence est requise";
    }
    
    if (empty($nom)) {
        $errors[] = "Le nom est requis";
    }
    
    if (empty($categorie)) {
        $errors[] = "La catégorie est requise";
    }
    
    if ($prix <= 0) {
        $errors[] = "Le prix doit être supérieur à zéro";
    }
    
    if ($quantite < 0) {
        $errors[] = "La quantité ne peut pas être négative";
    }
    
    if ($seuil < 0) {
        $errors[] = "Le seuil ne peut pas être négatif";
    }
    
    // Si pas d'erreurs, enregistrer le produit
    if (empty($errors)) {
        try {
            // Vérifier si la référence existe déjà
            $query = "SELECT id FROM produits WHERE reference = :reference";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':reference', $reference, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Cette référence existe déjà";
            } else {
                $data = [
                    'reference' => $reference,
                    'nom' => $nom,
                    'categorie' => $categorie,
                    'prix' => $prix,
                    'quantite' => $quantite,
                    'seuil' => $seuil
                ];
                
                createProduct($db, $data);
                $success = true;
                
                // Rediriger vers la liste des produits
                header("Location: index.php?success=created");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'enregistrement: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit - Gestion de Stocks</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h1>Ajouter un Produit</h1>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Produit ajouté avec succès!
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="" onsubmit="return window.formFunctions.validateProductForm()">
                        <div class="form-group">
                            <label for="reference">Référence / Code</label>
                            <input type="text" id="reference" name="reference" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="name">Nom du produit</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Catégorie</label>
                            <input type="text" id="category" name="category" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Prix unitaire</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantity">Quantité en stock</label>
                            <input type="number" id="quantity" name="quantity" min="0" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="threshold">Seuil de réapprovisionnement</label>
                            <input type="number" id="threshold" name="threshold" min="0" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <a href="index.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>
