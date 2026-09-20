<?php
$categorie = $categorie ?? [];
?>

<div class="page-header">

    <div>
        <h1><?= htmlspecialchars($categorie['nom']) ?></h1>
        <p>Détails de la catégorie</p>
    </div>

    <a
        href="/epise/api/backoffice/categorieModifier/<?= (int)$categorie['id_categorie'] ?>"
        class="button primary">
        <i class="fa-solid fa-pen"></i>
        Modifier
    </a>

</div>

<div class="card detail-card-simple">

    <div class="detail-image">

        <?php
        $imageCategorie = !empty($categorie['image'])
            ? $categorie['image']
            : 'default.png';
        ?>

        <img
            src="/epise/frontendepise/public/asset/img/categories/<?= htmlspecialchars($imageCategorie) ?>"
            alt="<?= htmlspecialchars($categorie['nom']) ?>">

    </div>

    <div>

        <h2>
            <?= htmlspecialchars($categorie['nom']) ?>
        </h2>

        <p>
            Identifiant :
            <?= (int)$categorie['id_categorie'] ?>
        </p>

        <p>
            <strong>
                <?= (int)$categorie['nombre_produits'] ?>
            </strong>
            produit(s) dans cette catégorie.
        </p>

    </div>

</div>

<div class="detail-actions">

    <a
        href="/epise/api/backoffice/categories"
        class="button secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Retour aux catégories
    </a>

    <a
        href="/epise/api/backoffice/categorieModifier/<?= (int)$categorie['id_categorie'] ?>"
        class="button primary">
        <i class="fa-solid fa-pen"></i>
        Modifier
    </a>

</div>