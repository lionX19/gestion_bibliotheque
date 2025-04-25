<?php
// Démarrer la session
session_start();

// Rediriger vers le dashboard si déjà connecté
if(isset($_SESSION['user_id'])) {
    header("Location: produits/index.php");
    exit;
}

// Traitement du formulaire de connexion
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Dans un environnement réel, vérifiez les identifiants dans la base de données
    // Ici, simple vérification pour démonstration
    if ($username === "admin" && $password === "password") {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = true;
        header("Location: produits/index.php");
        exit;
    } else {
        $error = "Identifiants incorrects";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion de Stocks</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1>Gestion de Stocks</h1>
            <h2>Connexion</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
        </div>
    </div>
</body>
</html>