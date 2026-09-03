<?php

namespace models;

class Don extends \app\Model
{
    public function __construct()
    {
        $this->table = "don";
        $this->primaryKey = "id_don";

        $this->getConnection();
    }

    //créer un don avec la liste de produits
    public function create(int $id_utilisateur, array $produits): int
    {
        if (empty($produits)) {
            throw new \InvalidArgumentException('Le don doit contenir au moins un produit');
        }

        $this->_connexion->begin_transaction();
        try {
            $sql = "INSERT INTO `{$this->table}` (id_utilisateur) VALUES (?)";
            $stmt = $this->_connexion->prepare($sql);
            $stmt->bind_param('i', $id_utilisateur);
            $stmt->execute();
            $id_don = $this->_connexion->insert_id;

            $sql = "INSERT INTO don_produit (id_don, id_produit, nom_produit, description, image, quantite)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->_connexion->prepare($sql);
            foreach ($produits as $p) {
                if (empty($p['nom_produit']) || empty($p['quantite'])) {
                    throw new \InvalidArgumentException('Chaque produit du don doit avoir un nom_produit et une quantite');
                }
                $id_produit = isset($p['id_produit']) ? (int) $p['id_produit'] : null;
                $description = $p['description'] ?? '';
                $image = $p['image'] ?? null;
                $quantite = (int) $p['quantite'];
                $stmt->bind_param('iissss', $id_don, $id_produit, $p['nom_produit'], $description, $image, $quantite);
                $stmt->execute();
            }

            $this->_connexion->commit();
            return $id_don;
        } catch (\Throwable $e) {
            $this->_connexion->rollback();
            throw $e;
        }
    }

    //dons en attente de validation, avec le détail des produits, pour le backoffice
    public function pending(): array
    {
        $sql = "SELECT d.*, u.nom, u.prenom
                FROM `{$this->table}` d
                JOIN utilisateur u ON u.id_utilisateur = d.id_utilisateur
                WHERE d.statut = 'en_attente'
                ORDER BY d.date_don ASC";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->execute();
        $dons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($dons as &$don) {
            $don['produits'] = $this->produitsDuDon((int) $don['id_don']);
        }
        return $dons;
    }

    public function findByUtilisateur(int $id_utilisateur): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE id_utilisateur = ? ORDER BY date_don DESC";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('i', $id_utilisateur);
        $stmt->execute();
        $dons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($dons as &$don) {
            $don['produits'] = $this->produitsDuDon((int) $don['id_don']);
        }
        return $dons;
    }

    public function produitsDuDon(int $id_don): array
    {
        $sql = "SELECT * FROM don_produit WHERE id_don = ?";
        $stmt = $this->_connexion->prepare($sql);
        $stmt->bind_param('i', $id_don);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    //valide ou refuse un don ; si validé, réapprovisionne le stock des produits reconnus
    public function valider(int $id_don, int $id_administrateur, string $statut, ?string $motif_refus = null): void
    {
        $this->_connexion->begin_transaction();
        try {
            $sql = "UPDATE `{$this->table}`
                    SET statut = ?, id_administrateur_validation = ?, date_validation = NOW(), motif_refus = ?
                    WHERE id_don = ?";
            $stmt = $this->_connexion->prepare($sql);
            $stmt->bind_param('sisi', $statut, $id_administrateur, $motif_refus, $id_don);
            $stmt->execute();

            if ($statut === 'valide') {
                $produits = $this->produitsDuDon($id_don);
                $sqlStock = "UPDATE produit SET stock = stock + ? WHERE id_produit = ?";
                $stmtStock = $this->_connexion->prepare($sqlStock);
                foreach ($produits as $p) {
                    if (!empty($p['id_produit'])) {
                        $stmtStock->bind_param('ii', $p['quantite'], $p['id_produit']);
                        $stmtStock->execute();
                    }
                }
            }

            $this->_connexion->commit();
        } catch (\Throwable $e) {
            $this->_connexion->rollback();
            throw $e;
        }
    }
}
