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

    public function create(
        int $id_utilisateur,
        string $date_passage,
        string $heure_passage,
        ?string $commentaire,
        array $produits
    ): int {
        if (empty($produits)) {
            throw new \InvalidArgumentException(
                'Le don doit contenir au moins un produit'
            );
        }

        require_once ROOT
            . 'models'
            . DIRECTORY_SEPARATOR
            . 'Horaire.php';

        $horaireModel = new \models\Horaire();

        if (!$horaireModel->estDisponible($date_passage, $heure_passage)) {
            throw new \InvalidArgumentException(
                "La date et l'heure choisies ne sont pas disponibles."
            );
        }

        $this->_connexion->begin_transaction();

        try {

            $sql = "INSERT INTO `{$this->table}`
    (
        id_utilisateur,
        date_passage,
        heure_passage,
        commentaire
    )
    VALUES (?, ?, ?, ?)";

            $stmt = $this->_connexion->prepare($sql);

            if (!$stmt) {
                throw new \RuntimeException($this->_connexion->error);
            }

            $stmt->bind_param(
                'isss',
                $id_utilisateur,
                $date_passage,
                $heure_passage,
                $commentaire
            );

            if (!$stmt->execute()) {
                throw new \RuntimeException($stmt->error);
            }

            $id_don = $this->_connexion->insert_id;

            $sql = "INSERT INTO don_produit
                (
                    id_don,
                    id_produit,
                    id_categorie,
                    categorie_proposee,
                    nom_produit,
                    description,
                    image,
                    quantite
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->_connexion->prepare($sql);

            if (!$stmt) {
                throw new \RuntimeException($this->_connexion->error);
            }


            foreach ($produits as $p) {

                if (
                    empty($p['nom_produit'])
                    || empty($p['quantite'])
                ) {
                    throw new \InvalidArgumentException(
                        'Chaque produit du don doit avoir un nom_produit et une quantite'
                    );
                }


                $id_produit = !empty($p['id_produit'])
                    ? (int) $p['id_produit']
                    : null;

                $id_categorie = !empty($p['id_categorie'])
                    ? (int) $p['id_categorie']
                    : null;

                $categorie_proposee = !empty($p['categorie_proposee'])
                    ? trim($p['categorie_proposee'])
                    : null;

                $nom_produit = trim($p['nom_produit']);

                $description = !empty($p['description'])
                    ? trim($p['description'])
                    : null;

                $image = !empty($p['image'])
                    ? $p['image']
                    : null;

                $quantite = (int) $p['quantite'];

                if ($id_categorie !== null) {
                    $categorie_proposee = null;
                }

                if (
                    $id_categorie === null
                    && empty($categorie_proposee)
                ) {
                    throw new \InvalidArgumentException(
                        'Une catégorie doit être sélectionnée ou proposée.'
                    );
                }

                $stmt->bind_param(
                    'iisssssi',
                    $id_don,
                    $id_produit,
                    $id_categorie,
                    $categorie_proposee,
                    $nom_produit,
                    $description,
                    $image,
                    $quantite
                );

                if (!$stmt->execute()) {
                    throw new \RuntimeException(
                        'Erreur insertion don_produit : ' . $stmt->error
                    );
                }
            }


            $this->_connexion->commit();

            return $id_don;
        } catch (\Throwable $e) {

            $this->_connexion->rollback();

            throw $e;
        }
    }

    public function pending(): array
    {
        $sql = "SELECT d.*, u.nom, u.prenom
                FROM `{$this->table}` d
                JOIN utilisateur u
                    ON u.id_utilisateur = d.id_utilisateur
                WHERE d.statut = 'en_attente'
                ORDER BY d.date_don ASC";

        $stmt = $this->_connexion->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException($this->_connexion->error);
        }

        if (!$stmt->execute()) {
            throw new \RuntimeException($stmt->error);
        }

        $dons = $stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);


        foreach ($dons as &$don) {

            $don['produits'] =
                $this->produitsDuDon(
                    (int) $don['id_don']
                );
        }


        return $dons;
    }

    public function findByUtilisateur(int $id_utilisateur): array
    {
        $sql = "SELECT *
                FROM `{$this->table}`
                WHERE id_utilisateur = ?
                ORDER BY date_don DESC";

        $stmt = $this->_connexion->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException($this->_connexion->error);
        }

        $stmt->bind_param('i', $id_utilisateur);

        if (!$stmt->execute()) {
            throw new \RuntimeException($stmt->error);
        }

        $dons = $stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);


        foreach ($dons as &$don) {

            $don['produits'] =
                $this->produitsDuDon(
                    (int) $don['id_don']
                );
        }


        return $dons;
    }

    public function produitsDuDon(int $id_don): array
    {
        $sql = "SELECT dp.*,
                       c.nom AS categorie_nom
                FROM don_produit dp
                LEFT JOIN categorie c
                    ON c.id_categorie = dp.id_categorie
                WHERE dp.id_don = ?";

        $stmt = $this->_connexion->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException($this->_connexion->error);
        }

        $stmt->bind_param('i', $id_don);

        if (!$stmt->execute()) {
            throw new \RuntimeException($stmt->error);
        }

        return $stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);
    }

    public function creerCategorieDepuisProposition(
        int $id_don_produit
    ): int {

        $this->_connexion->begin_transaction();

        try {

            $sql = "SELECT *
                    FROM don_produit
                    WHERE id_don_produit = ?
                    LIMIT 1";

            $stmt = $this->_connexion->prepare($sql);

            if (!$stmt) {
                throw new \RuntimeException(
                    $this->_connexion->error
                );
            }

            $stmt->bind_param('i', $id_don_produit);

            if (!$stmt->execute()) {
                throw new \RuntimeException($stmt->error);
            }

            $produitDon = $stmt
                ->get_result()
                ->fetch_assoc();


            if (!$produitDon) {
                throw new \RuntimeException(
                    'Produit du don introuvable.'
                );
            }


            if (
                empty($produitDon['categorie_proposee'])
            ) {
                throw new \RuntimeException(
                    'Aucune catégorie n\'a été proposée.'
                );
            }


            $nomCategorie =
                trim($produitDon['categorie_proposee']);


            $sql = "SELECT id_categorie
                    FROM categorie
                    WHERE nom = ?
                    LIMIT 1";

            $stmt = $this->_connexion->prepare($sql);

            if (!$stmt) {
                throw new \RuntimeException(
                    $this->_connexion->error
                );
            }

            $stmt->bind_param('s', $nomCategorie);

            if (!$stmt->execute()) {
                throw new \RuntimeException($stmt->error);
            }

            $categorieExistante = $stmt
                ->get_result()
                ->fetch_assoc();


            if ($categorieExistante) {

                $id_categorie =
                    (int) $categorieExistante['id_categorie'];
            } else {

                $sql = "INSERT INTO categorie
                        (nom, description)
                        VALUES (?, ?)";

                $stmt = $this->_connexion->prepare($sql);

                if (!$stmt) {
                    throw new \RuntimeException(
                        $this->_connexion->error
                    );
                }

                $description =
                    'Catégorie proposée par un donateur.';

                $stmt->bind_param(
                    'ss',
                    $nomCategorie,
                    $description
                );

                if (!$stmt->execute()) {
                    throw new \RuntimeException($stmt->error);
                }

                $id_categorie =
                    $this->_connexion->insert_id;
            }

            $sql = "UPDATE don_produit
                    SET id_categorie = ?,
                        categorie_proposee = NULL
                    WHERE id_don_produit = ?";

            $stmt = $this->_connexion->prepare($sql);

            if (!$stmt) {
                throw new \RuntimeException(
                    $this->_connexion->error
                );
            }

            $stmt->bind_param(
                'ii',
                $id_categorie,
                $id_don_produit
            );

            if (!$stmt->execute()) {
                throw new \RuntimeException($stmt->error);
            }


            $this->_connexion->commit();

            return $id_categorie;
        } catch (\Throwable $e) {

            $this->_connexion->rollback();

            throw $e;
        }
    }

    public function valider(
        int $id_don,
        int $id_administrateur,
        string $statut,
        ?string $motif_refus = null
    ): void {
        if (!in_array($statut, ['valide', 'refuse'], true)) {
            throw new \InvalidArgumentException(
                'Statut de don incorrect.'
            );
        }

        $this->_connexion->begin_transaction();

        try {
            $sql = "
            SELECT *
            FROM don
            WHERE id_don = ?
            AND statut = 'en_attente'
            LIMIT 1
        ";

            $stmt = $this->_connexion->prepare($sql);

            if (!$stmt) {
                throw new \RuntimeException(
                    $this->_connexion->error
                );
            }

            $stmt->bind_param('i', $id_don);
            $stmt->execute();

            $don = $stmt->get_result()->fetch_assoc();

            if (!$don) {
                throw new \RuntimeException(
                    'Don introuvable ou déjà traité.'
                );
            }

            $produits = $this->produitsDuDon($id_don);

            if ($statut === 'valide') {
                foreach ($produits as $produitDon) {
                    $idProduit = $produitDon['id_produit']
                        ? (int) $produitDon['id_produit']
                        : null;

                    $idCategorie = $produitDon['id_categorie']
                        ? (int) $produitDon['id_categorie']
                        : null;

                    if (!$idCategorie) {
                        throw new \RuntimeException(
                            'Le produit "' .
                                $produitDon['nom_produit'] .
                                '" n’a pas encore de catégorie.'
                        );
                    }

                    if ($idProduit) {
                        $quantite = (int) $produitDon['quantite'];

                        $sqlStock = "
                        UPDATE produit
                        SET stock = stock + ?
                        WHERE id_produit = ?
                    ";

                        $stmtStock = $this->_connexion->prepare(
                            $sqlStock
                        );

                        if (!$stmtStock) {
                            throw new \RuntimeException(
                                $this->_connexion->error
                            );
                        }

                        $stmtStock->bind_param(
                            'ii',
                            $quantite,
                            $idProduit
                        );

                        if (!$stmtStock->execute()) {
                            throw new \RuntimeException(
                                $stmtStock->error
                            );
                        }
                    } else {
                        $nouvelIdProduit =
                            $this->creerProduitDepuisDon(
                                $produitDon,
                                $idCategorie
                            );

                        $sqlLien = "
                        UPDATE don_produit
                        SET id_produit = ?
                        WHERE id_don_produit = ?
                    ";

                        $stmtLien = $this->_connexion->prepare(
                            $sqlLien
                        );

                        if (!$stmtLien) {
                            throw new \RuntimeException(
                                $this->_connexion->error
                            );
                        }

                        $idDonProduit =
                            (int) $produitDon['id_don_produit'];

                        $stmtLien->bind_param(
                            'ii',
                            $nouvelIdProduit,
                            $idDonProduit
                        );

                        if (!$stmtLien->execute()) {
                            throw new \RuntimeException(
                                $stmtLien->error
                            );
                        }
                    }
                }
            }

            $sqlUpdate = "
            UPDATE don
            SET statut = ?,
                id_administrateur_validation = ?,
                date_validation = NOW(),
                motif_refus = ?
            WHERE id_don = ?
        ";

            $stmtUpdate = $this->_connexion->prepare($sqlUpdate);

            if (!$stmtUpdate) {
                throw new \RuntimeException(
                    $this->_connexion->error
                );
            }

            $stmtUpdate->bind_param(
                'sisi',
                $statut,
                $id_administrateur,
                $motif_refus,
                $id_don
            );

            if (!$stmtUpdate->execute()) {
                throw new \RuntimeException(
                    $stmtUpdate->error
                );
            }

            $this->_connexion->commit();
        } catch (\Throwable $e) {
            $this->_connexion->rollback();
            throw $e;
        }
    }

    private function creerProduitDepuisDon(
        array $produitDon,
        int $idCategorie
    ): int {
        require_once ROOT
            . 'factories'
            . DIRECTORY_SEPARATOR
            . 'ProduitFactory.php';

        return \factories\ProduitFactory::creerDepuisDon(
            $this->_connexion,
            $produitDon,
            $idCategorie
        );
    }
}
