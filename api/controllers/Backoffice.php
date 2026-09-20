<?php

namespace controllers;

class Backoffice extends \app\Controller
{
    private function guard(): void
    {
        if (empty($_SESSION['admin_id'])) {
            header('Location: /backoffice/login');
            exit;
        }
    }

    private function uploadImage(string $type, ?array $file): ?string
    {
        if (!$file || empty($file['name'])) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(
                'Une erreur est survenue lors de l\'upload de l\'image.'
            );
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \RuntimeException(
                'L\'image ne doit pas dépasser 5 Mo.'
            );
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $typesAutorises = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif'
        ];

        if (!isset($typesAutorises[$mime])) {
            throw new \RuntimeException(
                'Format d\'image non autorisé. Utilisez JPG, PNG, WEBP ou GIF.'
            );
        }

        if (!in_array($type, ['produits', 'categories'], true)) {
            throw new \RuntimeException('Type d\'image invalide.');
        }

        $dossier = ROOT
            . 'assets'
            . DIRECTORY_SEPARATOR
            . 'img'
            . DIRECTORY_SEPARATOR
            . $type
            . DIRECTORY_SEPARATOR;

        if (!is_dir($dossier)) {
            if (!mkdir($dossier, 0755, true)) {
                throw new \RuntimeException(
                    'Impossible de créer le dossier d\'images.'
                );
            }
        }

        $nomFichier = bin2hex(random_bytes(16))
            . '.'
            . $typesAutorises[$mime];

        $destination = $dossier . $nomFichier;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException(
                'Impossible de déplacer l\'image.'
            );
        }

        return $nomFichier;
    }

    private function supprimerImage(string $type, ?string $nomFichier): void
    {
        if (
            empty($nomFichier)
            || $nomFichier === 'default.png'
        ) {
            return;
        }

        $nomFichier = basename($nomFichier);

        $chemin = ROOT
            . 'assets'
            . DIRECTORY_SEPARATOR
            . 'img'
            . DIRECTORY_SEPARATOR
            . $type
            . DIRECTORY_SEPARATOR
            . $nomFichier;

        if (is_file($chemin)) {
            unlink($chemin);
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

        $data = [
            'nbEtudiants' => $this->Utilisateur
                ->countByRole('beneficiaire'),

            'nbDonateurs' => $this->Utilisateur
                ->countByRole('donateur'),

            'nbProduits' => $this->Produit
                ->countProducts(),

            'nbStockFaible' => $this->Produit
                ->countStocksFaibles(),

            'nbCommandesDay' => $this->Commande
                ->countCommandesDay(),

            'nbCommandesWeek' => $this->Commande
                ->countCommandesWeek(),

            'stockFaibles' => $this->Produit
                ->stockFaiblesDashboard(),

            'commandesRecentes' => $this->Commande
                ->recent()
        ];

        $this->render(
            'index',
            $data,
            'dashboard'
        );
    }

    public function login(): void
    {
        $msg = null;

        if (isset($_POST['valide'])) {

            $email = trim($_POST['log'] ?? '');
            $pass = $_POST['pass'] ?? '';

            $this->loadModel('Administrateur');

            $a = $this->Administrateur
                ->connexion($email, $pass);

            if ($a) {

                session_regenerate_id(true);

                $_SESSION['admin_id'] = $a['id_administrateur'];
                $_SESSION['prenom'] = $a['prenom'];
                $_SESSION['nom'] = $a['nom'];

                header('Location: /backoffice');
                exit;
            }

            $msg = 'Erreur de connexion.';
        }

        $this->render(
            'connexion',
            compact('msg'),
            'admin'
        );
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();

        header('Location: /backoffice/login');
        exit;
    }


    public function produits(): void
    {
        $this->guard();

        $this->loadModel('Produit');
        $this->loadModel('Categorie');

        $categories = $this->Categorie->getAll();
        $produits = $this->Produit->getAll();

        $this->render(
            'produits/liste',
            compact('categories', 'produits'),
            'dashboard'
        );
    }

    public function produitAjouter(): void
    {
        $this->guard();

        $this->loadModel('Produit');
        $this->loadModel('Categorie');

        $erreur = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            try {

                $image = $this->uploadImage(
                    'produits',
                    $_FILES['image'] ?? null
                );

                $this->Produit->create([
                    'nom' => trim($_POST['nom'] ?? ''),
                    'stock' => (int) ($_POST['stock'] ?? 0),
                    'id_categorie' => (int) ($_POST['id_categorie'] ?? 0),
                    'description' => trim($_POST['description'] ?? ''),
                    'image' => $image
                ]);

                header('Location: /backoffice/produits');
                exit;
            } catch (\Throwable $e) {
                $erreur = $e->getMessage();
            }
        }

        $categories = $this->Categorie->getAll();

        $this->render(
            'produits/ajouter',
            compact('categories', 'erreur'),
            'dashboard'
        );
    }

    public function produitVoir(int $id): void
    {
        $this->guard();

        $this->loadModel('Produit');

        $produit = $this->Produit->findByIdAvecCategorie($id);

        if (!$produit) {
            http_response_code(404);
            echo 'Produit introuvable';
            return;
        }

        $this->render(
            'produits/voir',
            compact('produit'),
            'dashboard'
        );
    }

    public function produitModifier(int $id): void
    {
        $this->guard();

        $this->loadModel('Produit');
        $this->loadModel('Categorie');

        $produit = $this->Produit->findById($id);

        if (!$produit) {
            http_response_code(404);
            echo 'Produit introuvable';
            return;
        }

        $erreur = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            try {

                $ancienneImage = $produit['image'] ?? null;

                $image = $this->uploadImage(
                    'produits',
                    $_FILES['image'] ?? null
                );

                $donnees = [
                    'nom' => trim($_POST['nom'] ?? ''),
                    'stock' => (int) ($_POST['stock'] ?? 0),
                    'id_categorie' => (int) ($_POST['id_categorie'] ?? 0),
                    'description' => trim($_POST['description'] ?? '')
                ];

                if ($image !== null) {
                    $donnees['image'] = $image;
                }

                $this->Produit->update($id, $donnees);

                if ($image !== null) {
                    $this->supprimerImage(
                        'produits',
                        $ancienneImage
                    );
                }

                header('Location: /backoffice/produits');
                exit;
            } catch (\Throwable $e) {
                $erreur = $e->getMessage();
            }
        }

        $categories = $this->Categorie->getAll();

        $this->render(
            'produits/modifier',
            compact('produit', 'categories', 'erreur'),
            'dashboard'
        );
    }

    public function produitSupprimer(int $id): void
    {
        $this->guard();

        $this->loadModel('Produit');

        $produit = $this->Produit->findById($id);

        if (!$produit) {
            header('Location: /backoffice/produits');
            exit;
        }

        $image = $produit['image'] ?? null;

        $this->Produit->delete($id);

        $this->supprimerImage(
            'produits',
            $image
        );

        header('Location: /backoffice/produits');
        exit;
    }


    public function categories(): void
    {
        $this->guard();

        $this->loadModel('Categorie');

        $categories = $this->Categorie->getAll();

        $this->render(
            'categories/liste',
            compact('categories'),
            'dashboard'
        );
    }

    public function categorieAjouter(): void
    {
        $this->guard();

        $this->loadModel('Categorie');

        $erreur = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            try {

                $image = $this->uploadImage(
                    'categories',
                    $_FILES['image'] ?? null
                );

                $this->Categorie->create([
                    'nom' => trim($_POST['nom'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'image' => $image
                ]);

                header('Location: /backoffice/categories');
                exit;
            } catch (\Throwable $e) {
                $erreur = $e->getMessage();
            }
        }

        $this->render(
            'categories/ajouter',
            compact('erreur'),
            'dashboard'
        );
    }

    public function categorieVoir(int $id): void
    {
        $this->guard();

        $this->loadModel('Categorie');
        $this->loadModel('Produit');

        $categorie = $this->Categorie->findById($id);

        if (!$categorie) {
            http_response_code(404);
            echo 'Catégorie introuvable';
            return;
        }

        $categorie['nombre_produits'] = $this->Produit->countByCategorie($id);

        $this->render(
            'categories/voir',
            compact('categorie'),
            'dashboard'
        );
    }

    public function categorieModifier(int $id): void
    {
        $this->guard();

        $this->loadModel('Categorie');

        $categorie = $this->Categorie->findById($id);

        if (!$categorie) {
            http_response_code(404);
            echo 'Catégorie introuvable';
            return;
        }

        $erreur = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            try {

                $ancienneImage = $categorie['image'] ?? null;

                $image = $this->uploadImage(
                    'categories',
                    $_FILES['image'] ?? null
                );

                $donnees = [
                    'nom' => trim($_POST['nom'] ?? ''),
                    'description' => trim($_POST['description'] ?? '')
                ];

                if ($image !== null) {
                    $donnees['image'] = $image;
                }

                $this->Categorie->update($id, $donnees);

                if ($image !== null) {
                    $this->supprimerImage(
                        'categories',
                        $ancienneImage
                    );
                }

                header('Location: /backoffice/categories');
                exit;
            } catch (\Throwable $e) {
                $erreur = $e->getMessage();
            }
        }

        $this->render(
            'categories/modifier',
            compact('categorie', 'erreur'),
            'dashboard'
        );
    }

    public function categorieSupprimer(int $id): void
    {
        $this->guard();

        $this->loadModel('Categorie');

        $categorie = $this->Categorie->findById($id);

        if (!$categorie) {
            header('Location: /backoffice/categories');
            exit;
        }

        $image = $categorie['image'] ?? null;

        try {

            $this->Categorie->delete($id);

            $this->supprimerImage(
                'categories',
                $image
            );
        } catch (\Throwable $e) {

            echo 'Impossible de supprimer cette catégorie. '
                . 'Vérifiez qu\'elle ne contient aucun produit.';
            return;
        }

        header('Location: /backoffice/categories');
        exit;
    }

    public function horaires(): void
    {
        $this->guard();

        $this->loadModel('Horaire');

        $jours = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche'
        ];

        $message = null;
        $erreur = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            try {

                $horaires = [];

                for ($jour = 1; $jour <= 7; $jour++) {

                    $ouvert =
                        isset($_POST['ouvert'][$jour])
                        && $_POST['ouvert'][$jour] === '1';

                    $ouverture1 =
                        $_POST['ouverture_1'][$jour] ?? null;

                    $fermeture1 =
                        $_POST['fermeture_1'][$jour] ?? null;

                    $ouverture2 =
                        $_POST['ouverture_2'][$jour] ?? null;

                    $fermeture2 =
                        $_POST['fermeture_2'][$jour] ?? null;

                    if (!$ouvert) {

                        $ouverture1 = null;
                        $fermeture1 = null;
                        $ouverture2 = null;
                        $fermeture2 = null;
                    } else {

                        if (
                            empty($ouverture1)
                            || empty($fermeture1)
                        ) {
                            throw new \InvalidArgumentException(
                                'Chaque jour ouvert doit avoir un premier créneau complet.'
                            );
                        }

                        if (
                            ($ouverture2 && !$fermeture2)
                            || (!$ouverture2 && $fermeture2)
                        ) {
                            throw new \InvalidArgumentException(
                                'Le deuxième créneau doit avoir une heure d’ouverture et de fermeture.'
                            );
                        }
                    }

                    $horaires[] = [
                        'jour' => $jour,
                        'ouvert' => $ouvert,
                        'ouverture_1' => $ouverture1,
                        'fermeture_1' => $fermeture1,
                        'ouverture_2' => $ouverture2,
                        'fermeture_2' => $fermeture2
                    ];
                }

                $this->Horaire->mettreAJourTous($horaires);

                $message =
                    'Les horaires de l’EPISE ont bien été enregistrés.';
            } catch (\Throwable $e) {

                $erreur = $e->getMessage();
            }
        }

        $horaires = $this->Horaire->getAllHoraires();

        $this->render(
            'horaires',
            compact(
                'horaires',
                'jours',
                'message',
                'erreur'
            ),
            'dashboard'
        );
    }

    public function commandes(): void
    {
        $this->guard();

        $this->loadModel('Commande');

        $message = null;
        $erreur = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $idCommande = (int) ($_POST['id_commande'] ?? 0);

            if ($idCommande <= 0) {
                $erreur = 'Commande invalide.';
            } else {

                try {

                    // Marquer une commande comme récupérée
                    if (isset($_POST['recuperer'])) {

                        $this->Commande->marquerRecuperee(
                            $idCommande
                        );

                        $message =
                            'La commande a été marquée comme récupérée.';
                    }


                    // Annuler une commande en tant qu'administrateur
                    elseif (isset($_POST['annuler'])) {

                        $motif = trim(
                            $_POST['motif_annulation'] ?? ''
                        );

                        $this->Commande->annulerParAdmin(
                            $idCommande,
                            (int) $_SESSION['admin_id'],
                            $motif
                        );

                        $message =
                            'La commande a été annulée.';
                    }
                } catch (\Throwable $e) {

                    $erreur = $e->getMessage();
                }
            }
        }

        $commandes = $this->Commande->recent(50);

        $this->render(
            'commandes',
            compact(
                'commandes',
                'message',
                'erreur'
            ),
            'dashboard'
        );
    }

    public function categorieDepuisDon(int $id_don_produit): void
    {
        $this->guard();

        $this->loadModel('Don');

        try {

            $idCategorie =
                $this->Don->creerCategorieDepuisProposition(
                    $id_don_produit
                );

            header(
                'Location: /backoffice/dons'
            );

            exit;
        } catch (\Throwable $e) {

            http_response_code(400);

            echo '<h1>Impossible de créer la catégorie</h1>';

            echo '<p>'
                . htmlspecialchars($e->getMessage())
                . '</p>';

            echo '<p>';

            echo '<a href="/backoffice/dons">'
                . 'Retour aux dons'
                . '</a>';

            echo '</p>';
        }
    }

    public function dons(): void
    {
        $this->guard();

        $this->loadModel('Don');

        $message = null;

        try {

            if (
                $_SERVER['REQUEST_METHOD'] === 'POST'
                && isset($_POST['id_don'])
                && isset($_POST['valider'])
            ) {

                $idDon = (int) $_POST['id_don'];

                $this->Don->valider(
                    $idDon,
                    (int) $_SESSION['admin_id'],
                    'valide'
                );

                $message = 'Le don a été accepté.';
            }


            elseif (
                $_SERVER['REQUEST_METHOD'] === 'POST'
                && isset($_POST['id_don'])
                && isset($_POST['refuser'])
            ) {

                $idDon = (int) $_POST['id_don'];

                $motif = trim(
                    $_POST['motif_refus'] ?? ''
                );

                $this->Don->valider(
                    $idDon,
                    (int) $_SESSION['admin_id'],
                    'refuse',
                    $motif !== '' ? $motif : null
                );

                $message = 'Le don a été refusé.';
            }
        } catch (\Throwable $e) {

            $message = $e->getMessage();
        }


        $dons = $this->Don->pending();


        $this->render(
            'dons',
            compact('dons', 'message'),
            'dashboard'
        );
    }
}
