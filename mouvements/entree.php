<?php
require_once '../includes/auth.php';
requireLogin();

require_once '../db/db.php';

$productId = $_GET['product_id'] ?? null;
$selectedProduct = null;

if ($productId) {
    $selectedProduct = getProductById($db, $productId);
}

$products = getAllProducts($db);
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données
    $produitId = $_POST['product'] ?? '';
    $quantite = intval($_POST['quantity'] ?? 0);
    $date = $_POST['date'] ?? '';
    $commentaire = $_POST['comment'] ?? '';
    
    // Validation
    if (empty($produitId)) {
        $errors[] = "Veuillez sélectionner un produit";
    }
    
    if ($quantite <= 0) {
        $errors[] = "La quantité doit être supérieure à zéro";
    }
    
    if (empty($date)) {
        $errors[] = "La date est requise";
    }
    
    if (empty($commentaire)) {
        $errors[] = "Le commentaire est requis";
    }
    
    // Si pas d'erreurs, enregistrer le mouvement
    if (empty($errors)) {
        try {
            $data = [
                'produit_id' => $produitId,
                'type' => 'entry',
                'quantite' => $quantite,
                'date' => $date,
                'commentaire' => $commentaire
            ];
            
            createMovement($db, $data);
            $success = true;
            
            // Rediriger vers la liste des mouvements
            header("Location: ../suivi_stock/index.php?success=entry_added");
            exit;
        } catch (Exception $e) {
            $errors[] = "Erreur lors de l'enregistrement: " . $e->getMessage();
        }
    }
}

// Définir la date par défaut à aujourd'hui
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrée de Stock - Gestion de Stocks</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h1>Entrée de Stock</h1>
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
                            Entrée de stock enregistrée avec succès!
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="" onsubmit="return window.formFunctions.validateMovementForm()">
                        <input type="hidden" id="type" name="type" value="entry">
                        
                        <div class="form-group">
                            <label for="product">Produit</label>
                            <select id="product" name="product" class="form-control" required>
                                <option value="">Sélectionner un produit</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>" <?php echo ($selectedProduct && $selectedProduct['id'] == $product['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($product['reference'] . ' - ' . $product['nom'] . ' (Stock: ' . $product['quantite'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantity">Quantité</label>
                            <input type="number" id="quantity" name="quantity" min="1" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="date">Date d'entrée</label>
                            <input type="date" id="date" name="date" class="form-control" value="<?php echo $today; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="comment">Commentaire (fournisseur, raison)</label>
                            <textarea id="comment" name="comment" rows="3" class="form-control" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Enregistrer l'entrée</button>
                            <a href="../suivi_stock/index.php" class="btn btn-secondary">Annuler</a>
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