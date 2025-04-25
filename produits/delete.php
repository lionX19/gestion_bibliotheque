<?php
require_once '../includes/auth.php';
requireLogin();

require_once '../db/db.php';

$id = $_GET['id'] ?? 0;
$product = getProductById($db, $id);

if (!$product) {
    header("Location: index.php?error=not_found");
    exit;
}

$confirmDelete = isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes';

if ($confirmDelete) {
    // Supprimer le produit
    if (deleteProduct($db, $id)) {
        header("Location: index.php?success=deleted");
        exit;
    } else {
        $error = "Une erreur est survenue lors de la suppression du produit.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un Produit - Gestion de Stocks</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h1>Supprimer un Produit</h1>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-warning">
                        <p>Êtes-vous sûr de vouloir supprimer le produit suivant ?</p>
                        <p><strong>Référence:</strong> <?php echo htmlspecialchars($product['reference']); ?></p>
                        <p><strong>Nom:</strong> <?php echo htmlspecialchars($product['nom']); ?></p>
                        <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($product['categorie']); ?></p>
                        <p><strong>Quantité en stock:</strong> <?php echo $product['quantite']; ?></p>
                        <p class="mb-0"><em>Attention: Cette action supprimera également tous les mouvements de stock associés à ce produit et ne peut pas être annulée.</em></p>
                    </div>
                    
                    <form method="post" action="">
                        <input type="hidden" name="confirm_delete" value="yes">
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                            <a href="index.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>