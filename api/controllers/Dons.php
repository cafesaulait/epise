<?php

namespace controllers;

class Dons extends \app\Controller
{
    public function index(...$params): void
    {
        if (!isset($_SESSION['utilisateur_id']) && !$this->isAdmin()) {
            $this->json(['error' => 'Connexion requise'], 401);
            return;
        }

        $this->loadModel('Don');
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
                    $d = $this->jsonInput();
                    if (empty($d['produits']) || !is_array($d['produits'])) {
                        throw new \InvalidArgumentException('Le don doit contenir au moins un produit (champ "produits")');
                    }
                    $id_don = $this->Don->create((int) $_SESSION['utilisateur_id'], $d['produits']);
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
        } catch (\InvalidArgumentException $e) {
            $this->json(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
