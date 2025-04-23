<?php
// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Déterminer la page active
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="../livres/index.php">
            <i class="fas fa-book"></i> Gestion Bibliothèque
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_dir == 'livres') ? 'active' : ''; ?>" href="../livres/index.php">
                        <i class="fas fa-book-open"></i> Livres
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_dir == 'adherents') ? 'active' : ''; ?>" href="../adherents/index.php">
                        <i class="fas fa-users"></i> Adhérents
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_dir == 'emprunts') ? 'active' : ''; ?>" href="../emprunts/index.php">
                        <i class="fas fa-exchange-alt"></i> Emprunts
                    </a>
                </li>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar"></i> Statistiques
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../statistiques/livres.php">Statistiques des livres</a></li>
                            <li><a class="dropdown-item" href="../statistiques/emprunts.php">Historique des emprunts</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <!-- Bouton Chatbot -->
                <li class="nav-item me-3">
                    <a href="../chatbot/index.php" class="nav-link" title="Assistant Bibliothèque">
                        <i class="fas fa-robot"></i>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Modal Chatbot -->
<div class="modal fade" id="chatbotModal" tabindex="-1" aria-labelledby="chatbotModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="chatbotModalLabel">
                    <i class="fas fa-robot me-2"></i>Assistant Bibliothèque
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe src="../chatbot/index.php" width="100%" height="500px" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Intercepter le clic sur le lien du chatbot
        const chatbotLink = document.querySelector('a[href="../chatbot/index.php"]');
        if (chatbotLink) {
            chatbotLink.addEventListener('click', function(e) {
                e.preventDefault();
                const chatbotModal = new bootstrap.Modal(document.getElementById('chatbotModal'));
                chatbotModal.show();
            });
        }
    });
</script>