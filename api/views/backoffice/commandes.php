<h1>Commandes / passages</h1>
<a href="/epise/api/backoffice">Retour</a>
<?php
foreach ($commandes as $c): ?><article>#<?= (int)$c['id_commande'] ?> — <?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?> — <?= htmlspecialchars($c['mode']) ?> — <?= htmlspecialchars($c['statut']) ?> — <?= htmlspecialchars($c['date_commande']) ?></article><?php endforeach; ?>