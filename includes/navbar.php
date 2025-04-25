
Copier
<header>
    <nav class="navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="../produits/index.php" class="navbar-brand">
                    Gestion de Stocks
                </a>
                
                <button id="mobile-menu-button" class="d-md-none">
                    <i class="fas fa-bars"></i>
                </button>
                
                <ul class="navbar-nav d-none d-md-flex">
                    <li class="nav-item">
                        <a href="../suivi_stock/index.php" class="nav-link">
                            <i class="fas fa-chart-bar"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../produits/index.php" class="nav-link">
                            <i class="fas fa-boxes"></i> Produits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../mouvements/entree.php" class="nav-link">
                            <i class="fas fa-arrow-circle-up"></i> Entrées
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../mouvements/sortie.php" class="nav-link">
                            <i class="fas fa-arrow-circle-down"></i> Sorties
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div id="mobile-menu" class="d-md-none">
        <div class="container">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="../suivi_stock/index.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../produits/index.php" class="nav-link">
                        <i class="fas fa-boxes"></i> Produits
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../mouvements/entree.php" class="nav-link">
                        <i class="fas fa-arrow-circle-up"></i> Entrées
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../mouvements/sortie.php" class="nav-link">
                        <i class="fas fa-arrow-circle-down"></i> Sorties
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

<script>
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.toggle('show');
    });
</script>