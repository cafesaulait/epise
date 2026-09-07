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

    public function countByRole(string $role): int
    {
        $sql = "SELECT COUNT(*) AS total FROM `{$this->table}` WHERE `role` = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('s', $role);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function verifierMotDePasse(int $id, string $mdp): bool
    {
        $sql = "SELECT mdp FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $ligne = $stmt->get_result()->fetch_assoc();
        return $ligne && password_verify($mdp, $ligne['mdp']);
    }

    public function changerMotDePasse(int $id, string $nouveauMdp): void
    {
        $hash = password_hash($nouveauMdp, PASSWORD_DEFAULT);
        $sql = "UPDATE `{$this->table}` SET `mdp` = ? WHERE `{$this->primaryKey}` = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('si', $hash, $id);
        if (!$stmt->execute()) {
            throw new \RuntimeException($stmt->error);
        }
    }
}
