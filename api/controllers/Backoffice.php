<?php

namespace controllers;

class Backoffice extends \app\Controller
{
    private function guard(): void
    {
        if (empty($_SESSION['admin_id'])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') return;
            header('Location: /epise/api/backoffice/login');
            exit;
        }
    }
    public function index(): void
    {
        if (empty($_SESSION['admin_id'])) {
            $this->login();
            return;
        }
        $this->loadModel('Utilisateur');
        $this->loadModel('Produit');
        $this->loadModel('Commande');
        $this->loadModel('Don');
        $data = ['nbEtudiants' => $this->Utilisateur->countByRole('beneficiaire'), 'nbDonateurs' => $this->Utilisateur->countByRole('donateur'), 'nbProduits' => $this->Produit->countProducts(), 'nbStockFaible' => $this->Produit->countStocksFaibles(), 'nbCommandesDay' => $this->Commande->countCommandesDay(), 'nbCommandesWeek' => $this->Commande->countCommandesWeek(), 'stockFaibles' => $this->Produit->stockFaiblesDashboard(), 'commandesRecentes' => $this->Commande->recent()];
        $this->render('index', $data, 'dashboard');
    }
    public function login(): void
    {
        $msg = null;
        if (isset($_POST['valide'])) {
            $email = trim($_POST['log'] ?? '');
            $pass = $_POST['pass'] ?? '';
            $this->loadModel('Administrateur');
            $a = $this->Administrateur->connexion($email, $pass);
            if ($a) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $a['id_administrateur'];
                $_SESSION['prenom'] = $a['prenom'];
                $_SESSION['nom'] = $a['nom'];
                header('Location: /epise/api/backoffice');
                exit;
            }
            $msg = 'Erreur de connexion.';
        }
        $this->render('connexion', compact('msg'), 'admin');
    }
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        header('Location: /epise/api/backoffice/login');
        exit;
    }
    public function produits(): void
    {
        $this->guard();
        $this->loadModel('Produit');
        $this->loadModel('Categorie');
        $categories = $this->Categorie->getAll();
        $produits = $this->Produit->getAll();
        $this->render('produits', compact('categories', 'produits'), 'dashboard');
    }
    public function commandes(): void
    {
        $this->guard();
        $this->loadModel('Commande');
        $commandes = $this->Commande->recent(50);
        $this->render('commandes', compact('commandes'), 'dashboard');
    }
    public function dons(): void
    {
        $this->guard();
        $this->loadModel('Don');
        $dons = $this->Don->pending();
        $this->render('dons', compact('dons'), 'dashboard');
    }
}
