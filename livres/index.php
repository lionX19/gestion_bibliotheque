<?php
session_start();
require_once '../db/db.php';
require_once '../includes/auth.php';

requireLogin();

// Récupérer les livres avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM livres WHERE titre LIKE :search OR auteur LIKE :search OR code LIKE :search ORDER BY titre ASC LIMIT :limit OFFSET :offset");
    $searchParam = "%{$search}%";
    $stmt->bindParam(':search', $searchParam);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $countStmt = $conn->prepare("SELECT COUNT(*) FROM livres WHERE titre LIKE :search OR auteur LIKE :search OR code LIKE :search");
    $countStmt->bindParam(':search', $searchParam);
} else {
    $stmt = $conn->prepare("SELECT * FROM livres ORDER BY titre ASC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $countStmt = $conn->prepare("SELECT COUNT(*) FROM livres");
}

$stmt->execute();
$livres = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt->execute();
$totalLivres = $countStmt->fetchColumn();
$totalPages = ceil($totalLivres / $limit);

// Message de confirmation/erreur
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Livres - Bibliothèque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container content">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-book-open me-2"></i>Gestion des Livres</h5>
                <a href="create.php" class="btn btn-light btn-sm"><i class="fas fa-plus me-1"></i>Ajouter un livre</a>
            </div>
            <div class="card-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6 offset-md-6">
                        <form action="" method="get" class="d-flex">
                            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Rechercher un livre..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Titre</th>
                                <th>Auteur</th>
                                <th>Code</th>
                                <th>Date de publication</th>
                                <th>Exemplaires</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($livres) > 0): ?>
                                <?php foreach ($livres as $livre): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($livre['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($livre['image']); ?>" alt="Couverture" class="book-img img-thumbnail">
                                            <?php else: ?>
                                                <img src="../assets/images/default.png" alt="Image par défaut" class="book-img img-thumbnail">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($livre['titre']); ?></td>
                                        <td><?php echo htmlspecialchars($livre['auteur']); ?></td>
                                        <td><?php echo htmlspecialchars($livre['code']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($livre['date_publication'])); ?></td>
                                        <td><?php echo $livre['exemplaires']; ?></td>
                                        <td class="action-buttons">
                                            <a href="edit.php?id=<?php echo $livre['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $livre['id']; ?>" class="btn btn-sm btn-danger delete-confirm">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Aucun livre trouvé</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Précédent</a>
                            </li>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Suivant</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>