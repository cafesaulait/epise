<?php

namespace controllers;

class Utilisateur extends \app\Controller
{
    public function login(): void
    {
        $msg = null;
        if (isset($_POST['valide'])) {
            $this->loadModel('Utilisateur');
            $u = $this->Utilisateur->authenticate(trim($_POST['email'] ?? ''), $_POST['pass'] ?? '');
            if ($u) {
                session_regenerate_id(true);
                $_SESSION['utilisateur_id'] = $u['id_utilisateur'];
                $_SESSION['utilisateur'] = $u;
                header('Location: /epise/frontend/');
                exit;
            }
            $msg = 'Identifiants incorrects ou email non vérifié.';
        }
        $this->render('login', compact('msg'));
    }
    public function creationcompte(): void
    {
        $msg = null;
        if (isset($_POST['valide'])) {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['pass'] ?? '';
            $confirm = $_POST['pass_confirm'] ?? '';
            $role = $_POST['role'] ?? 'beneficiaire';
            if (!$nom || !$prenom || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8 || $pass !== $confirm) {
                $msg = 'Veuillez vérifier les informations saisies.';
            } elseif (!isset($_POST['email_verifie'])) {
                $msg = 'Vous devez valider votre email pour créer votre compte.';
            } else {
                try {
                    $this->loadModel('Utilisateur');
                    $id = $this->Utilisateur->create($nom, $prenom, $email, $pass, $role);
                    $msg = 'Compte créé avec succès.';
                } catch (\Throwable $e) {
                    $msg = 'Cette adresse email est peut-être déjà utilisée.';
                }
            }
        }
        $this->render('creationcompte', compact('msg'));
    }
    public function logout(): void
    {
        unset($_SESSION['utilisateur_id'], $_SESSION['utilisateur']);
        header('Location: /epise/frontend/');
        exit;
    }
}
?>