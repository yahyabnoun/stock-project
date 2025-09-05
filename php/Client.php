<?php
require_once("Personne.php");
class Client extends Personne {
    private ?string $mdp;

    public function __construct(
        $nom,
        $prenom,
        $adr,
        $tele,
        $email,
        $image,
        $mdp = null
    ) {
        Personne::__construct($nom, $prenom, $adr, $tele, $email, $image);
        $this->mdp = $mdp;
    }


    // Getter for mdp
    public function __get(string $property) {
        if ($property === 'mdp') {
            return $this->mdp;
        }
        // Call parent getter for other properties
        return parent::__get($property);
    }


    // Méthode d'authentification simple (si mots de passe en texte brut)
    public static function authenticateSimple($email, $password) {
        try {
            $pdo = Dao::getPDO();
            
            $stmt = $pdo->prepare("SELECT * FROM client WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $client_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($client_data && $password === $client_data['mdp']) {
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("client authentication error: " . $e->getMessage());
            return false;
        }
    }


} 