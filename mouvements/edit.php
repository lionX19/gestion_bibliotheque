<?php
require_once '../includes/auth.php';
requireLogin();

require_once '../db/db.php';

// Note: L'édition des mouvements n'est pas recommandée car cela perturberait l'historique et les niveaux de stock
// Cette page redirige vers la page des mouvements avec un message d'erreur

header("Location: ../suivi_stock/index.php?error=edit_not_allowed");
exit;
?>