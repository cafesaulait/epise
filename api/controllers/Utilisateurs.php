<?php

namespace controllers;

class Utilisateurs extends \app\Controller
{
    public function index(...$params): void
    {
        $this->loadModel('Utilisateur');

        $method = $_SERVER['REQUEST_METHOD'];

        $id = isset($params[0])
            ? (int) $params[0]
            : 0;

        try {

            switch ($method) {

                case 'GET':

                    if (!$id) {

                        if (!$this->isAdmin()) {

                            $this->json([
                                'error' =>
                                'Accès réservé à l\'administrateur'
                            ], 403);

                            return;
                        }

                        $this->json(
                            $this->Utilisateur->getAll()
                        );

                        return;
                    }

                    if (
                        !$this->isAdmin()
                        &&
                        (int) ($_SESSION['utilisateur_id'] ?? 0)
                        !== $id
                    ) {

                        $this->json([
                            'error' =>
                            'Accès non autorisé'
                        ], 403);

                        return;
                    }

                    $data =
                        $this->Utilisateur->findById($id);

                    if (!$data) {

                        $this->json([
                            'error' =>
                            'Utilisateur introuvable'
                        ], 404);

                        return;
                    }

                    unset($data['mdp']);

                    $this->json($data);

                    return;


                case 'POST':

                    $d = $this->jsonInput();

                    foreach (
                        ['nom', 'prenom', 'email', 'mdp', 'role']
                        as $k
                    ) {

                        if (empty($d[$k])) {

                            throw new \InvalidArgumentException(
                                'Champ manquant : ' . $k
                            );
                        }
                    }

                    if (
                        !in_array(
                            $d['role'],
                            ['donateur', 'beneficiaire'],
                            true
                        )
                    ) {

                        throw new \InvalidArgumentException(
                            'Rôle invalide'
                        );
                    }


                    if (
                        $this->Utilisateur->emailExists(
                            trim($d['email'])
                        )
                    ) {

                        $this->json([
                            'error' =>
                            'Cette adresse e-mail est déjà utilisée.'
                        ], 409);

                        return;
                    }

                    $utilisateur =
                        $this->Utilisateur->create(
                            trim($d['nom']),
                            trim($d['prenom']),
                            trim($d['email']),
                            $d['mdp'],
                            $d['role']
                        );


                    session_regenerate_id(true);

                    $_SESSION['utilisateur_id'] =
                        $utilisateur['id_utilisateur'];

                    $_SESSION['utilisateur'] =
                        $utilisateur;

                    $this->json([
                        'utilisateur' => $utilisateur
                    ], 201);

                    return;


                case 'DELETE':

                    if (!$id) {

                        throw new \InvalidArgumentException(
                            'Identifiant utilisateur manquant.'
                        );
                    }

                    if (
                        (int) ($_SESSION['utilisateur_id'] ?? 0)
                        !== $id
                    ) {

                        $this->json([
                            'error' =>
                            'Vous ne pouvez supprimer que votre propre compte.'
                        ], 403);

                        return;
                    }

                    $this->Utilisateur->deleteAccount($id);

                    // Destruction complète de la session
                    $_SESSION = [];

                    if (ini_get("session.use_cookies")) {

                        $paramsCookie =
                            session_get_cookie_params();

                        setcookie(
                            session_name(),
                            '',
                            time() - 42000,
                            $paramsCookie["path"],
                            $paramsCookie["domain"],
                            $paramsCookie["secure"],
                            $paramsCookie["httponly"]
                        );
                    }

                    session_destroy();

                    $this->json([
                        'message' =>
                        'Votre compte a bien été supprimé.'
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
