<h1>Panier</h1>
<p><?= (int)$total ?> / 5 produits</p><?php foreach ($items as $item): ?><p><?= htmlspecialchars($item['nom']) ?> × <?= (int)$item['quantite'] ?></p><?php endforeach; ?>