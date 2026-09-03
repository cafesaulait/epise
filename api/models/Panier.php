<?php

namespace models;

class Panier extends \app\Model
{
    public function __construct()
    {
        $this->table = "panier";
        $this->primaryKey = "id_panier";

        $this->getConnection();
    }

    public function getOrCreate(int $id_utilisateur): int
    {
        $sql = "SELECT id_panier FROM `{$this->table}` WHERE id_utilisateur = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('i', $id_utilisateur);
        $stmt->execute();
        $panier = $stmt->get_result()->fetch_assoc();

        if ($panier) {
            return (int) $panier['id_panier'];
        }

        $sql = "INSERT INTO `{$this->table}` (id_utilisateur) VALUES (?)";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('i', $id_utilisateur);
        $stmt->execute();
        return $this->_connexion->insert_id;
    }

    public function items(int $id_panier): array
    {
        $sql = "SELECT pp.id_produit, pp.quantite, p.nom, p.description, p.image, p.stock
                FROM panier_produit pp
                JOIN produit p ON p.id_produit = pp.id_produit
                WHERE pp.id_panier = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('i', $id_panier);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function totalUnits(int $id_panier): int
    {
        $sql = "SELECT COALESCE(SUM(quantite), 0) AS total FROM panier_produit WHERE id_panier = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('i', $id_panier);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function quantiteActuelle(int $id_panier, int $id_produit): int
    {
        $sql = "SELECT quantite FROM panier_produit WHERE id_panier = ? AND id_produit = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('ii', $id_panier, $id_produit);
        $stmt->execute();
        $ligne = $stmt->get_result()->fetch_assoc();
        return $ligne ? (int) $ligne['quantite'] : 0;
    }

    //ajoute un produit au panier
    public function addItem(int $id_panier, int $id_produit, int $quantite): void
    {
        $sql = "INSERT INTO panier_produit (id_panier, id_produit, quantite) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantite = quantite + VALUES(quantite)";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('iii', $id_panier, $id_produit, $quantite);
        $stmt->execute();
    }

    //fixe la quantité d'un produit dans le panier
    public function updateItem(int $id_panier, int $id_produit, int $quantite): void
    {
        if ($quantite <= 0) {
            $this->removeItem($id_panier, $id_produit);
            return;
        }
        $sql = "UPDATE panier_produit SET quantite = ? WHERE id_panier = ? AND id_produit = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('iii', $quantite, $id_panier, $id_produit);
        $stmt->execute();
    }

    public function removeItem(int $id_panier, int $id_produit): void
    {
        $sql = "DELETE FROM panier_produit WHERE id_panier = ? AND id_produit = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('ii', $id_panier, $id_produit);
        $stmt->execute();
    }
}
