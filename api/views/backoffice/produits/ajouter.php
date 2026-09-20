<div class="page-header">
    <div>
        <h1>Ajouter un produit</h1>
        <p>Ajoutez un nouveau produit au catalogue.</p>
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
            <label for="nom">Nom du produit</label>

            <input
                type="text"
                id="nom"
                name="nom"
                value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>

            <textarea
                id="description"
                name="description"
                rows="5"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-grid">

            <div class="form-group">

                <label for="stock">Stock</label>

                <input
                    type="number"
                    id="stock"
                    name="stock"
                    min="0"
                    value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>"
                    required>

            </div>

            <div class="form-group">

                <label for="id_categorie">Catégorie</label>

                <select
                    id="id_categorie"
                    name="id_categorie"
                    required>

                    <option value="">
                        Sélectionner une catégorie
                    </option>

                    <?php foreach (($categories ?? []) as $categorie): ?>

                        <option
                            value="<?= (int)$categorie['id_categorie'] ?>"
                            <?= ((int)($_POST['id_categorie'] ?? 0) === (int)$categorie['id_categorie']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categorie['nom']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>

        <div class="form-group">

            <label>Image du produit</label>

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
                        <strong>Glissez-déposez une image ici</strong>
                    </p>

                    <p>
                        ou cliquez pour choisir une image
                    </p>

                    <small>
                        PNG, JPG, WEBP ou GIF — 5 Mo maximum
                    </small>

                </div>

                <img
                    class="upload-preview"
                    data-upload-preview
                    alt="Aperçu de l'image"
                    hidden>

            </div>

        </div>

        <div class="form-actions">

            <a
                href="/backoffice/produits"
                class="button secondary">
                Annuler
            </a>

            <button
                type="submit"
                name="ajouter"
                class="button primary">
                <i class="fa-solid fa-plus"></i>
                Ajouter le produit
            </button>

        </div>

    </form>

</div>