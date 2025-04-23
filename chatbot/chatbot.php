<?php
require_once '../db/db.php';
require_once 'ChatGPT.php';

class Chatbot
{
    private $config;
    private $conn;
    private $lastResponse;
    private $userId;
    private $chatGPT;

    public function __construct($userId = null)
    {
        global $conn;
        $this->config = require_once 'config.php';
        $this->conn = $conn;
        $this->lastResponse = null;
        $this->userId = $userId;

        // Initialiser ChatGPT
        $this->chatGPT = new ChatGPT($this->config['openai_api_key']);
    }

    public function processMessage($message)
    {
        $message = strtolower(trim($message));
        $response = '';

        try {
            // Vérifier si c'est une salutation
            if ($this->isGreeting($message)) {
                $response = $this->getRandomResponse('greeting');
            }
            // Vérifier si c'est une demande d'aide
            else if ($this->isHelpRequest($message)) {
                $response = $this->getHelpResponse();
            }
            // Recherche de livres
            else if (strpos($message, 'cherche') !== false || strpos($message, 'recherche') !== false || strpos($message, 'trouve') !== false) {
                $response = $this->searchBooks($message);
            }
            // Vérification de disponibilité
            else if (strpos($message, 'disponible') !== false) {
                $response = $this->checkAvailability($message);
            }
            // Information sur les emprunts
            else if (strpos($message, 'emprunt') !== false || strpos($message, 'emprunté') !== false) {
                $response = $this->getLoanInformation($message);
            }
            // Information sur les adhérents
            else if (strpos($message, 'adhérent') !== false || strpos($message, 'membre') !== false) {
                $response = $this->getMemberInformation($message);
            }
            // Statistiques
            else if (strpos($message, 'statistique') !== false || strpos($message, 'stats') !== false) {
                $response = $this->getStatistics($message);
            }
            // Suggestion
            else if (strpos($message, 'suggestion') !== false || strpos($message, 'suggère') !== false) {
                $response = $this->handleSuggestion($message);
            }
            // Pour toutes les autres questions, utiliser ChatGPT
            else {
                $context = "Tu es un assistant de bibliothèque. Tu peux aider les utilisateurs avec :\n" .
                    "- La recherche de livres\n" .
                    "- La vérification de disponibilité\n" .
                    "- Les informations sur les emprunts\n" .
                    "- Les informations sur les adhérents\n" .
                    "- Les statistiques de la bibliothèque\n" .
                    "Réponds de manière concise et professionnelle.";

                $response = $this->chatGPT->getResponse($message, $context);
            }

            // Enregistrer la conversation
            $this->saveConversation($message, $response);

            return $response;
        } catch (Exception $e) {
            error_log("Erreur chatbot: " . $e->getMessage());
            return "Désolé, une erreur est survenue. Veuillez réessayer.";
        }
    }

    private function saveConversation($message, $response)
    {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO chatbot_conversations (user_id, message, response, context)
                VALUES (:user_id, :message, :response, :context)
            ");

            $context = $this->determineContext($message);
            $stmt->bindParam(':user_id', $this->userId);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':response', $response);
            $stmt->bindParam(':context', $context);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de l'enregistrement de la conversation: " . $e->getMessage());
        }
    }

    private function determineContext($message)
    {
        if (strpos($message, 'cherche') !== false || strpos($message, 'recherche') !== false) {
            return 'recherche';
        } else if (strpos($message, 'disponible') !== false) {
            return 'disponibilite';
        } else if (strpos($message, 'emprunt') !== false) {
            return 'emprunt';
        } else if (strpos($message, 'adhérent') !== false) {
            return 'adherent';
        } else if (strpos($message, 'statistique') !== false) {
            return 'statistiques';
        } else if (strpos($message, 'suggestion') !== false) {
            return 'suggestion';
        }
        return 'general';
    }

    private function handleSuggestion($message)
    {
        try {
            // Extraire la suggestion du message
            $suggestion = str_replace(['suggestion', 'suggère', 'je suggère'], '', $message);
            $suggestion = trim($suggestion);

            if (empty($suggestion)) {
                return "Je n'ai pas compris votre suggestion. Pouvez-vous la reformuler ?";
            }

            // Enregistrer la suggestion
            $stmt = $this->conn->prepare("
                INSERT INTO chatbot_suggestions (user_id, suggestion)
                VALUES (:user_id, :suggestion)
            ");
            $stmt->bindParam(':user_id', $this->userId);
            $stmt->bindParam(':suggestion', $suggestion);
            $stmt->execute();

            return "Merci pour votre suggestion ! Elle sera examinée par l'équipe de la bibliothèque.";
        } catch (PDOException $e) {
            error_log("Erreur lors de l'enregistrement de la suggestion: " . $e->getMessage());
            return "Désolé, je n'ai pas pu enregistrer votre suggestion. Veuillez réessayer.";
        }
    }

    private function isGreeting($message)
    {
        $greetings = ['bonjour', 'salut', 'hello', 'hi', 'coucou', 'bonsoir'];
        foreach ($greetings as $greeting) {
            if (strpos($message, $greeting) !== false) {
                return true;
            }
        }
        return false;
    }

    private function isHelpRequest($message)
    {
        $helpKeywords = ['aide', 'help', 'aider', 'assistance', 'que peux-tu faire', 'comment'];
        foreach ($helpKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    private function getHelpResponse()
    {
        return "Je peux vous aider avec les tâches suivantes :\n\n" .
            "1. Rechercher des livres (ex: 'cherche un livre de Victor Hugo')\n" .
            "2. Vérifier la disponibilité (ex: 'est-ce que le livre X est disponible ?')\n" .
            "3. Informations sur les emprunts (ex: 'qui a emprunté le livre X ?')\n" .
            "4. Informations sur les adhérents (ex: 'combien de livres a emprunté l'adhérent X ?')\n" .
            "5. Statistiques (ex: 'montre-moi les statistiques des emprunts')\n" .
            "6. Faire une suggestion (ex: 'je suggère d'ajouter un nouveau livre')\n\n" .
            "Comment puis-je vous aider aujourd'hui ?";
    }

    private function searchBooks($message)
    {
        try {
            // Extraire les mots clés de recherche
            $keywords = str_replace(['cherche', 'recherche', 'trouve', 'livre', 'par'], '', $message);
            $keywords = trim($keywords);

            $sql = "SELECT * FROM livres WHERE titre LIKE :keywords OR auteur LIKE :keywords LIMIT 5";
            $stmt = $this->conn->prepare($sql);
            $searchParam = "%{$keywords}%";
            $stmt->bindParam(':keywords', $searchParam);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($results) > 0) {
                $response = "Voici les livres que j'ai trouvés :\n\n";
                foreach ($results as $book) {
                    $response .= "- {$book['titre']} par {$book['auteur']}\n";
                    $response .= "  Code: {$book['code']}\n";
                    $response .= "  Exemplaires disponibles: {$book['exemplaires']}\n\n";
                }
                return $response;
            } else {
                return "Désolé, je n'ai trouvé aucun livre correspondant à votre recherche.";
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la recherche: " . $e->getMessage());
            return "Désolé, une erreur est survenue lors de la recherche.";
        }
    }

    private function checkAvailability($message)
    {
        try {
            // Extraire le titre du livre
            $title = str_replace(['est-ce que', 'le livre', 'est', 'disponible', '?'], '', $message);
            $title = trim($title);

            $sql = "SELECT l.*, 
                    (l.exemplaires - COUNT(e.id)) as disponibles
                    FROM livres l
                    LEFT JOIN emprunts e ON l.id = e.livre_id AND e.date_retour_effective IS NULL
                    WHERE l.titre LIKE :title
                    GROUP BY l.id";
            $stmt = $this->conn->prepare($sql);
            $searchParam = "%{$title}%";
            $stmt->bindParam(':title', $searchParam);
            $stmt->execute();

            $book = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($book) {
                if ($book['disponibles'] > 0) {
                    return "Oui, le livre \"{$book['titre']}\" est disponible ! Il reste {$book['disponibles']} exemplaire(s).";
                } else {
                    return "Désolé, le livre \"{$book['titre']}\" n'est pas disponible pour le moment.";
                }
            } else {
                return "Désolé, je ne trouve pas de livre avec ce titre.";
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification: " . $e->getMessage());
            return "Désolé, une erreur est survenue lors de la vérification.";
        }
    }

    private function getLoanInformation($message)
    {
        try {
            // Vérifier si c'est une demande sur qui a emprunté un livre
            if (strpos($message, 'qui a emprunté') !== false || strpos($message, 'qui a emprunter') !== false) {
                // Extraire le titre du livre
                $title = str_replace(['qui a emprunté', 'qui a emprunter', 'le livre', '?', '"'], '', $message);
                $title = trim($title);

                $sql = "SELECT l.titre, a.nom, a.prenom, e.date_emprunt, e.date_retour_prevue
                        FROM emprunts e
                        JOIN livres l ON e.livre_id = l.id
                        JOIN adherents a ON e.adherent_id = a.id
                        WHERE l.titre LIKE :title AND e.date_retour_effective IS NULL
                        ORDER BY e.date_emprunt DESC";
                $stmt = $this->conn->prepare($sql);
                $searchParam = "%{$title}%";
                $stmt->bindParam(':title', $searchParam);
                $stmt->execute();

                $emprunts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($emprunts) > 0) {
                    $response = "Le livre \"{$emprunts[0]['titre']}\" est actuellement emprunté par :\n\n";
                    foreach ($emprunts as $emprunt) {
                        $response .= "- {$emprunt['prenom']} {$emprunt['nom']}\n";
                        $response .= "  Emprunté le: " . date('d/m/Y', strtotime($emprunt['date_emprunt'])) . "\n";
                        $response .= "  Retour prévu le: " . date('d/m/Y', strtotime($emprunt['date_retour_prevue'])) . "\n\n";
                    }
                    return $response;
                } else {
                    return "Ce livre n'est pas emprunté actuellement.";
                }
            }
            // Vérifier si c'est une demande sur les emprunts en retard
            else if (strpos($message, 'retard') !== false) {
                $sql = "SELECT l.titre, a.nom, a.prenom, e.date_retour_prevue
                        FROM emprunts e
                        JOIN livres l ON e.livre_id = l.id
                        JOIN adherents a ON e.adherent_id = a.id
                        WHERE e.date_retour_effective IS NULL 
                        AND e.date_retour_prevue < CURDATE()
                        ORDER BY e.date_retour_prevue ASC";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();

                $emprunts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($emprunts) > 0) {
                    $response = "Livres en retard de retour :\n\n";
                    foreach ($emprunts as $emprunt) {
                        $response .= "- \"{$emprunt['titre']}\" emprunté par {$emprunt['prenom']} {$emprunt['nom']}\n";
                        $response .= "  Retour prévu le: " . date('d/m/Y', strtotime($emprunt['date_retour_prevue'])) . "\n\n";
                    }
                    return $response;
                } else {
                    return "Il n'y a pas de livres en retard actuellement.";
                }
            }
            // Si aucune requête spécifique n'est détectée
            return "Je peux vous renseigner sur :\n" .
                "- Qui a emprunté un livre spécifique (ex: 'qui a emprunté le livre X')\n" .
                "- Les emprunts en retard (ex: 'quels sont les emprunts en retard')";
        } catch (PDOException $e) {
            error_log("Erreur lors de la recherche des emprunts: " . $e->getMessage());
            return "Désolé, une erreur est survenue lors de la recherche des emprunts.";
        }
    }

    private function getMemberInformation($message)
    {
        try {
            if (strpos($message, 'combien de livres') !== false) {
                // Extraire le nom de l'adhérent
                $name = str_replace(['combien de livres', 'a emprunté', 'l\'adhérent', '?'], '', $message);
                $name = trim($name);

                $sql = "SELECT a.nom, a.prenom, 
                        COUNT(e.id) as total_emprunts,
                        SUM(CASE WHEN e.date_retour_effective IS NULL THEN 1 ELSE 0 END) as emprunts_en_cours
                        FROM adherents a
                        LEFT JOIN emprunts e ON a.id = e.adherent_id
                        WHERE a.nom LIKE :name OR a.prenom LIKE :name
                        GROUP BY a.id";
                $stmt = $this->conn->prepare($sql);
                $searchParam = "%{$name}%";
                $stmt->bindParam(':name', $searchParam);
                $stmt->execute();

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    return "{$result['prenom']} {$result['nom']} a emprunté {$result['total_emprunts']} livre(s) au total, " .
                        "dont {$result['emprunts_en_cours']} emprunt(s) en cours.";
                } else {
                    return "Je ne trouve pas d'adhérent avec ce nom.";
                }
            }
            return "Que souhaitez-vous savoir sur les adhérents ?";
        } catch (PDOException $e) {
            error_log("Erreur lors de la recherche des informations: " . $e->getMessage());
            return "Désolé, une erreur est survenue lors de la recherche des informations.";
        }
    }

    private function getStatistics($message)
    {
        try {
            if (strpos($message, 'emprunt') !== false) {
                $sql = "SELECT 
                        COUNT(*) as total_emprunts,
                        COUNT(DISTINCT adherent_id) as nb_adherents,
                        COUNT(DISTINCT livre_id) as nb_livres,
                        SUM(CASE WHEN date_retour_effective IS NULL THEN 1 ELSE 0 END) as emprunts_en_cours
                        FROM emprunts";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $stats = $stmt->fetch(PDO::FETCH_ASSOC);

                return "Statistiques des emprunts :\n\n" .
                    "- Nombre total d'emprunts : {$stats['total_emprunts']}\n" .
                    "- Emprunts en cours : {$stats['emprunts_en_cours']}\n" .
                    "- Nombre d'adhérents différents : {$stats['nb_adherents']}\n" .
                    "- Nombre de livres différents : {$stats['nb_livres']}";
            }
            return "Quelles statistiques souhaitez-vous consulter ? (emprunts, livres, adhérents)";
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des statistiques: " . $e->getMessage());
            return "Désolé, une erreur est survenue lors de la récupération des statistiques.";
        }
    }

    private function getRandomResponse($type)
    {
        $responses = $this->config['responses'][$type];
        return $responses[array_rand($responses)];
    }
}
