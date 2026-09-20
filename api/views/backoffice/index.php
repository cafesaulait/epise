<?php
$nbEtudiants = $nbEtudiants ?? 0;
$nbDonateurs = $nbDonateurs ?? 0;
$nbProduits = $nbProduits ?? 0;
$nbStockFaible = $nbStockFaible ?? 0;
$nbCommandesDay = $nbCommandesDay ?? 0;
$nbCommandesWeek = $nbCommandesWeek ?? 0;
$stockFaibles = $stockFaibles ?? [];
$commandesRecentes = $commandesRecentes ?? [];
?>

<h1>Tableau de bord</h1>

<p>
    Bienvenue
    <strong>
        <?= htmlspecialchars(
            $_SESSION['prenom'] ?? ''
        ) ?>
    </strong>
    dans le backoffice EPISE.
</p>

<div class="stats">

    <div class="stat">
        <span>Étudiants bénéficiaires</span>
        <strong>
            <?= (int)$nbEtudiants ?>
        </strong>
    </div>

    <div class="stat">
        <span>Donateurs</span>
        <strong>
            <?= (int)$nbDonateurs ?>
        </strong>
    </div>

    <div class="stat">
        <span>Produits</span>
        <strong>
            <?= (int) $nbProduits ?>
        </strong>
    </div>

    <div class="stat">
        <span>Stocks faibles</span>
        <strong>
            <?= (int) $nbStockFaible ?>
        </strong>
    </div>

    <div class="stat">
        <span>Commandes aujourd'hui</span>
        <strong>
            <?= (int) $nbCommandesDay ?>
        </strong>
    </div>

    <div class="stat">
        <span>Commandes cette semaine</span>
        <strong>
            <?= (int) $nbCommandesWeek ?>
        </strong>
    </div>

</div>


<div class="card">

    <h2>Stocks faibles</h2>

    <?php if (empty($stockFaibles)): ?>

        <p>
            Aucun produit n'a actuellement un stock faible.
        </p>

    <?php else: ?>

        <table class="backoffice-table">

            <thead>

                <tr>
                    <th>Produit</th>
                    <th>Catégorie</th>
                    <th>Stock</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($stockFaibles as $produit): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $produit['nom']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $produit['categorie_nom']
                            ) ?>
                        </td>

                        <td>
                            <strong>
                                <?= (int) $produit['stock'] ?>
                            </strong>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</div>


<div class="card">

    <h2>Commandes récentes</h2>

    <?php if (empty($commandesRecentes)): ?>

        <p>
            Aucune commande.
        </p>

    <?php else: ?>

        <table class="backoffice-table">

            <thead>

                <tr>
                    <th>N°</th>
                    <th>Bénéficiaire</th>
                    <th>Date</th>
                    <th>Statut</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($commandesRecentes as $commande): ?>

                    <tr>

                        <td>
                            #<?= (int)
                                $commande['id_commande'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $commande['prenom']
                                    . ' '
                                    . $commande['nom']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $commande['date_commande']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $commande['statut']
                            ) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</div>