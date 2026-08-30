<h1>Tableau de bord</h1>
<p>Bienvenue <?= htmlspecialchars($_SESSION['prenom'] ?? '') ?>.</p>
<div class="stats">
    <p>Étudiants : <?= (int)$nbEtudiants['total'] ?></p>
    <p>Donateurs : <?= (int)$nbDonateurs['total'] ?></p>
    <p>Produits : <?= (int)$nbProduits['total'] ?></p>
    <p>Stock faible : <?= (int)$nbStockFaible['total'] ?></p>
    <p>Commandes aujourd'hui : <?= (int)$nbCommandesDay['total'] ?></p>
    <p>Commandes cette semaine : <?= (int)$nbCommandesWeek['total'] ?></p>
</div>
<nav><a href="/epise/api/backoffice/produits">Produits</a> | <a href="/epise/api/backoffice/commandes">Commandes</a> | <a href="/epise/api/backoffice/dons">Dons</a></nav>
<h2>Stock faible</h2><?php foreach ($stockFaibles as $p): ?><p><?= htmlspecialchars($p['nom']) ?> — <?= (int)$p['stock'] ?></p><?php endforeach; ?>