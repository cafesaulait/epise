<?php

namespace controllers;

class Dons extends \app\Controller
{
    private function verifierDateEtHeure(
        string $date_passage,
        string $heure_passage
    ): void {
        $date = \DateTime::createFromFormat(
            'Y-m-d',
            $date_passage
        );

        $heure = \DateTime::createFromFormat(
            'H:i',
            $heure_passage
        );

        if (
            !$date
            || $date->format('Y-m-d') !== $date_passage
        ) {
            throw new \InvalidArgumentException(
                'La date de passage est invalide.'
            );
        }

        if (
            !$heure
            || $heure->format('H:i') !== $heure_passage
        ) {
            throw new \InvalidArgumentException(
                'L’heure de passage est invalide.'
            );
        }

        $maintenant = new \DateTime(
            'now',
            new \DateTimeZone('Pacific/Noumea')
        );

        $passage = new \DateTime(
            $date_passage . ' ' . $heure_passage,
            new \DateTimeZone('Pacific/Noumea')
        );

        if ($passage <= $maintenant) {
            throw new \InvalidArgumentException(
                'La date et l’heure de passage doivent être dans le futur.'
            );
        }
    }

    public function index(...$params): void
    {
        if (!isset($_SESSION['utilisateur_id']) && !$this->isAdmin()) {
            $this->json(['error' => 'Connexion requise'], 401);
            return;
        }

        $this->loadModel('Don');
        $this->loadModel('Horaire');
        $method = $_SERVER['REQUEST_METHOD'];
        $id = isset($params[0]) ? (int) $params[0] : 0;

        try {
            switch ($method) {
                case 'GET':
                    if ($this->isAdmin()) {
                        $data = $id ? $this->Don->findById($id) : $this->Don->getAll();
                    } else {
                        $data = $this->Don->findByUtilisateur((int) $_SESSION['utilisateur_id']);
                    }
                    if ($id && !$data) {
                        $this->json(['error' => 'Don introuvable'], 404);
                        return;
                    }
                    $this->json($data);
                    return;

                case 'POST':
                    if (!isset($_SESSION['utilisateur_id'])) {
                        $this->json(['error' => 'Connexion requise'], 401);
                        return;
                    }
                    $this->loadModel('Utilisateur');

                    $utilisateur = $this->Utilisateur->findById(
                        (int) $_SESSION['utilisateur_id']
                    );

                    if (!$utilisateur || $utilisateur['role'] !== 'donateur') {
                        $this->json([
                            'error' => 'Seuls les donateurs peuvent proposer un don.'
                        ], 403);
                        return;
                    }
                    $d = $this->jsonInput();

                    if (empty($d['date_passage'])) {
                        throw new \InvalidArgumentException(
                            'La date de passage est obligatoire.'
                        );
                    }

                    if (empty($d['heure_passage'])) {
                        throw new \InvalidArgumentException(
                            'L\'heure de passage est obligatoire.'
                        );
                    }

                    $this->verifierDateEtHeure(
                        $d['date_passage'],
                        $d['heure_passage']
                    );

                    if (!$this->Horaire->estOuvert(
                        $d['date_passage'],
                        $d['heure_passage']
                    )) {
                        throw new \InvalidArgumentException(
                            'Cette date et cette heure ne correspondent pas aux horaires d’ouverture de l’EPISE.'
                        );
                    }
                    
                    if (empty($d['produits']) || !is_array($d['produits'])) {
                        throw new \InvalidArgumentException('Le don doit contenir au moins un produit (champ "produits")');
                    }
                    $id_don = $this->Don->create(
                        (int) $_SESSION['utilisateur_id'],
                        $d['date_passage'] ?? '',
                        $d['heure_passage'] ?? '',
                        $d['commentaire'] ?? null,
                        $d['produits']
                    );
                    $this->json(['id_don' => $id_don], 201);
                    return;

                case 'PUT':
                    if (!$this->isAdmin()) {
                        $this->json(['error' => 'Action réservée aux administrateurs'], 403);
                        return;
                    }
                    if (!$id) throw new \InvalidArgumentException('ID manquant dans l\'URL (/dons/{id})');
                    $d = $this->jsonInput();
                    if (empty($d['statut']) || !in_array($d['statut'], ['valide', 'refuse'], true)) {
                        throw new \InvalidArgumentException('statut doit être "valide" ou "refuse"');
                    }
                    $this->Don->valider($id, (int) $_SESSION['admin_id'], $d['statut'], $d['motif_refus'] ?? null);
                    $this->json(['message' => 'Don mis à jour']);
                    return;

                default:
                    $this->json(['error' => 'Méthode non autorisée'], 405);
                    return;
            }
        } catch (\Throwable $e) {
            error_log(
                '[EPISE DON] '
                    . $e->getMessage()
                    . ' dans '
                    . $e->getFile()
                    . ' ligne '
                    . $e->getLine()
            );

            $this->json([
                'error' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
