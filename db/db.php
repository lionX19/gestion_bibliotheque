<?php
// Paramètres de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gesti_biblio');

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Création de la base de données si elle n'existe pas
    $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->exec("USE " . DB_NAME);

    // Création des tables
    $sql = "CREATE TABLE IF NOT EXISTS livres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image VARCHAR(255),
        titre VARCHAR(255) NOT NULL,
        auteur VARCHAR(255) NOT NULL,
        code VARCHAR(100) UNIQUE NOT NULL,
        date_publication DATE,
        exemplaires INT DEFAULT 1
    );
    
    CREATE TABLE IF NOT EXISTS adherents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        photo_profil VARCHAR(255),
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        adresse TEXT,
        telephone VARCHAR(20),
        email VARCHAR(100) UNIQUE
    );
    
    CREATE TABLE IF NOT EXISTS emprunts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        livre_id INT,
        adherent_id INT,
        date_emprunt DATE,
        date_retour_prevue DATE,
        date_retour_effective DATE DEFAULT NULL,
        FOREIGN KEY (livre_id) REFERENCES livres(id) ON DELETE CASCADE,
        FOREIGN KEY (adherent_id) REFERENCES adherents(id) ON DELETE CASCADE
    );
    
    CREATE TABLE IF NOT EXISTS utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'bibliothecaire') DEFAULT 'bibliothecaire'
    );

    CREATE TABLE IF NOT EXISTS chatbot_conversations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        message TEXT NOT NULL,
        response TEXT NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        context VARCHAR(255),
        FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
    );

    CREATE TABLE IF NOT EXISTS chatbot_suggestions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        suggestion TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
    );";

    $conn->exec($sql);

    // Vérifier si l'utilisateur admin existe déjà
    $stmt = $conn->prepare("SELECT COUNT(*) FROM utilisateurs WHERE username = 'admin'");
    $stmt->execute();

    if ($stmt->fetchColumn() == 0) {
        // Créer l'utilisateur admin avec mot de passe '0000'
        $password_hash = password_hash('0000', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO utilisateurs (username, password, role) VALUES ('admin', :password, 'admin')");
        $stmt->bindParam(':password', $password_hash);
        $stmt->execute();
    }
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Fonction pour nettoyer les entrées utilisateur
function clean_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
