<?php
// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'gestion_stock';
$username = 'root';
$password = '';

// Connexion à la base de données
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Configurer le mode d'erreur
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Fonctions pour interagir avec la base de données

// Produits
function getAllProducts($db) {
    $query = "SELECT * FROM produits ORDER BY nom";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductById($db, $id) {
    $query = "SELECT * FROM produits WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function searchProducts($db, $term) {
    $term = "%$term%";
    $query = "SELECT * FROM produits WHERE reference LIKE :term OR nom LIKE :term OR categorie LIKE :term";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':term', $term, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createProduct($db, $data) {
    $query = "INSERT INTO produits (reference, nom, categorie, prix, quantite, seuil) 
              VALUES (:reference, :nom, :categorie, :prix, :quantite, :seuil)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':reference', $data['reference'], PDO::PARAM_STR);
    $stmt->bindParam(':nom', $data['nom'], PDO::PARAM_STR);
    $stmt->bindParam(':categorie', $data['categorie'], PDO::PARAM_STR);
    $stmt->bindParam(':prix', $data['prix'], PDO::PARAM_STR);
    $stmt->bindParam(':quantite', $data['quantite'], PDO::PARAM_INT);
    $stmt->bindParam(':seuil', $data['seuil'], PDO::PARAM_INT);
    $stmt->execute();
    return $db->lastInsertId();
}

function updateProduct($db, $id, $data) {
    $query = "UPDATE produits 
              SET reference = :reference, nom = :nom, categorie = :categorie, 
                  prix = :prix, quantite = :quantite, seuil = :seuil 
              WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':reference', $data['reference'], PDO::PARAM_STR);
    $stmt->bindParam(':nom', $data['nom'], PDO::PARAM_STR);
    $stmt->bindParam(':categorie', $data['categorie'], PDO::PARAM_STR);
    $stmt->bindParam(':prix', $data['prix'], PDO::PARAM_STR);
    $stmt->bindParam(':quantite', $data['quantite'], PDO::PARAM_INT);
    $stmt->bindParam(':seuil', $data['seuil'], PDO::PARAM_INT);
    return $stmt->execute();
}

function deleteProduct($db, $id) {
    // Commencer une transaction pour s'assurer que les deux opérations réussissent ou échouent ensemble
    $db->beginTransaction();
    
    try {
        // Supprimer d'abord les mouvements associés au produit
        $query = "DELETE FROM mouvements WHERE produit_id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Puis supprimer le produit
        $query = "DELETE FROM produits WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Valider la transaction
        $db->commit();
        return true;
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $db->rollBack();
        return false;
    }
}

function getLowStockProducts($db) {
    $query = "SELECT * FROM produits WHERE quantite <= seuil ORDER BY quantite ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateProductQuantity($db, $id, $newQuantity) {
    $query = "UPDATE produits SET quantite = :quantite WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':quantite', $newQuantity, PDO::PARAM_INT);
    return $stmt->execute();
}

// Mouvements
function getAllMovements($db) {
    $query = "SELECT m.*, p.nom as produit_nom, p.reference as produit_reference 
              FROM mouvements m 
              JOIN produits p ON m.produit_id = p.id 
              ORDER BY m.date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMovementById($db, $id) {
    $query = "SELECT * FROM mouvements WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getMovementsByType($db, $type) {
    $query = "SELECT m.*, p.nom as produit_nom, p.reference as produit_reference 
              FROM mouvements m 
              JOIN produits p ON m.produit_id = p.id 
              WHERE m.type = :type 
              ORDER BY m.date DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMovementsByProduct($db, $productId) {
    $query = "SELECT * FROM mouvements WHERE produit_id = :produit_id ORDER BY date DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':produit_id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createMovement($db, $data) {
    // Commencer une transaction
    $db->beginTransaction();
    
    try {
        // Insérer le mouvement
        $query = "INSERT INTO mouvements (produit_id, type, quantite, date, commentaire) 
                  VALUES (:produit_id, :type, :quantite, :date, :commentaire)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':produit_id', $data['produit_id'], PDO::PARAM_INT);
        $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
        $stmt->bindParam(':quantite', $data['quantite'], PDO::PARAM_INT);
        $stmt->bindParam(':date', $data['date'], PDO::PARAM_STR);
        $stmt->bindParam(':commentaire', $data['commentaire'], PDO::PARAM_STR);
        $stmt->execute();
        
        // Mettre à jour la quantité du produit
        $product = getProductById($db, $data['produit_id']);
        $newQuantity = $product['quantite'];
        
        if ($data['type'] === 'entry') {
            $newQuantity += $data['quantite'];
        } else if ($data['type'] === 'exit') {
            $newQuantity -= $data['quantite'];
            // S'assurer que la quantité ne devient pas négative
            if ($newQuantity < 0) {
                $newQuantity = 0;
            }
        }
        
        updateProductQuantity($db, $data['produit_id'], $newQuantity);
        
        // Valider la transaction
        $db->commit();
        return $db->lastInsertId();
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $db->rollBack();
        throw $e;
    }
}

function deleteMovement($db, $id) {
    // Commencer une transaction
    $db->beginTransaction();
    
    try {
        // Récupérer les informations du mouvement
        $movement = getMovementById($db, $id);
        if (!$movement) {
            throw new Exception("Mouvement non trouvé");
        }
        
        // Récupérer les informations du produit
        $product = getProductById($db, $movement['produit_id']);
        if (!$product) {
            throw new Exception("Produit non trouvé");
        }
        
        // Calculer la nouvelle quantité
        $newQuantity = $product['quantite'];
        
        if ($movement['type'] === 'entry') {
            $newQuantity -= $movement['quantite'];
            // S'assurer que la quantité ne devient pas négative
            if ($newQuantity < 0) {
                $newQuantity = 0;
            }
        } else if ($movement['type'] === 'exit') {
            $newQuantity += $movement['quantite'];
        }
        
        // Mettre à jour la quantité du produit
        updateProductQuantity($db, $movement['produit_id'], $newQuantity);
        
        // Supprimer le mouvement
        $query = "DELETE FROM mouvements WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Valider la transaction
        $db->commit();
        return true;
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $db->rollBack();
        return false;
    }
}

function getRecentMovements($db, $limit = 5) {
    $query = "SELECT m.*, p.nom as produit_nom, p.reference as produit_reference 
              FROM mouvements m 
              JOIN produits p ON m.produit_id = p.id 
              ORDER BY m.date DESC 
              LIMIT :limit";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Statistiques et résumés
function getStockSummary($db) {
    $summary = [
        'total_products' => 0,
        'low_stock' => 0,
        'out_of_stock' => 0,
        'total_value' => 0
    ];
    
    $query = "SELECT COUNT(*) as total FROM produits";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $summary['total_products'] = $result['total'];
    
    $query = "SELECT COUNT(*) as low_stock FROM produits WHERE quantite > 0 AND quantite <= seuil";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $summary['low_stock'] = $result['low_stock'];
    
    $query = "SELECT COUNT(*) as out_of_stock FROM produits WHERE quantite = 0";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $summary['out_of_stock'] = $result['out_of_stock'];
    
    $query = "SELECT SUM(prix * quantite) as total_value FROM produits";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $summary['total_value'] = $result['total_value'] ?: 0;
    
    return $summary;
}
?>