<h1>Dons en attente</h1>


<?php if (!empty($message)): ?>

    <div class="message">
        <?= htmlspecialchars($message) ?>
    </div>

<?php endif; ?>


<?php if (empty($dons)): ?>

    <div class="card">

        <p>
            Aucun don n'est actuellement en attente.
        </p>

    </div>

<?php else: ?>


    <?php foreach ($dons as $don): ?>

        <div class="card">

            <h2>
                Don #<?= (int) $don['id_don'] ?>
            </h2>


            <p>

                <strong>Donateur :</strong>

                <?= htmlspecialchars(
                    $don['prenom']
                        . ' '
                        . $don['nom']
                ) ?>

            </p>


            <p>

                <strong>Date :</strong>

                <?= htmlspecialchars(
                    $don['date_don']
                ) ?>

            </p>

            <p>
                <strong>Date de passage :</strong>
                <?= htmlspecialchars($don['date_passage'] ?? '') ?>
            </p>

            <p>
                <strong>Heure de passage :</strong>
                <?= htmlspecialchars(substr($don['heure_passage'] ?? '', 0, 5)) ?>
            </p>


            <?php if (!empty($don['commentaire'])): ?>

                <p>

                    <strong>Commentaire :</strong>

                    <?= htmlspecialchars(
                        $don['commentaire']
                    ) ?>

                </p>

            <?php endif; ?>


            <h3>
                Produits proposés
            </h3>


            <ul>

                <?php foreach (
                    $don['produits']
                    as $produit
                ): ?>

                    <li>

                        <strong>
                            <?= htmlspecialchars(
                                $produit['nom_produit']
                            ) ?>
                        </strong>

                        ×
                        <?= (int) $produit['quantite'] ?>


                        <?php if (
                            !empty($produit['categorie_nom'])
                        ): ?>

                            <br>

                            <small>

                                <strong>
                                    Catégorie :
                                </strong>

                                <?= htmlspecialchars(
                                    $produit['categorie_nom']
                                ) ?>

                            </small>

                        <?php endif; ?>


                        <?php if (
                            !empty($produit['categorie_proposee'])
                        ): ?>

                            <br>

                            <span class="categorie-proposee">

                                <strong>
                                    Catégorie proposée :
                                </strong>

                                <?= htmlspecialchars(
                                    $produit['categorie_proposee']
                                ) ?>

                            </span>


                            <form
                                method="post"
                                action="/epise/api/backoffice/categorieDepuisDon/<?= (int) $produit['id_don_produit'] ?>"
                                style="margin-top: 8px;">

                                <button
                                    type="submit"
                                    class="button secondary">

                                    <i class="fa-solid fa-folder-plus"></i>

                                    Créer cette catégorie

                                </button>

                            </form>

                        <?php endif; ?>


                    </li>

                <?php endforeach; ?>

            </ul>


            <div class="actions">

                <!-- Accepter -->
                <form method="post">

                    <input
                        type="hidden"
                        name="id_don"
                        value="<?= (int) $don['id_don'] ?>">

                    <button
                        type="submit"
                        name="valider"
                        class="button primary">

                        <i class="fa-solid fa-check"></i>

                        Accepter le don

                    </button>

                </form>


                <!-- Refuser -->
                <form method="post">

                    <input
                        type="hidden"
                        name="id_don"
                        value="<?= (int) $don['id_don'] ?>">

                    <input
                        type="text"
                        name="motif_refus"
                        placeholder="Motif du refus">

                    <button
                        type="submit"
                        name="refuser"
                        class="button danger">

                        <i class="fa-solid fa-xmark"></i>

                        Refuser

                    </button>

                </form>

            </div>


        </div>

    <?php endforeach; ?>


<?php endif; ?>