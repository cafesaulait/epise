<?php

namespace models;

class Administrateur extends \app\Model
{
    public function __construct()
    {
        $this->table = "administrateur";
        $this->primaryKey = "id_administrateur";

        $this->getConnection();
    }

    public function connexion(string $email, string $mdp): array|false
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `email` = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && password_verify($mdp, $admin['mdp'])) {
            return $admin;
        }
        return false;
    }
}
