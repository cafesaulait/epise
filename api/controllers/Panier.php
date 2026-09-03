<?php

namespace controllers;

class Panier extends \app\Controller
{
    public function index(...$params): void
    {
        if (!isset($_SESSION['utilisateur_id'])) {
            $this->json(['error' => 'Connexion requise'], 401);
            return;
        }

        $this->loadModel('Panier');
        $this->loadModel('Produit');
        $id_utilisateur = (int) $_SESSION['utilisateur_id'];
        $id_panier = $this->Panier->getOrCreate($id_utilisateur);
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch ($method) {
                case 'GET':
                    $this->json([
                        'id_panier' => $id_panier,
                        'items' => $this->Panier->items($id_panier),
                        'total_unites' => $this->Panier->totalUnits($id_panier),
                    ]);
                    return;

                case 'POST':
                    $d = $this->jsonInput();
                    if (empty($d['id_produit']) || empty($d['quantite'])) {
                        throw new \InvalidArgumentException('id_produit et quantite requis');
                    }
                    $id_produit = (int) $d['id_produit'];
                    $produit = $this->Produit->findById($id_produit);
                    if (!$produit) {
                        throw new \InvalidArgumentException('Produit introuvable');
                    }
                    $quantiteDemandee = $this->Panier->quantiteActuelle($id_panier, $id_produit) + (int) $d['quantite'];
                    if ($quantiteDemandee > (int) $produit['stock']) {
                        throw new \InvalidArgumentException("Stock insuffisant : il ne reste que {$produit['stock']} en stock");
                    }
                    $this->Panier->addItem($id_panier, $id_produit, (int) $d['quantite']);
                    $this->json(['message' => 'Produit ajouté au panier'], 201);
                    return;

                case 'PUT':
                    $d = $this->jsonInput();
                    if (empty($d['id_produit']) || !isset($d['quantite'])) {
                        throw new \InvalidArgumentException('id_produit et quantite requis');
                    }
                    $id_produit = (int) $d['id_produit'];
                    $produit = $this->Produit->findById($id_produit);
                    if (!$produit) {
                        throw new \InvalidArgumentException('Produit introuvable');
                    }
                    if ((int) $d['quantite'] > (int) $produit['stock']) {
                        throw new \InvalidArgumentException("Stock insuffisant : il ne reste que {$produit['stock']} en stock");
                    }
                    $this->Panier->updateItem($id_panier, $id_produit, (int) $d['quantite']);
                    $this->json(['message' => 'Quantité mise à jour']);
                    return;

                case 'DELETE':
                    $d = $this->jsonInput();
                    if (empty($d['id_produit'])) {
                        throw new \InvalidArgumentException('id_produit requis');
                    }
                    $this->Panier->removeItem($id_panier, (int) $d['id_produit']);
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
