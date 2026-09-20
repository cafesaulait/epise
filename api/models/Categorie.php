<?php

namespace models;

class Categorie extends \app\Model
{
    public function __construct()
    {
        $this->table = "categorie";
        $this->primaryKey = "id_categorie";
        $this->getConnection();
    }

    public function create(array $d): int
    {
        $sql = "INSERT INTO `{$this->table}`
                (`nom`, `image`, `description`)
                VALUES (?, ?, ?)";

        $stmt = $this->_connexion->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException($this->_connexion->error);
        }

        $nom = $d['nom'];
        $image = $d['image'] ?? null;
        $description = $d['description'] ?? '';

        $stmt->bind_param(
            'sss',
            $nom,
            $image,
            $description
        );

        if (!$stmt->execute()) {
            throw new \RuntimeException($stmt->error);
        }

        return $this->_connexion->insert_id;
    }

    public function update(int $id, array $d): void
    {
        $champsPossibles = [
            'nom',
            'image',
            'description'
        ];

        $sets = [];
        $valeurs = [];
        $types = '';

        foreach ($champsPossibles as $champ) {

            if (array_key_exists($champ, $d)) {

                $sets[] = "`{$champ}` = ?";
                $valeurs[] = $d[$champ];
                $types .= 's';
            }
        }

        if (empty($sets)) {
            throw new \InvalidArgumentException(
                'Aucun champ à mettre à jour'
            );
        }

        $sql = "UPDATE `{$this->table}`
                SET " . implode(', ', $sets) . "
                WHERE `{$this->primaryKey}` = ?";

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

    public function delete(int $id): void
    {
        $sql = "DELETE FROM `{$this->table}`
                WHERE `{$this->primaryKey}` = ?";

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