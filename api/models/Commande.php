<?php

namespace models;

class Commande extends \app\Model
{
    public function __construct()
    {
        $this->table = "commande";
        $this->primaryKey = "id_commande";

        $this->getConnection();
    }

    public function findByUtilisateur(int $id_utilisateur): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE id_utilisateur = ? ORDER BY date_commande DESC";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('i', $id_utilisateur);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countCommandesDay(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM `{$this->table}` WHERE DATE(date_commande) = CURDATE()";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function countCommandesWeek(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM `{$this->table}` WHERE YEARWEEK(date_commande, 1) = YEARWEEK(CURDATE(), 1)";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    //dernières commandes, avec le nom du client, pour le backoffice
    public function recent(int $limite = 20): array
    {
        $sql = "SELECT c.*, u.nom, u.prenom
                FROM `{$this->table}` c
                JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
                ORDER BY c.date_commande DESC
                LIMIT ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('i', $limite);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function createFromCart(int $id_utilisateur, int $id_panier): int
    {
        $items = $this->_connexion->prepare(
            "SELECT id_produit, quantite FROM panier_produit WHERE id_panier = ?"
        );
        $items->bind_param('i', $id_panier);
        $items->execute();
        $lignes = $items->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($lignes)) {
            throw new \InvalidArgumentException('Le panier est vide');
        }

        $this->_connexion->begin_transaction();
        try {
            foreach ($lignes as $ligne) {
                $sql = "SELECT stock FROM produit WHERE id_produit = ? FOR UPDATE";
                $stmt = $this->_connexion->prepare($sql);
                $stmt->bind_param('i', $ligne['id_produit']);
                $stmt->execute();
                $produit = $stmt->get_result()->fetch_assoc();
                if (!$produit || (int) $produit['stock'] < (int) $ligne['quantite']) {
                    throw new \InvalidArgumentException('Stock insuffisant pour un des produits du panier');
                }
            }

            $sql = "INSERT INTO `{$this->table}` (id_utilisateur, mode) VALUES (?, 'en_ligne')";
            $stmt = $this->_connexion->prepare($sql);
            $stmt->bind_param('i', $id_utilisateur);
            $stmt->execute();
            $id_commande = $this->_connexion->insert_id;

            $sql = "INSERT INTO ligne_commande (id_commande, id_produit, quantite) VALUES (?, ?, ?)";
            $stmt = $this->_connexion->prepare($sql);
            $sqlStock = "UPDATE produit SET stock = stock - ? WHERE id_produit = ?";
            $stmtStock = $this->_connexion->prepare($sqlStock);
            foreach ($lignes as $ligne) {
                $stmt->bind_param('iii', $id_commande, $ligne['id_produit'], $ligne['quantite']);
                $stmt->execute();

                $stmtStock->bind_param('ii', $ligne['quantite'], $ligne['id_produit']);
                $stmtStock->execute();
            }

            $vider = $this->_connexion->prepare("DELETE FROM panier_produit WHERE id_panier = ?");
            $vider->bind_param('i', $id_panier);
            $vider->execute();

            $this->_connexion->commit();
            return $id_commande;
        } catch (\Throwable $e) {
            $this->_connexion->rollback();
            throw $e;
        }
    }
}
