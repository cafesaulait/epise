<?php

namespace controllers;

class Commandes extends \app\Controller
{
    public function index(...$params): void
    {
        if (!isset($_SESSION['utilisateur_id']) && !$this->isAdmin()) {
            $this->json(['error' => 'Connexion requise'], 401);
            return;
        }

        $this->loadModel('Commande');
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch ($method) {
                case 'GET':
                    if ($this->isAdmin()) {
                        $this->json($this->Commande->getAll());
                        return;
                    }
                    $this->json($this->Commande->findByUtilisateur((int) $_SESSION['utilisateur_id']));
                    return;

                case 'POST':
                    if (!isset($_SESSION['utilisateur_id'])) {
                        $this->json(['error' => 'Connexion requise'], 401);
                        return;
                    }
                    $this->loadModel('Panier');
                    $id_utilisateur = (int) $_SESSION['utilisateur_id'];
                    $id_panier = $this->Panier->getOrCreate($id_utilisateur);
                    $id_commande = $this->Commande->createFromCart($id_utilisateur, $id_panier);
                    $this->json(['id_commande' => $id_commande], 201);
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
