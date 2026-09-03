<?php

namespace controllers;

class Produits extends \app\Controller
{
    public function index(...$params): void
    {
        $this->loadModel('Produit');
        $method = $_SERVER['REQUEST_METHOD'];
        $id = isset($params[0]) ? (int) $params[0] : 0;

        if ($method !== 'GET' && !$this->isAdmin()) {
            $this->json(['error' => 'Action réservée aux administrateurs'], 403);
            return;
        }

        try {
            switch ($method) {
                case 'GET':
                    $data = $id ? $this->Produit->findById($id) : $this->Produit->getAll();
                    if ($id && !$data) {
                        $this->json(['error' => 'Produit introuvable'], 404);
                        return;
                    }
                    $this->json($data);
                    return;

                case 'POST':
                    $d = $this->jsonInput();
                    foreach (['nom', 'stock', 'id_categorie'] as $k) {
                        if (!isset($d[$k]) || $d[$k] === '') throw new \InvalidArgumentException('Champ manquant : ' . $k);
                    }
                    $newId = $this->Produit->create($d);
                    $this->json(['id_produit' => $newId], 201);
                    return;

                case 'PUT':
                    if (!$id) throw new \InvalidArgumentException('ID manquant dans l\'URL (/produits/{id})');
                    if (!$this->Produit->findById($id)) {
                        $this->json(['error' => 'Produit introuvable'], 404);
                        return;
                    }
                    $d = $this->jsonInput();
                    $this->Produit->update($id, $d);
                    $this->json(['message' => 'Produit mis à jour']);
                    return;

                case 'DELETE':
                    if (!$id) throw new \InvalidArgumentException('ID manquant dans l\'URL (/produits/{id})');
                    if (!$this->Produit->findById($id)) {
                        $this->json(['error' => 'Produit introuvable'], 404);
                        return;
                    }
                    $this->Produit->delete($id);
                    http_response_code(204);
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
