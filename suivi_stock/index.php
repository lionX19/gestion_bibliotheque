<?php
require_once '../includes/auth.php';
requireLogin();

require_once '../db/db.php';

// Récupérer les statistiques de stock
$stockSummary = getStockSummary($db);

// Récupérer les alertes de stock
$lowStockProducts = getLowStockProducts($db);

// Récupérer les mouvements récents
$recentMovements = getRecentMovements($db);

// Filtrage des mouvements
$filter = $_GET['filter'] ?? 'all';
$movements = [];

if ($filter === 'all') {
    $movements = getAllMovements($db);
} else {
    $movements = getMovementsByType($db, $filter);
}

// Messages de succès
$success = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'entry_added':
            $success = 'Entrée de stock enregistrée avec succès.';
            break;
        case 'exit_added':
            $success = 'Sortie de stock enregistrée avec succès.';
            break;
        case 'movement_deleted':
            $success = 'Mouvement supprimé avec succès.';
            break;
    }
}

// Messages d'erreur
$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'not_found':
            $error = 'Élément non trouvé.';
            break;
        case 'edit_not_allowed':
            $error = "L'édition des mouvements n'est pas autorisée pour préserver l'intégrité de l'historique.";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion de Stocks</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <main>
        <div class="container">
            <h1 class="mb-4">Tableau de Bord</h1>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistiques -->
            <div class="dashboard-stats mb-5">
                <div class="stat-card">
                    <h3>Total Produits</h3>
                    <p class="stat-value stat-primary"><?php echo $stockSummary['total_products']; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Stock Faible</h3>
                    <p class="stat-value stat-warning"><?php echo $stockSummary['low_stock']; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Rupture de Stock</h3>
                    <p class="stat-value stat-danger"><?php echo $stockSummary['out_of_stock']; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Valeur du Stock</h3>
                    <p class="stat-value stat-primary"><?php echo number_format($stockSummary['total_value'], 2); ?> €</p>
                </div>
            </div>
            
            <!-- Alertes de stock -->
            <div class="card mb-5">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Alertes de Stock</h2>
                </div>
                <div class="card-body">
                    <div id="low-stock-alerts">
                        <?php if (empty($lowStockProducts)): ?>
                            <p class="text-center">Aucune alerte de stock</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Produit</th>
                                            <th>Stock actuel</th>
                                            <th>Seuil</th>
                                            <th>Statut</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lowStockProducts as $product): ?>
                                            <?php
                                            // Déterminer le statut
                                            $statusClass = 'badge-warning';
                                            $statusText = 'Stock faible';
                                            
                                            if ($product['quantite'] === 0) {
                                                $statusClass = 'badge-danger';
                                                $statusText = 'Rupture';
                                            }
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['reference']); ?></td>
                                                <td><?php echo htmlspecialchars($product['nom']); ?></td>
                                                <td><?php echo $product['quantite']; ?></td>
                                                <td><?php echo $product['seuil']; ?></td>
                                                <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                                <td>
                                                    <a href="../mouvements/entree.php?product_id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-plus-circle"></i> Ajouter du stock
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Mouvements récents -->
            <div class="card mb-5">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Derniers Mouvements</h2>
                    <div>
                        <a href="../mouvements/entree.php" class="btn btn-success btn-sm">
                            <i class="fas fa-plus-circle"></i> Entrée
                        </a>
                        <a href="../mouvements/sortie.php" class="btn btn-danger btn-sm">
                            <i class="fas fa-minus-circle"></i> Sortie
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <form action="" method="GET" class="d-flex">
                            <label for="filter" class="mr-2">Filtrer par:</label>
                            <select name="filter" id="filter" class="form-control" onchange="this.form.submit()">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Tous les mouvements</option>
                                <option value="entry" <?php echo $filter === 'entry' ? 'selected' : ''; ?>>Entrées</option>
                                <option value="exit" <?php echo $filter === 'exit' ? 'selected' : ''; ?>>Sorties</option>
                            </select>
                        </form>
                    </div>
                    
                    <?php if (empty($movements)): ?>
                        <p class="text-center">Aucun mouvement trouvé</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Produit</th>
                                        <th>Type</th>
                                        <th>Quantité</th>
                                        <th>Commentaire</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="movements-table-body">
                                    <?php foreach ($movements as $movement): ?>
                                        <?php
                                        // Déterminer type
                                        $typeClass = $movement['type'] === 'entry' ? 'badge-success' : 'badge-danger';
                                        $typeText = $movement['type'] === 'entry' ? 'Entrée' : 'Sortie';
                                        ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($movement['date'])); ?></td>
                                            <td><?php echo htmlspecialchars($movement['produit_nom']); ?></td>
                                            <td><span class="badge <?php echo $typeClass; ?>"><?php echo $typeText; ?></span></td>
                                            <td><?php echo $movement['quantite']; ?></td>
                                            <td><?php echo htmlspecialchars($movement['commentaire']); ?></td>
                                            <td>
                                                <a href="../mouvements/delete.php?id=<?php echo $movement['id']; ?>" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>