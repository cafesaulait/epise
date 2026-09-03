<?php

namespace controllers;

class Categories extends \app\Controller
{
    public function index(...$params): void
    {
        $this->loadModel('Categorie');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(['error' => 'Méthode non autorisée'], 405);
            return;
        }

        $id = isset($params[0]) ? (int) $params[0] : 0;
        $data = $id ? $this->Categorie->findById($id) : $this->Categorie->getAll();

        if ($id && !$data) {
            $this->json(['error' => 'Catégorie introuvable'], 404);
            return;
        }

        $this->json($data);
    }
}
