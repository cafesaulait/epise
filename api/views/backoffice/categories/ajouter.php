<div class="page-header">

    <div>
        <h1>Ajouter une catégorie</h1>
        <p>Créez une nouvelle catégorie de produits.</p>
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
                required>

        </div>

        <div class="form-group">

            <label for="description">Description</label>

            <textarea
                id="description"
                name="description"
                rows="4"></textarea>

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
                            Glissez-déposez une image ici
                        </strong>
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
                    alt="Aperçu"
                    hidden>

            </div>

        </div>

        <div class="form-actions">

            <a
                href="/backoffice/categories"
                class="button secondary">
                Annuler
            </a>

            <button
                type="submit"
                name="ajouter"
                class="button primary">
                <i class="fa-solid fa-plus"></i>
                Ajouter la catégorie
            </button>

        </div>

    </form>

</div>