<?php

namespace models;

class Horaire extends \app\Model
{
    public function __construct()
    {
        $this->table = "horaires_epise";
        $this->primaryKey = "id_horaire";

        $this->getConnection();
    }

    public function getAllHoraires(): array
    {
        $sql = "SELECT *
                FROM {$this->table}
                ORDER BY jour ASC";

        $resultat = $this->_connexion->query($sql);

        if (!$resultat) {
            throw new \RuntimeException(
                $this->_connexion->error
            );
        }

        return $resultat->fetch_all(MYSQLI_ASSOC);
    }

    public function getByJour(int $jour): ?array
    {
        $sql = "SELECT *
                FROM {$this->table}
                WHERE jour = ?
                LIMIT 1";

        $stmt = $this->_connexion->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException(
                $this->_connexion->error
            );
        }

        $stmt->bind_param('i', $jour);

        if (!$stmt->execute()) {
            throw new \RuntimeException(
                $stmt->error
            );
        }

        $horaire = $stmt
            ->get_result()
            ->fetch_assoc();

        return $horaire ?: null;
    }

    public function mettreAJour(
        int $jour,
        bool $ouvert,
        ?string $ouverture_1,
        ?string $fermeture_1,
        ?string $ouverture_2,
        ?string $fermeture_2
    ): void {
        $sql = "UPDATE {$this->table}
                SET ouvert = ?,
                    ouverture_1 = ?,
                    fermeture_1 = ?,
                    ouverture_2 = ?,
                    fermeture_2 = ?
                WHERE jour = ?";

        $stmt = $this->_connexion->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException(
                $this->_connexion->error
            );
        }

        $stmt->bind_param(
            'issssi',
            $ouvert,
            $ouverture_1,
            $fermeture_1,
            $ouverture_2,
            $fermeture_2,
            $jour
        );

        if (!$stmt->execute()) {
            throw new \RuntimeException(
                $stmt->error
            );
        }
    }

    public function mettreAJourTous(array $horaires): void
    {
        $this->_connexion->begin_transaction();

        try {

            foreach ($horaires as $horaire) {

                $this->mettreAJour(
                    (int) $horaire['jour'],
                    (bool) $horaire['ouvert'],
                    $horaire['ouverture_1'] ?? null,
                    $horaire['fermeture_1'] ?? null,
                    $horaire['ouverture_2'] ?? null,
                    $horaire['fermeture_2'] ?? null
                );
            }

            $this->_connexion->commit();
        } catch (\Throwable $e) {

            $this->_connexion->rollback();

            throw $e;
        }
    }

    public function estOuvert(string $date, string $heure): bool
    {
        $jour = (int) date('N', strtotime($date));
        $heure = date('H:i:s', strtotime($heure));

        $horaire = $this->getByJour($jour);

        if (!$horaire || (int) $horaire['ouvert'] !== 1) {
            return false;
        }

        $dansPremierePlage =
            $horaire['ouverture_1'] !== null
            && $horaire['fermeture_1'] !== null
            && $heure >= $horaire['ouverture_1']
            && $heure <= $horaire['fermeture_1'];

        $dansDeuxiemePlage =
            $horaire['ouverture_2'] !== null
            && $horaire['fermeture_2'] !== null
            && $heure >= $horaire['ouverture_2']
            && $heure <= $horaire['fermeture_2'];

        return $dansPremierePlage || $dansDeuxiemePlage;
    }

    public function estDisponible(string $date, string $heure): bool
    {
        $jour = (int) date('N', strtotime($date));

        $sql = "
        SELECT *
        FROM horaires_epise
        WHERE jour = ?
        LIMIT 1
    ";

        $stmt = $this->_connexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $jour);
        $stmt->execute();

        $resultat = $stmt->get_result();
        $horaire = $resultat->fetch_assoc();

        if (!$horaire || !(int) $horaire['ouvert']) {
            return false;
        }

        $heure = substr($heure, 0, 5);

        $dansPremierCreneau =
            !empty($horaire['ouverture_1']) &&
            !empty($horaire['fermeture_1']) &&
            $heure >= substr($horaire['ouverture_1'], 0, 5) &&
            $heure <= substr($horaire['fermeture_1'], 0, 5);

        $dansDeuxiemeCreneau =
            !empty($horaire['ouverture_2']) &&
            !empty($horaire['fermeture_2']) &&
            $heure >= substr($horaire['ouverture_2'], 0, 5) &&
            $heure <= substr($horaire['fermeture_2'], 0, 5);

        return $dansPremierCreneau || $dansDeuxiemeCreneau;
    }
}
