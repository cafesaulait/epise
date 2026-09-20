<?php

namespace factories;

use mysqli;
use RuntimeException;

/*Design pattern : Fabrique*/

class ProduitFactory
{
    public static function creerDepuisDon(
        mysqli $connexion,
        array $produitDon,
        int $idCategorie
    ): int {
        $nom = trim($produitDon['nom_produit'] ?? '');
        $stock = (int) ($produitDon['quantite'] ?? 0);
        $description = $produitDon['description'] ?? '';
        $image = $produitDon['image'] ?? null;

        if ($nom === '') {
            throw new \InvalidArgumentException(
                'Le nom du produit est obligatoire.'
            );
        }

        if ($stock <= 0) {
            throw new \InvalidArgumentException(
                'La quantité du produit doit être supérieure à zéro.'
            );
        }

        if ($idCategorie <= 0) {
            throw new \InvalidArgumentException(
                'Une catégorie valide est obligatoire.'
            );
        }

        $sql = "
            INSERT INTO produit
            (
                nom,
                stock,
                id_categorie,
                description,
                image
            )
            VALUES (?, ?, ?, ?, ?)
        ";

        $stmt = $connexion->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException(
                'Erreur préparation création produit : '
                    . $connexion->error
            );
        }

        $stmt->bind_param(
            'siiss',
            $nom,
            $stock,
            $idCategorie,
            $description,
            $image
        );

        if (!$stmt->execute()) {
            throw new RuntimeException(
                'Erreur création produit : '
                    . $stmt->error
            );
        }

        return (int) $connexion->insert_id;
    }
}
