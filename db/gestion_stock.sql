
-- Structure de la base de données pour la gestion de stock

CREATE DATABASE IF NOT EXISTS gestion_stock;
USE gestion_stock;

-- Table des produits
CREATE TABLE IF NOT EXISTS produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(20) NOT NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    categorie VARCHAR(50) NOT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    quantite INT NOT NULL DEFAULT 0,
    seuil INT NOT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des mouvements de stock
CREATE TABLE IF NOT EXISTS mouvements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT NOT NULL,
    type ENUM('entry', 'exit') NOT NULL,
    quantite INT NOT NULL,
    date DATE NOT NULL,
    commentaire TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
);

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_utilisateur VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    est_admin BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Données de démonstration

-- Utilisateur administrateur (mot de passe: password)
INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe, est_admin) VALUES
('admin', 'admin@example.com', '$2y$10$rBs5GXMQD.H8YFxOu2NVWOvRwJ1MUMGWHkGd/RFY45dCY.8LC7wY.', TRUE);

-- Produits de démonstration
INSERT INTO produits (reference, nom, categorie, prix, quantite, seuil) VALUES
('PRD001', 'Ordinateur portable', 'Informatique', 899.99, 15, 5),
('PRD002', 'Smartphone', 'Téléphonie', 499.99, 8, 10),
('PRD003', 'Imprimante', 'Informatique', 149.99, 3, 5),
('PRD004', 'Écran 24"', 'Informatique', 199.99, 0, 3),
('PRD005', 'Clavier sans fil', 'Accessoires', 39.99, 20, 8);

-- Mouvements de démonstration
INSERT INTO mouvements (produit_id, type, quantite, date, commentaire) VALUES
(1, 'entry', 5, '2023-06-10', 'Réapprovisionnement fournisseur A'),
(2, 'exit', 2, '2023-06-11', 'Vente client B'),
(3, 'entry', 10, '2023-06-12', 'Livraison initiale'),
(4, 'exit', 3, '2023-06-13', 'Transfert à la succursale C'),
(5, 'entry', 15, '2023-06-14', 'Réception commande fournisseur D');