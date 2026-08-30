<?php

namespace controllers;

class Don extends \app\Controller
{
    public function index(): void
    {
        $this->render('index');
    }
    public function creer(): void
    {
        if (empty($_SESSION['utilisateur_id'])) {
            header('Location: /epise/frontend/');
            exit;
        }
        $this->render('index');
    }
}
?>