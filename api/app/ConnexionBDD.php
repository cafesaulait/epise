<?php
namespace app;

/*Design pattern : singleton*/

class ConnexionBDD
{
    private static ?ConnexionBDD $instance = null;
    private \mysqli $connexion;

    private function __construct()
    {
        $this->connexion = new \mysqli(
            getenv('EPISE_DB_HOST') ?: 'localhost',
            getenv('EPISE_DB_USER') ?: 'root',
            getenv('EPISE_DB_PASSWORD') ?: '',
            getenv('EPISE_DB_NAME') ?: 'episebdd'
        );

        if ($this->connexion->connect_error) {
            throw new \Exception('Erreur de connexion : ' . $this->connexion->connect_error);
        }

        $this->connexion->set_charset('utf8mb4');
    }

    public static function getInstance(): ConnexionBDD
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnexion(): \mysqli
    {
        return $this->connexion;
    }
}
?>
