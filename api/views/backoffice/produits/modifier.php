<?php $produit = $produit ?? []; ?>

<div class="page-header">

    <div>
        <h1>Modifier le produit</h1>
        <p><?= htmlspecialchars($produit['nom'] ?? '') ?></p>
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

            <label for="nom">
                Nom du produit
            </label>

            <input
                type="text"
                id="nom"
                name="nom"
                value="<?= htmlspecialchars($produit['nom']) ?>"
                required>

        </div>

        <div class="form-group">

            <label for="description">
                Description
            </label>

            <textarea
                id="description"
                name="description"
                rows="5"><?= htmlspecialchars($produit['description'] ?? '') ?></textarea>

        </div>

        <div class="form-grid">

            <div class="form-group">

                <label for="stock">
                    Stock
                </label>

                <input
                    type="number"
                    id="stock"
                    name="stock"
                    min="0"
                    value="<?= (int)$produit['stock'] ?>"
                    required>

            </div>

            <div class="form-group">

                <label for="id_categorie">
                    Catégorie
                </label>

                <select
                    id="id_categorie"
                    name="id_categorie"
                    required>

                    <?php if (!empty($categories) && is_array($categories)): ?>
                        <?php foreach ($categories as $categorie): ?>

                            <option
                                value="<?= (int)$categorie['id_categorie'] ?>"
                                <?= ((int)$produit['id_categorie'] === (int)$categorie['id_categorie']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categorie['nom']) ?>
                            </option>

                        <?php endforeach; ?>
                    <?php endif; ?>

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
                $imageProduit = !empty($produit['image'])
                    ? $produit['image']
                    : 'default.png';
                ?>

                <img
                    src="/asset/img/produits/<?= htmlspecialchars($imageProduit) ?>"
                    class="upload-preview"
                    data-upload-preview
                    alt="Image actuelle du produit">

            </div>

        </div>

        <div class="form-actions">

            <a
                href="/epise/api/backoffice/produits"
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
        La suppression d'un produit est définitive.
    </p>

    <a
        href="/epise/api/backoffice/produitSupprimer/<?= (int)$produit['id_produit'] ?>"
        class="button danger"
        onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?');">
        <i class="fa-solid fa-trash"></i>
        Supprimer le produit
    </a>

</div>