// Ajouter une ligne produit dynamiquement
document.getElementById('ajouter-produit').addEventListener('click', function () {
  const liste = document.getElementById('liste-produits');

  const ligne = document.createElement('div');
  ligne.classList.add('ligne-produit'); // classe CSS

  ligne.innerHTML = `
    <select class="don-select" name="categorie[]">
      <option value="">Sélectionner</option>
      <option value="textile">Textile</option>
      <option value="alimentaire">Alimentaire</option>
      <option value="menager">Produits ménagers</option>
      <option value="hygiene">Hygiène</option>
      <option value="autre">Autre</option>
    </select>
    <input class="don-input-text" type="text" name="nom_produit[]" placeholder="Nom du produit">
    <input class="don-input-number" type="number" name="quantite[]" placeholder="Ex. : 2" min="1">
    <button type="button" class="button danger supprimer"><i class="fa-solid fa-trash-can"></i>Supprimer</button>
  `;

  liste.appendChild(ligne);

  // Gérer le bouton supprimer de la nouvelle ligne
  ligne.querySelector('.supprimer').addEventListener('click', function () {
    liste.removeChild(ligne);
  });
});

// Gérer les boutons supprimer déjà présents dans le HTML
document.querySelectorAll('#liste-produits button').forEach(function (btn) {
  btn.addEventListener('click', function () {
    btn.parentElement.remove();
  });
});