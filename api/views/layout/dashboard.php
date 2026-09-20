<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Backoffice EPISE
    </title>

    <link
        rel="stylesheet"
        href="/assets/css/backoffice.css">

</head>

<body class="backoffice">

    <header class="backoffice-header">

        <div>

            <strong>
                EPISE — Administration
            </strong>

            <span>
                Bonjour
                <?= htmlspecialchars(
                    $_SESSION['prenom'] ?? ''
                ) ?>
            </span>

        </div>

        <nav class="backoffice-nav">

            <a href="/backoffice">
                <i class="fa-solid fa-chart-line"></i>
                Tableau de bord
            </a>

            <a href="/backoffice/produits">
                <i class="fa-solid fa-box"></i>
                Produits
            </a>

            <a href="/backoffice/categories">
                <i class="fa-solid fa-folder"></i>
                Catégories
            </a>

            <a href="/backoffice/dons">
                <i class="fa-solid fa-hand-holding-heart"></i>
                Dons
            </a>

            <a href="/backoffice/horaires">
                <i class="fa-solid fa-clock"></i>
                Horaires
            </a>

            <a href="/backoffice/commandes">
                <i class="fa-solid fa-cart-shopping"></i>
                Commandes
            </a>

            <a href="/backoffice/logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                Déconnexion
            </a>

        </nav>

    </header>

    <main class="backoffice-main">

        <?= $content ?? '' ?>

    </main>

    <script src="/assets/js/backoffice.js"></script>
</body>

</html>