<?php

namespace controllers;

class Api extends \app\Controller
{
    public function index(): void
    {
        $this->json(['message' => 'API EPISE opérationnelle', 'endpoints' => ['GET /api/produits', 'GET /api/categories', 'POST /api/utilisateurs', 'GET /api/panier', 'POST /api/commandes']]);
    }
    public function produits(...$params): void
    {
        $this->loadModel('Produit');
        $method = $_SERVER['REQUEST_METHOD'];
        $id = isset($params[0]) ? (int)$params[0] : 0;
        try {
            if ($method === 'GET') $data = $id ? $this->Produit->findById($id) : $this->Produit->getAll();
            else {
                http_response_code(405);
                $this->json(['error' => 'Méthode non autorisée'], 405);
                return;
            }
            $this->json($data ?? []);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    public function categories(...$params): void
    {
        $this->loadModel('Categorie');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(['error' => 'Méthode non autorisée'], 405);
            return;
        }
        $id = isset($params[0]) ? (int)$params[0] : 0;
        $this->json($id ? $this->Categorie->findById($id) : $this->Categorie->getAll());
    }
    public function utilisateurs(...$params): void
    {
        $this->loadModel('Utilisateur');
        $method = $_SERVER['REQUEST_METHOD'];
        $id = isset($params[0]) ? (int)$params[0] : 0;
        try {
            if ($method === 'GET') $data = $id ? $this->Utilisateur->findById($id) : $this->Utilisateur->getAll();
            elseif ($method === 'POST') {
                $d = json_decode(file_get_contents('php://input'), true) ?: [];
                foreach (['nom', 'prenom', 'email', 'mdp', 'role'] as $k) {
                    if (empty($d[$k])) throw new \InvalidArgumentException('Champ manquant : ' . $k);
                }
                if ($d['role'] !== 'beneficiaire' && $d['role'] !== 'donateur') throw new \InvalidArgumentException('Rôle invalide.');
                $idNew = $this->Utilisateur->create(trim($d['nom']), trim($d['prenom']), trim($d['email']), $d['mdp'], $d['role']);
                $data = ['id_utilisateur' => $idNew];
            } else {
                $this->json(['error' => 'Méthode non autorisée'], 405);
                return;
            }
            $this->json($data ?? []);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    public function panier(): void
    {
        if (!isset($_SESSION['utilisateur_id'])) {
            $this->json(['error' => 'Connexion requise'], 401);
            return;
        }
        $this->loadModel('Panier');
        $id = $this->Panier->getOrCreate((int)$_SESSION['utilisateur_id']);
        $this->json(['id_panier' => $id, 'items' => $this->Panier->items($id), 'total' => $this->Panier->totalUnits($id), 'limite' => 5]);
    }
    public function commandes(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Méthode non autorisée'], 405);
            return;
        }
        if (!isset($_SESSION['utilisateur_id'])) {
            $this->json(['error' => 'Connexion requise'], 401);
            return;
        }
        try {
            $this->loadModel('Panier');
            $this->loadModel('Commande');
            $pid = $this->Panier->getOrCreate((int)$_SESSION['utilisateur_id']);
            $id = $this->Commande->createFromCart((int)$_SESSION['utilisateur_id'], $pid);
            $this->json(['success' => true, 'id_commande' => $id], 201);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
?>