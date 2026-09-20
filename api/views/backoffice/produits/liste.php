<div class="page-header">
    <div>
        <h1>Produits</h1>
        <p>Gérez les produits disponibles à l'EPISE.</p>
    </div>

    <a
        href="/backoffice/produitAjouter"
        class="button primary">
        <i class="fa-solid fa-plus"></i>
        Ajouter un produit
    </a>
</div>

<?php if (empty($produits)): ?>

    <div class="card empty-state">
        <i class="fa-solid fa-box-open"></i>
        <h2>Aucun produit</h2>
        <p>Il n'y a actuellement aucun produit enregistré.</p>
    </div>

<?php else: ?>

    <div class="products-grid">

        <?php foreach ($produits as $produit): ?>

            <article class="product-card">

                <div class="product-image">

                    <?php
                    $imageProduit = !empty($produit['image'])
                        ? $produit['image']
                        : 'default.png';
                    ?>

                    <img
                        src="/frontendepise/public/asset/img/produits/<?= htmlspecialchars($imageProduit) ?>"
                        alt="<?= htmlspecialchars($produit['nom']) ?>">

                </div>

                <div class="product-content">

                    <h2>
                        <?= htmlspecialchars($produit['nom']) ?>
                    </h2>

                    <p class="product-description">
                        <?= htmlspecialchars($produit['description'] ?? '') ?>
                    </p>

                    <div class="product-stock">

                        <span>Stock</span>

                        <strong
                            class="<?= ((int)$produit['stock'] <= 10) ? 'stock-low' : '' ?>">
                            <?= (int)$produit['stock'] ?>
                        </strong>

                    </div>

                    <div class="actions">

                        <a
                            href="/backoffice/produitVoir/<?= (int)$produit['id_produit'] ?>"
                            class="button secondary">
                            <i class="fa-solid fa-eye"></i>
                            Voir
                        </a>

                        <a
                            href="/backoffice/produitModifier/<?= (int)$produit['id_produit'] ?>"
                            class="button primary">
                            <i class="fa-solid fa-pen"></i>
                            Modifier
                        </a>

                    </div>

                </div>

            </article>

        <?php endforeach; ?>

    </div>

<?php endif; ?>