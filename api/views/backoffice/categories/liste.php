<div class="page-header">

    <div>
        <h1>Catégories</h1>
        <p>Gérez les catégories de produits.</p>
    </div>

    <a
        href="/backoffice/categorieAjouter"
        class="button primary">
        <i class="fa-solid fa-plus"></i>
        Ajouter une catégorie
    </a>

</div>

<?php if (empty($categories)): ?>

    <div class="card empty-state">

        <i class="fa-solid fa-folder-open"></i>

        <h2>Aucune catégorie</h2>

        <p>
            Il n'y a actuellement aucune catégorie.
        </p>

    </div>

<?php else: ?>

    <div class="categories-grid">

        <?php foreach ($categories as $categorie): ?>

            <article class="category-card">

                <div class="category-image">

                    <?php
                    $imageCategorie = !empty($categorie['image'])
                        ? $categorie['image']
                        : 'default.png';
                    ?>

                    <img
                        src="/assets/img/categories/<?= htmlspecialchars($imageCategorie) ?>"
                        alt="<?= htmlspecialchars($categorie['nom']) ?>">

                </div>

                <div>

                    <h2>
                        <?= htmlspecialchars($categorie['nom']) ?>
                    </h2>

                    <p class="category-description">
                        <?= htmlspecialchars($categorie['description'] ?? '') ?>
                    </p>

                    <p>
                        Catégorie #<?= (int)$categorie['id_categorie'] ?>
                    </p>

                </div>

                <div class="actions">

                    <a
                        href="/backoffice/categorieVoir/<?= (int)$categorie['id_categorie'] ?>"
                        class="button secondary">
                        <i class="fa-solid fa-eye"></i>
                        Voir
                    </a>

                    <a
                        href="/backoffice/categorieModifier/<?= (int)$categorie['id_categorie'] ?>"
                        class="button primary">
                        <i class="fa-solid fa-pen"></i>
                        Modifier
                    </a>

                </div>

            </article>

        <?php endforeach; ?>

    </div>

<?php endif; ?>