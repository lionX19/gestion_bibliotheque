<?php
session_start();
require_once '../db/db.php';
require_once '../includes/auth.php';

requireLogin();

// Récupérer les emprunts avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

if (!empty($search)) {
    $stmt = $conn->prepare("
        SELECT e.*, l.titre as livre_titre, a.nom as adherent_nom, a.prenom as adherent_prenom,
               e.date_retour_effective as date_retour
        FROM emprunts e
        JOIN livres l ON e.livre_id = l.id
        JOIN adherents a ON e.adherent_id = a.id
        WHERE l.titre LIKE :search 
        OR a.nom LIKE :search 
        OR a.prenom LIKE :search
        ORDER BY e.date_emprunt DESC
        LIMIT :limit OFFSET :offset
    ");
    $searchParam = "%{$search}%";
    $stmt->bindParam(':search', $searchParam);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $countStmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM emprunts e
        JOIN livres l ON e.livre_id = l.id
        JOIN adherents a ON e.adherent_id = a.id
        WHERE l.titre LIKE :search 
        OR a.nom LIKE :search 
        OR a.prenom LIKE :search
    ");
    $countStmt->bindParam(':search', $searchParam);
} else {
    $stmt = $conn->prepare("
        SELECT e.*, l.titre as livre_titre, a.nom as adherent_nom, a.prenom as adherent_prenom,
               e.date_retour_effective as date_retour
        FROM emprunts e
        JOIN livres l ON e.livre_id = l.id
        JOIN adherents a ON e.adherent_id = a.id
        ORDER BY e.date_emprunt DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $countStmt = $conn->prepare("SELECT COUNT(*) FROM emprunts");
}

$stmt->execute();
$emprunts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt->execute();
$totalEmprunts = $countStmt->fetchColumn();
$totalPages = ceil($totalEmprunts / $limit);

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
    <title>Gestion des Emprunts - Bibliothèque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container content">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-book-reader me-2"></i>Gestion des Emprunts</h5>
                <a href="create.php" class="btn btn-light btn-sm"><i class="fas fa-plus me-1"></i>Nouvel Emprunt</a>
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
                            <input type="text" name="search" class="form-control" placeholder="Rechercher un emprunt..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Livre</th>
                                <th>Adhérent</th>
                                <th>Date d'emprunt</th>
                                <th>Date de retour prévue</th>
                                <th>Date de retour</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($emprunts) > 0): ?>
                                <?php foreach ($emprunts as $emprunt): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($emprunt['livre_titre']); ?></td>
                                        <td><?php echo htmlspecialchars($emprunt['adherent_prenom'] . ' ' . $emprunt['adherent_nom']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($emprunt['date_emprunt'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($emprunt['date_retour_prevue'])); ?></td>
                                        <td>
                                            <?php if (!empty($emprunt['date_retour'])): ?>
                                                <?php echo date('d/m/Y', strtotime($emprunt['date_retour'])); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (empty($emprunt['date_retour'])): ?>
                                                <?php if (strtotime($emprunt['date_retour_prevue']) < time()): ?>
                                                    <span class="badge bg-danger">En retard</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">En cours</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-success">Retourné</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="view.php?id=<?php echo $emprunt['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (empty($emprunt['date_retour'])): ?>
                                                <a href="edit.php?id=<?php echo $emprunt['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Aucun emprunt trouvé</td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>