<?php

namespace models;

class Utilisateur extends \app\Model
{
    public function __construct()
    {
        $this->table = "utilisateur";
        $this->primaryKey = "id_utilisateur";

        $this->getConnection();
    }

    //créer un utilisateur et renvoyer la ligne créée (sans mdp), pour connexion directe
    public function create(string $nom, string $prenom, string $email, string $mdp, string $role): array
    {
        $hash = password_hash($mdp, PASSWORD_DEFAULT);

        $sql = "INSERT INTO `{$this->table}` (`nom`, `prenom`, `email`, `mdp`, `role`) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->_connexion->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException($this->_connexion->error);
        }
        $stmt->bind_param('sssss', $nom, $prenom, $email, $hash, $role);
        if (!$stmt->execute()) {
            throw new \RuntimeException($stmt->error);
        }

        $utilisateur = $this->findById($this->_connexion->insert_id);
        unset($utilisateur['mdp']);
        return $utilisateur;
    }

    //verifier email et mot de passe pour l'authentification
    public function authenticate(string $email, string $mdp): array|false
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `email` = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $utilisateur = $stmt->get_result()->fetch_assoc();

        if (!$utilisateur || !password_verify($mdp, $utilisateur['mdp'])) {
            return false;
        }

        unset($utilisateur['mdp']);
        return $utilisateur;
    }

    public function emailExists(string $email): bool
    {
        $sql = "SELECT id_utilisateur FROM `{$this->table}` WHERE `email` = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return (bool) $stmt->get_result()->fetch_assoc();
    }

    //modifier nom/prenom/email, renvoie la ligne à jour (sans mdp)
    public function update(int $id, string $nom, string $prenom, string $email): array
    {
        $sql = "UPDATE `{$this->table}` SET `nom` = ?, `prenom` = ?, `email` = ? WHERE `{$this->primaryKey}` = ?";
        $stmt = $this->_connexion->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException($this->_connexion->error);
        }
        $stmt->bind_param('sssi', $nom, $prenom, $email, $id);
        if (!$stmt->execute()) {
            throw new \RuntimeException($stmt->error);
        }

        $utilisateur = $this->findById($id);
        unset($utilisateur['mdp']);
        return $utilisateur;
    }

    //compte les utilisateurs par rôle, pour le dashboard admin
    public function countByRole(string $role): int
    {
        $sql = "SELECT COUNT(*) AS total FROM `{$this->table}` WHERE `role` = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('s', $role);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }
}
