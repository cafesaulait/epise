<?php

namespace controllers;

class Panier extends \app\Controller
{
    public function index(): void
    {
        if (empty($_SESSION['utilisateur_id'])) {
            header('Location: /epise/frontend/');
            exit;
        }
        $this->loadModel('Panier');
        $id = $this->Panier->getOrCreate((int)$_SESSION['utilisateur_id']);
        $items = $this->Panier->items($id);
        $total = $this->Panier->totalUnits($id);
        $this->render('index', compact('items', 'total'));
    }
}
?>