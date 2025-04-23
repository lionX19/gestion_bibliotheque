<?php
session_start();
require_once '../db/db.php';
require_once '../includes/auth.php';

requireLogin();

// Récupérer les adhérents avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM adherents WHERE nom LIKE :search OR prenom LIKE :search OR email LIKE :search ORDER BY nom, prenom ASC LIMIT :limit OFFSET :offset");
    $searchParam = "%{$search}%";
    $stmt->bindParam(':search', $searchParam);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $countStmt = $conn->prepare("SELECT COUNT(*) FROM adherents WHERE nom LIKE :search OR prenom LIKE :search OR email LIKE :search");
    $countStmt->bindParam(':search', $searchParam);
} else {
    $stmt = $conn->prepare("SELECT * FROM adherents ORDER BY nom, prenom ASC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $countStmt = $conn->prepare("SELECT COUNT(*) FROM adherents");
}

$stmt->execute();
$adherents = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt->execute();
$totalAdherents = $countStmt->fetchColumn();
$totalPages = ceil($totalAdherents / $limit);

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
    <title>Gestion des Adhérents - Bibliothèque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container content">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Gestion des Adhérents</h5>
                <a href="create.php" class="btn btn-light btn-sm"><i class="fas fa-plus me-1"></i>Ajouter un adhérent</a>
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
                            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Rechercher un adhérent..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($adherents) > 0): ?>
                                <?php foreach ($adherents as $adherent): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($adherent['photo_profil'])): ?>
                                                <img src="<?php echo htmlspecialchars($adherent['photo_profil']); ?>" alt="Photo" class="profile-img img-thumbnail">
                                            <?php else: ?>
                                                <img src="../assets/images/default.png" alt="Image par défaut" class="profile-img img-thumbnail">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($adherent['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($adherent['prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($adherent['email']); ?></td>
                                        <td><?php echo htmlspecialchars($adherent['telephone']); ?></td>
                                        <td class="action-buttons">
                                            <a href="view.php?id=<?php echo $adherent['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $adherent['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $adherent['id']; ?>" class="btn btn-sm btn-danger delete-confirm">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucun adhérent trouvé</td>
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