<h1>Dons en attente</h1>
<a href="/epise/api/backoffice">Retour</a><?php if (!$dons): ?><p>Aucun don en attente.</p><?php endif; ?><?php foreach ($dons as $d): ?><article>
        <h2>Don #<?= (int)$d['id_don'] ?> — <?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></h2><?php foreach ($d['produits'] as $p): ?><p><?= htmlspecialchars($p['nom_produit']) ?> × <?= (int)$p['quantite'] ?></p><?php endforeach; ?>
    </article><?php endforeach; ?>