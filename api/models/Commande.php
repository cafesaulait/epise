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

    /**
     * Vérifie combien de commandes le bénéficiaire
     * a effectuées cette semaine.
     *
     * Les commandes annulées ne sont pas comptées.
     */
    public function nombreCommandesSemaine(int $id_utilisateur): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM commande
                WHERE id_utilisateur = ?
                AND statut <> 'annulee'
                AND YEARWEEK(date_commande, 1) = YEARWEEK(CURDATE(), 1)";

        $stmt = $this->_connexion->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException($this->_connexion->error);
        }

        $stmt->bind_param('i', $id_utilisateur);
        $stmt->execute();

        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    /**
     * Crée une commande à partir du panier.
     *
     * Cette opération :
     * - vérifie que le panier n'est pas vide
     * - vérifie le stock
     * - crée la commande
     * - crée les lignes de commande
     * - retire les produits du stock
     * - supprime le panier
     *
     * Tout est fait dans une transaction.
     */
    public function createFromCart(
        int $id_utilisateur,
        int $id_panier
    ): int {

        // Récupération des produits du panier
        $sql = "SELECT id_produit, quantite
                FROM panier_produit
                WHERE id_panier = ?";

        $stmt = $this->_connexion->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException($this->_connexion->error);
        }

        $stmt->bind_param('i', $id_panier);
        $stmt->execute();

        $lignes = $stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);

        if (empty($lignes)) {
            throw new \InvalidArgumentException(
                'Le panier est vide'
            );
        }

        // Vérification de la limite de 5 produits
        $total = 0;

        foreach ($lignes as $ligne) {
            $total += (int) $ligne['quantite'];
        }

        if ($total > 5) {
            throw new \InvalidArgumentException(
                'Une commande ne peut pas contenir plus de 5 produits.'
            );
        }

        // Vérification des commandes de la semaine
        if ($this->nombreCommandesSemaine($id_utilisateur) >= 2) {
            throw new \InvalidArgumentException(
                'Vous avez déjà effectué vos 2 commandes autorisées cette semaine.'
            );
        }

        $this->_connexion->begin_transaction();

        try {

            /*
             * On verrouille les produits pendant la transaction
             * afin d'éviter qu'une autre commande utilise le même stock.
             */
            $sqlProduit = "SELECT stock
                           FROM produit
                           WHERE id_produit = ?
                           FOR UPDATE";

            $stmtProduit = $this->_connexion->prepare($sqlProduit);

            if (!$stmtProduit) {
                throw new \RuntimeException(
                    $this->_connexion->error
                );
            }

            foreach ($lignes as $ligne) {

                $id_produit = (int) $ligne['id_produit'];
                $quantite = (int) $ligne['quantite'];

                $stmtProduit->bind_param(
                    'i',
                    $id_produit
                );

                $stmtProduit->execute();

                $produit = $stmtProduit
                    ->get_result()
                    ->fetch_assoc();

                if (!$produit) {
                    throw new \InvalidArgumentException(
                        'Un produit de votre panier n\'existe plus.'
                    );
                }

                if ((int) $produit['stock'] < $quantite) {
                    throw new \InvalidArgumentException(
                        'Le stock d\'un des produits de votre panier est insuffisant.'
                    );
                }
            }

            // Création de la commande
            $sqlCommande = "INSERT INTO commande
                            (id_utilisateur, mode, statut, date_commande)
                            VALUES (?, 'en_ligne', 'en_attente', NOW())";

            $stmtCommande = $this->_connexion->prepare(
                $sqlCommande
            );

            if (!$stmtCommande) {
                throw new \RuntimeException(
                    $this->_connexion->error
                );
            }

            $stmtCommande->bind_param(
                'i',
                $id_utilisateur
            );

            $stmtCommande->execute();

            $id_commande = $this->_connexion->insert_id;

            // Création des lignes de commande
            $sqlLigne = "INSERT INTO ligne_commande
                         (id_commande, id_produit, quantite)
                         VALUES (?, ?, ?)";

            $stmtLigne = $this->_connexion->prepare(
                $sqlLigne
            );

            // Diminution du stock
            $sqlStock = "UPDATE produit
                         SET stock = stock - ?
                         WHERE id_produit = ?";

            $stmtStock = $this->_connexion->prepare(
                $sqlStock
            );

            foreach ($lignes as $ligne) {

                $id_produit = (int) $ligne['id_produit'];
                $quantite = (int) $ligne['quantite'];

                // Ligne commande
                $stmtLigne->bind_param(
                    'iii',
                    $id_commande,
                    $id_produit,
                    $quantite
                );

                $stmtLigne->execute();

                // Stock
                $stmtStock->bind_param(
                    'ii',
                    $quantite,
                    $id_produit
                );

                $stmtStock->execute();
            }

            // Suppression du contenu du panier
            $vider = $this->_connexion->prepare(
                "DELETE FROM panier_produit
                 WHERE id_panier = ?"
            );

            $vider->bind_param(
                'i',
                $id_panier
            );

            $vider->execute();

            // Suppression du panier lui-même
            $supprimerPanier = $this->_connexion->prepare(
                "DELETE FROM panier
                 WHERE id_panier = ?"
            );

            $supprimerPanier->bind_param(
                'i',
                $id_panier
            );

            $supprimerPanier->execute();

            $this->_connexion->commit();

            return $id_commande;

        } catch (\Throwable $e) {

            $this->_connexion->rollback();

            throw $e;
        }
    }

    /**
     * Récupère toutes les commandes d'un utilisateur.
     */
    public function findByUtilisateur(
        int $id_utilisateur
    ): array {

        $sql = "SELECT
                    c.id_commande,
                    c.id_utilisateur,
                    c.date_commande,
                    c.mode,
                    c.statut
                FROM commande c
                WHERE c.id_utilisateur = ?
                ORDER BY c.date_commande DESC";

        $stmt = $this->_connexion->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException(
                $this->_connexion->error
            );
        }

        $stmt->bind_param(
            'i',
            $id_utilisateur
        );

        $stmt->execute();

        $commandes = $stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);

        foreach ($commandes as &$commande) {

            $commande['produits'] =
                $this->produitsCommande(
                    (int) $commande['id_commande']
                );
        }

        return $commandes;
    }

    /**
     * Récupère les produits d'une commande.
     */
    public function produitsCommande(
        int $id_commande
    ): array {

        $sql = "SELECT
                    lc.id_produit,
                    lc.quantite,
                    p.nom,
                    p.image
                FROM ligne_commande lc
                JOIN produit p
                    ON p.id_produit = lc.id_produit
                WHERE lc.id_commande = ?";

        $stmt = $this->_connexion->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException(
                $this->_connexion->error
            );
        }

        $stmt->bind_param(
            'i',
            $id_commande
        );

        $stmt->execute();

        return $stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Récupère une commande appartenant à un utilisateur.
     */
    public function findByIdUtilisateur(
        int $id_commande,
        int $id_utilisateur
    ): array|false {

        $sql = "SELECT *
                FROM commande
                WHERE id_commande = ?
                AND id_utilisateur = ?";

        $stmt = $this->_connexion->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException(
                $this->_connexion->error
            );
        }

        $stmt->bind_param(
            'ii',
            $id_commande,
            $id_utilisateur
        );

        $stmt->execute();

        return $stmt
            ->get_result()
            ->fetch_assoc();
    }

    /**
     * Annule une commande.
     *
     * Les produits sont remis en stock.
     * La commande est conservée avec le statut "annulee".
     */
    public function annuler(
        int $id_commande,
        int $id_utilisateur
    ): void {

        $commande = $this->findByIdUtilisateur(
            $id_commande,
            $id_utilisateur
        );

        if (!$commande) {
            throw new \InvalidArgumentException(
                'Commande introuvable.'
            );
        }

        if ($commande['statut'] === 'recuperee') {
            throw new \InvalidArgumentException(
                'Cette commande a déjà été récupérée et ne peut plus être annulée.'
            );
        }

        if ($commande['statut'] === 'annulee') {
            throw new \InvalidArgumentException(
                'Cette commande est déjà annulée.'
            );
        }

        $this->_connexion->begin_transaction();

        try {

            $sql = "SELECT id_produit, quantite
                    FROM ligne_commande
                    WHERE id_commande = ?";

            $stmt = $this->_connexion->prepare($sql);

            $stmt->bind_param(
                'i',
                $id_commande
            );

            $stmt->execute();

            $lignes = $stmt
                ->get_result()
                ->fetch_all(MYSQLI_ASSOC);

            // Remettre les produits en stock
            $sqlStock = "UPDATE produit
                         SET stock = stock + ?
                         WHERE id_produit = ?";

            $stmtStock = $this->_connexion->prepare(
                $sqlStock
            );

            foreach ($lignes as $ligne) {

                $quantite = (int) $ligne['quantite'];
                $id_produit = (int) $ligne['id_produit'];

                $stmtStock->bind_param(
                    'ii',
                    $quantite,
                    $id_produit
                );

                $stmtStock->execute();
            }

            // Marquer la commande comme annulée
            $sqlAnnulation = "UPDATE commande
                              SET statut = 'annulee'
                              WHERE id_commande = ?
                              AND id_utilisateur = ?";

            $stmtAnnulation = $this->_connexion->prepare(
                $sqlAnnulation
            );

            $stmtAnnulation->bind_param(
                'ii',
                $id_commande,
                $id_utilisateur
            );

            $stmtAnnulation->execute();

            $this->_connexion->commit();

        } catch (\Throwable $e) {

            $this->_connexion->rollback();

            throw $e;
        }
    }
}