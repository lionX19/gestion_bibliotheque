<?php
require_once '../includes/auth.php';
requireLogin();

require_once '../db/db.php';

// Recherche de produits
$search = $_GET['search'] ?? '';
$products = [];

if (!empty($search)) {
    $products = searchProducts($db, $search);
} else {
    $products = getAllProducts($db);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Produits - Gestion de Stocks</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <main>
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Liste des Produits</h1>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un produit
                </a>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <form action="" method="GET" class="d-flex">
                        <input type="text" name="search" id="search-input" class="form-control" placeholder="Rechercher par référence, nom ou catégorie..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary ml-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Nom</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Quantité</th>
                                    <th>Seuil</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="products-table-body">
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Aucun produit trouvé</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <?php
                                        // Déterminer le statut
                                        $statusClass = 'badge-success';
                                        $statusText = 'En stock';
                                        
                                        if ($product['quantite'] === 0) {
                                            $statusClass = 'badge-danger';
                                            $statusText = 'Rupture';
                                        } else if ($product['quantite'] < $product['seuil']) {
                                            $statusClass = 'badge-warning';
                                            $statusText = 'Stock faible';
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['reference']); ?></td>
                                            <td><?php echo htmlspecialchars($product['nom']); ?></td>
                                            <td><?php echo htmlspecialchars($product['categorie']); ?></td>
                                            <td><?php echo number_format($product['prix'], 2); ?> €</td>
                                            <td><?php echo $product['quantite']; ?></td>
                                            <td><?php echo $product['seuil']; ?></td>
                                            <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                            <td>
                                                <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>