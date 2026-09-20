<?php
$produit = $produit ?? [];
$produitNom = $produit['nom'] ?? 'Produit';
$produitImage = !empty($produit['image']) ? $produit['image'] : 'default.png';
$produitId = (int)($produit['id_produit'] ?? 0);
$produitStock = (int)($produit['stock'] ?? 0);
?>

<div class="page-header">

    <div>
        <h1><?= htmlspecialchars($produitNom) ?></h1>
        <p>Détails du produit</p>
    </div>

    <a
        href="/epise/api/backoffice/produitModifier/<?= $produitId ?>"
        class="button primary">
        <i class="fa-solid fa-pen"></i>
        Modifier
    </a>

</div>

<div class="detail-card">

    <div class="detail-image">

        <img
            src="/epise/frontendepise/public/asset/img/produits/<?= htmlspecialchars($produitImage) ?>"
            alt="<?= htmlspecialchars($produitNom) ?>">

    </div>

    <div class="detail-content">

        <h2><?= htmlspecialchars($produitNom) ?></h2>

        <div class="detail-row">
            <strong>Description</strong>
            <p>
                <?= nl2br(htmlspecialchars($produit['description'] ?? 'Aucune description.')) ?>
            </p>
        </div>

        <div class="detail-row">
            <strong>Catégorie</strong>
            <p>
                <?= htmlspecialchars($produit['categorie_nom'] ?? 'Aucune catégorie') ?>
            </p>
        </div>

        <div class="detail-row">
            <strong>Stock</strong>

            <p class="<?= $produitStock <= 10 ? 'stock-low' : '' ?>">
                <?= $produitStock ?> unité(s)
            </p>
        </div>

        <div class="detail-row">
            <strong>Image</strong>
            <p>
                <?= htmlspecialchars($produit['image'] ?? 'Aucune image') ?>
            </p>
        </div>

    </div>

</div>

<div class="detail-actions">

    <a
        href="/epise/api/backoffice/produits"
        class="button secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Retour aux produits
    </a>

    <a
        href="/epise/api/backoffice/produitModifier/<?= $produitId ?>"
        class="button primary">
        <i class="fa-solid fa-pen"></i>
        Modifier
    </a>

</div>