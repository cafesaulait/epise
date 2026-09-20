<?php

namespace controllers;

class Horaires extends \app\Controller
{
    public function index(): void
    {
        $this->loadModel('Horaire');

        try {

            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                $this->json(
                    ['error' => 'Méthode non autorisée'],
                    405
                );
                return;
            }

            $horaires = $this->Horaire->getAllHoraires();

            $jours = [
                1 => 'Lundi',
                2 => 'Mardi',
                3 => 'Mercredi',
                4 => 'Jeudi',
                5 => 'Vendredi',
                6 => 'Samedi',
                7 => 'Dimanche'
            ];

            foreach ($horaires as &$horaire) {
                $horaire['nom_jour'] =
                    $jours[(int) $horaire['jour']];
            }

            $this->json($horaires);
        } catch (\Throwable $e) {

            $this->json(
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
