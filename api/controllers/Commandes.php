<?php

namespace controllers;

class Commandes extends \app\Controller
{
    public function index(...$params): void
    {
        if (
            !isset($_SESSION['utilisateur_id'])
            && !$this->isAdmin()
        ) {
            $this->json(
                ['error' => 'Connexion requise'],
                401
            );

            return;
        }

        $this->loadModel('Commande');

        $method = $_SERVER['REQUEST_METHOD'];

        $id = isset($params[0])
            ? (int) $params[0]
            : 0;

        try {

            switch ($method) {

                //GET
                case 'GET':

                    if ($this->isAdmin()) {

                        $this->json(
                            $this->Commande->getAll()
                        );

                        return;
                    }

                    $this->json(
                        $this->Commande->findByUtilisateur(
                            (int) $_SESSION['utilisateur_id']
                        )
                    );

                    return;


                    //vider le panier
                case 'POST':

                    if (!isset($_SESSION['utilisateur_id'])) {

                        $this->json(
                            ['error' => 'Connexion requise'],
                            401
                        );

                        return;
                    }

                    // SEUL UN BENEFICIAIRE PEUT COMMANDER
                    $role = $_SESSION['utilisateur']['role'] ?? '';

                    if ($role !== 'beneficiaire') {

                        $this->json([
                            'error' =>
                            'Seuls les bénéficiaires peuvent commander. Si vous vous êtes trompé, vous pouvez modifier votre rôle sur votre compte.'
                        ], 403);

                        return;
                    }

                    $this->loadModel('Panier');

                    $id_utilisateur =
                        (int) $_SESSION['utilisateur_id'];

                    $id_panier =
                        $this->Panier->getOrCreate(
                            $id_utilisateur
                        );

                    $id_commande =
                        $this->Commande->createFromCart(
                            $id_utilisateur,
                            $id_panier
                        );

                    $this->json([
                        'message' =>
                        'Votre commande a bien été validée.',
                        'id_commande' => $id_commande
                    ], 201);

                    return;


                    //annuler une commande
                case 'DELETE':

                    if (!$id) {

                        $this->json([
                            'error' =>
                            'Identifiant de commande manquant.'
                        ], 400);

                        return;
                    }

                    if (!isset($_SESSION['utilisateur_id'])) {

                        $this->json(
                            ['error' => 'Connexion requise'],
                            401
                        );

                        return;
                    }

                    $this->Commande->annuler(
                        $id,
                        (int) $_SESSION['utilisateur_id']
                    );

                    $this->json([
                        'message' =>
                        'Votre commande a été annulée.'
                    ]);

                    return;


                default:

                    $this->json([
                        'error' =>
                        'Méthode non autorisée'
                    ], 405);

                    return;
            }
        } catch (\InvalidArgumentException $e) {

            $this->json([
                'error' => $e->getMessage()
            ], 400);
        } catch (\Throwable $e) {

            $this->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
