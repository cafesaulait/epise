<?php
$categorie = $categorie ?? [];
$idCategorie = !empty($categorie['id_categorie']) ? (int)$categorie['id_categorie'] : 0;
$nomCategorie = !empty($categorie['nom']) ? $categorie['nom'] : '';
$imageCategorie = !empty($categorie['image']) ? $categorie['image'] : 'default.png';
?>

<div class="page-header">

    <div>
        <h1>Modifier la catégorie</h1>
        <p><?= htmlspecialchars($nomCategorie) ?></p>
    </div>

</div>

<?php if (!empty($erreur)): ?>

    <div class="message-erreur">
        <?= htmlspecialchars($erreur) ?>
    </div>

<?php endif; ?>

<div class="card form-card">

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">

            <label for="nom">Nom de la catégorie</label>

            <input
                type="text"
                id="nom"
                name="nom"
                value="<?= htmlspecialchars($nomCategorie) ?>"
                required>

        </div>

        <div class="form-group">

            <label for="description">Description</label>

            <textarea
                id="description"
                name="description"
                rows="4"><?= htmlspecialchars($categorie['description'] ?? '') ?></textarea>

        </div>

        <div class="form-group">

            <label>Image de la catégorie</label>

            <div
                class="upload-zone"
                data-upload-zone>

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept="image/png,image/jpeg,image/webp,image/gif"
                    hidden>

                <div class="upload-zone-content">

                    <span class="upload-icon">📁</span>

                    <p>
                        <strong>
                            Glissez-déposez une nouvelle image
                        </strong>
                    </p>

                    <p>
                        ou cliquez pour choisir une image
                    </p>

                    <small>
                        Laisser vide pour conserver l'image actuelle.
                    </small>

                </div>

                <?php
                $imageCategorie = !empty($categorie['image'])
                    ? $categorie['image']
                    : 'default.png';
                ?>

                <img
                    src="/asset/img/categories/<?= htmlspecialchars($imageCategorie) ?>"
                    class="upload-preview"
                    data-upload-preview
                    alt="Image actuelle de la catégorie">

            </div>

        </div>

        <div class="form-actions">

            <a
                href="/epise/api/backoffice/categories/"
                class="button secondary">
                Annuler
            </a>

            <button
                type="submit"
                name="modifier"
                class="button primary">
                <i class="fa-solid fa-floppy-disk"></i>
                Enregistrer
            </button>

        </div>

    </form>

</div>

<div class="danger-zone">

    <h2>Zone dangereuse</h2>

    <p>
        La suppression d'une catégorie est définitive.
    </p>

    <a
        href="/epise/api/backoffice/categorieSupprimer/<?= (int)$categorie['id_categorie'] ?>"
        class="button danger"
        onclick="return confirm('Voulez-vous vraiment supprimer cette catégorie ?');">
        <i class="fa-solid fa-trash"></i>
        Supprimer la catégorie
    </a>

</div>