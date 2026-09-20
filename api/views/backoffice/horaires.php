<div class="page-header">

    <div>

        <h1>
            Horaires de l'EPISE
        </h1>

        <p>
            Modifiez ici les horaires d'ouverture de l'EPISE.
            Ces horaires seront automatiquement affichés sur le site.
        </p>

    </div>

</div>


<?php if (!empty($message)): ?>

    <div class="message">
        <?= htmlspecialchars($message) ?>
    </div>

<?php endif; ?>


<?php if (!empty($erreur)): ?>

    <div class="message-erreur">
        <?= htmlspecialchars($erreur) ?>
    </div>

<?php endif; ?>


<form
    method="post"
    class="form-card horaires-form">

    <?php foreach ($horaires as $horaire): ?>

        <?php
        $jour = (int) $horaire['jour'];
        ?>

        <div class="horaire-admin">

            <div class="horaire-admin-jour">

                <strong>
                    <?= htmlspecialchars($jours[$jour]) ?>
                </strong>

            </div>


            <div class="horaire-admin-ouvert">

                <label>
                    <input
                        type="radio"
                        name="ouvert[<?= $jour ?>]"
                        value="1"
                        <?= $horaire['ouvert'] ? 'checked' : '' ?>>

                    Ouvert
                </label>


                <label>
                    <input
                        type="radio"
                        name="ouvert[<?= $jour ?>]"
                        value="0"
                        <?= !$horaire['ouvert'] ? 'checked' : '' ?>>

                    Fermé
                </label>

            </div>


            <div class="horaire-admin-creneaux">

                <div>

                    <label>
                        Ouverture 1
                    </label>

                    <input
                        type="time"
                        name="ouverture_1[<?= $jour ?>]"
                        value="<?= htmlspecialchars(
                                    $horaire['ouverture_1']
                                        ? substr($horaire['ouverture_1'], 0, 5)
                                        : ''
                                ) ?>">

                    <span>à</span>

                    <label>
                        Fermeture 1
                    </label>

                    <input
                        type="time"
                        name="fermeture_1[<?= $jour ?>]"
                        value="<?= htmlspecialchars(
                                    $horaire['fermeture_1']
                                        ? substr($horaire['fermeture_1'], 0, 5)
                                        : ''
                                ) ?>">

                </div>


                <div>

                    <label>
                        Ouverture 2
                    </label>

                    <input
                        type="time"
                        name="ouverture_2[<?= $jour ?>]"
                        value="<?= htmlspecialchars(
                                    $horaire['ouverture_2']
                                        ? substr($horaire['ouverture_2'], 0, 5)
                                        : ''
                                ) ?>">

                    <span>à</span>

                    <label>
                        Fermeture 2
                    </label>

                    <input
                        type="time"
                        name="fermeture_2[<?= $jour ?>]"
                        value="<?= htmlspecialchars(
                                    $horaire['fermeture_2']
                                        ? substr($horaire['fermeture_2'], 0, 5)
                                        : ''
                                ) ?>">

                </div>

            </div>

        </div>

    <?php endforeach; ?>


    <div class="form-actions">

        <button
            type="submit"
            class="button primary">

            <i class="fa-solid fa-floppy-disk"></i>

            Enregistrer les horaires

        </button>

    </div>

</form>