<?php
require_once '../includes/auth.php';
requireLogin();

require_once '../db/db.php';

$id = $_GET['id'] ?? 0;
$movement = getMovementById($db, $id);

if (!$movement) {
    header("Location: ../suivi_stock/index.php?error=not_found");
    exit;
}

// Récupérer les infos du produit
$product = getProductById($db, $movement['produit_id']);

$confirmDelete = isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes';

if ($confirmDelete) {
    // Supprimer le mouvement
    if (deleteMovement($db, $id)) {
        header("Location: ../suivi_stock/index.php?success=movement_deleted");
        exit;
    } else {
        $error = "Une erreur est survenue lors de la suppression du mouvement.";
    }
}

// Type de mouvement en français
$typeText = $movement['type'] === 'entry' ? 'entrée' : 'sortie';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un Mouvement - Gestion de Stocks</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h1>Supprimer un Mouvement</h1>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-warning">
                        <p>Êtes-vous sûr de vouloir supprimer ce mouvement ?</p>
                        <p><strong>Type:</strong> <?php echo $typeText; ?></p>
                        <p><strong>Produit:</strong> <?php echo $product ? htmlspecialchars($product['nom']) : 'Produit inconnu'; ?></p>
                        <p><strong>Quantité:</strong> <?php echo $movement['quantite']; ?></p>
                        <p><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($movement['date'])); ?></p>
                        <p><strong>Commentaire:</strong> <?php echo htmlspecialchars($movement['commentaire']); ?></p>
                        <p class="mb-0"><em>Attention: Cette action va annuler ce mouvement de stock et ajuster la quantité du produit en conséquence. Elle ne peut pas être annulée.</em></p>
                    </div>
                    
                    <form method="post" action="">
                        <input type="hidden" name="confirm_delete" value="yes">
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                            <a href="../suivi_stock/index.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>