<?php

namespace controllers;

class Connexion extends \app\Controller
{
    public function index(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->json(['utilisateur' => $_SESSION['utilisateur'] ?? null]);
            return;
        }

        if ($method !== 'POST') {
            $this->json(['error' => 'Méthode non autorisée'], 405);
            return;
        }

        $d = $this->jsonInput();
        if (empty($d['email']) || empty($d['mdp'])) {
            $this->json(['error' => 'email et mdp requis'], 400);
            return;
        }

        $this->loadModel('Utilisateur');
        $utilisateur = $this->Utilisateur->authenticate(trim($d['email']), $d['mdp']);

        if (!$utilisateur) {
            $this->json(['error' => 'Identifiants incorrects'], 401);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['utilisateur_id'] = $utilisateur['id_utilisateur'];
        $_SESSION['utilisateur'] = $utilisateur;

        $this->json(['utilisateur' => $utilisateur]);
    }

    public function deconnexion(): void
    {
        unset($_SESSION['utilisateur_id'], $_SESSION['utilisateur']);
        $this->json(['message' => 'Déconnecté']);
    }
}
