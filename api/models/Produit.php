<?php

namespace models;

class Produit extends \app\Model
{
    public function __construct()
    {
        $this->table = "produit";
        $this->primaryKey = "id_produit";

        $this->getConnection();
    }

    public function countProducts(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM `{$this->table}`";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    // seuil de stock faible : 10 unités, à ajuster si besoin
    public function countStocksFaibles(int $seuil = 10): int
    {
        $sql = "SELECT COUNT(*) AS total FROM `{$this->table}` WHERE stock <= ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('i', $seuil);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function stockFaiblesDashboard(int $seuil = 10): array
    {
        $sql = "SELECT p.*, c.nom AS categorie_nom
                FROM `{$this->table}` p
                JOIN categorie c ON c.id_categorie = p.id_categorie
                WHERE p.stock <= ?
                ORDER BY p.stock ASC";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('i', $seuil);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    //créer un produit
    public function create(array $d): int
    {
        $sql = "INSERT INTO `{$this->table}` (`nom`, `stock`, `id_categorie`, `description`, `image`) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->_connexion->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException($this->_connexion->error);
        }

        $nom = $d['nom'];
        $stock = (int) $d['stock'];
        $id_categorie = (int) $d['id_categorie'];
        $description = $d['description'] ?? '';
        $image = $d['image'] ?? '';

        $stmt->bind_param('sisss', $nom, $stock, $id_categorie, $description, $image);
        if (!$stmt->execute()) {
            throw new \RuntimeException($stmt->error);
        }

        return $this->_connexion->insert_id;
    }

    //modifier un produit
    public function update(int $id, array $d): void
    {
        $champsPossibles = ['nom', 'stock', 'id_categorie', 'description', 'image'];
        $sets = [];
        $valeurs = [];
        $types = '';

        foreach ($champsPossibles as $champ) {
            if (array_key_exists($champ, $d)) {
                $sets[] = "`{$champ}` = ?";
                $valeurs[] = $d[$champ];
                $types .= in_array($champ, ['stock', 'id_categorie']) ? 'i' : 's';
            }
        }

        if (empty($sets)) {
            throw new \InvalidArgumentException('Aucun champ à mettre à jour');
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets) . " WHERE `{$this->primaryKey}` = ?";
        $stmt = $this->_connexion->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException($this->_connexion->error);
        }

        $valeurs[] = $id;
        $types .= 'i';
        $stmt->bind_param($types, ...$valeurs);

        if (!$stmt->execute()) {
            throw new \RuntimeException($stmt->error);
        }
    }

    //supprimer un produit
    public function delete(int $id): void
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";
        $stmt = $this->_connexion->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException($this->_connexion->error);
        }

        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new \RuntimeException($stmt->error);
        }
    }
}
