<h1>Commandes</h1>

<?php if (!empty($message)): ?>

    <div class="message">
        <?= htmlspecialchars($message) ?>
    </div>

<?php endif; ?>


<?php if (empty($commandes)): ?>

    <div class="card">

        <p>
            Aucune commande.
        </p>

    </div>

<?php else: ?>

    <?php foreach ($commandes as $commande): ?>

        <div class="card">

            <h2>
                Commande
                #<?= (int)
                    $commande['id_commande'] ?>
            </h2>

            <p>

                <strong>
                    Bénéficiaire :
                </strong>

                <?= htmlspecialchars(
                    $commande['prenom']
                        . ' '
                        . $commande['nom']
                ) ?>

            </p>

            <p>

                <strong>
                    E-mail :
                </strong>

                <?= htmlspecialchars(
                    $commande['email']
                ) ?>

            </p>

            <p>

                <strong>
                    Date :
                </strong>

                <?= htmlspecialchars(
                    $commande['date_commande']
                ) ?>

            </p>

            <p>

                <strong>
                    Mode :
                </strong>

                <?= $commande['mode']
                    === 'en_ligne'
                    ? 'Réservation en ligne'
                    : 'Passage en magasin'
                ?>

            </p>

            <p>

                <strong>
                    Statut :
                </strong>

                <?= htmlspecialchars(
                    $commande['statut']
                ) ?>

            </p>


            <h3>
                Produits
            </h3>

            <ul>

                <?php foreach (
                    $commande['produits']
                    as $produit
                ): ?>

                    <li>

                        <?= htmlspecialchars(
                            $produit['nom']
                        ) ?>

                        ×

                        <?= (int)
                        $produit['quantite'] ?>

                    </li>

                <?php endforeach; ?>

            </ul>

            <?php if (
    $commande['statut']
    === 'en_attente'
): ?>

    <form method="post">

        <input
            type="hidden"
            name="id_commande"
            value="<?= (int)
                    $commande['id_commande'] ?>">

        <button
            type="submit"
            name="recuperer"
            class="button primary"
            onclick="
                return confirm(
                    'Confirmer que cette commande a été récupérée ?'
                );
            "
        >
            <i class="fa-solid fa-check"></i>
            Marquer comme récupérée
        </button>

    </form>


    <form method="post">

        <input
            type="hidden"
            name="id_commande"
            value="<?= (int)
                    $commande['id_commande'] ?>">

        <div class="champ-annulation">

            <label for="motif-annulation-<?= (int) $commande['id_commande'] ?>">
                Motif de l'annulation (facultatif)
            </label>

            <textarea
                id="motif-annulation-<?= (int) $commande['id_commande'] ?>"
                name="motif_annulation"
                placeholder="Pourquoi cette commande est-elle annulée ?"
            ></textarea>

        </div>

        <button
            type="submit"
            name="annuler"
            class="button danger"
            onclick="
                return confirm(
                    'Voulez-vous vraiment annuler cette commande ?'
                );
            "
        >
            <i class="fa-solid fa-xmark"></i>
            Annuler la commande
        </button>

    </form>


    <?php elseif (
        $commande['statut']
        === 'recuperee'
    ): ?>

        <p class="statut-ok">
            <i class="fa-solid fa-circle-check"></i>
            Commande récupérée.
        </p>


    <?php elseif (
        $commande['statut']
        === 'annulee'
    ): ?>

        <p class="statut-annule">
            <i class="fa-solid fa-circle-xmark"></i>
            Commande annulée.
        </p>

            <?php elseif (
                $commande['statut']
                === 'recuperee'
            ): ?>

                <p>
                    Commande récupérée.
                </p>

            <?php elseif (
                $commande['statut']
                === 'annulee'
            ): ?>

                <p>
                    Commande annulée.
                </p>

            <?php endif; ?>

        </div>

    <?php endforeach; ?>

<?php endif; ?>