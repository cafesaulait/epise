<?php

namespace controllers;

class Main extends \app\Controller
{
    public function index(): void
    {
        header('Location: /epise/frontendepise/');
        exit;
    }
}
