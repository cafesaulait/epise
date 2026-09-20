/**
 * catalogue.js
 * Gère les boutons "Ajouter" dans la vue views/produits/index.php.
 * Les données produit sont lues depuis les data-attributs du bouton.
 * Le panier est stocké dans localStorage.
 */
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".btn-ajouter").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation(); // ne pas suivre le lien <a> parent

      const produit = {
        id: parseInt(btn.dataset.id),
        nom: btn.dataset.nom,
        image: btn.dataset.image,
        alt: btn.dataset.alt,
        quantite: 1,
      };

      ajouterAuPanier(produit);
      afficherConfirmation(`"${produit.nom}" ajouté au panier`);
      mettreAJourCompteurHeader();
    });
  });

    function ajouterAuPanier(produit) {
    let panier = JSON.parse(localStorage.getItem("panier") || "[]");

    // Calcule le total d'articles déjà dans le panier
    const totalActuel = panier.reduce((acc, p) => acc + p.quantite, 0);
    if (totalActuel >= 5) {
        alert("Vous ne pouvez pas ajouter plus de 5 articles par passage.");
        return;
    }

    const existant = panier.find((p) => p.id === produit.id);
    if (existant) {
        existant.quantite++;
    } else {
        panier.push(produit);
    }
    localStorage.setItem("panier", JSON.stringify(panier));
    }

  function mettreAJourCompteurHeader() {
    const panier = JSON.parse(localStorage.getItem("panier") || "[]");
    const total = panier.reduce((acc, p) => acc + p.quantite, 0);
    const badge = document.getElementById("panier-compteur");
    if (badge) {
      badge.textContent = total;
      badge.style.display = total > 0 ? "inline-block" : "none";
    }
  }

  function afficherConfirmation(texte) {
    let msg = document.querySelector(".msg-confirmation");
    if (!msg) {
      msg = document.createElement("div");
      msg.className = "msg-confirmation";
      document.body.appendChild(msg);
    }
    msg.textContent = texte;
    msg.classList.add("visible");
    setTimeout(() => msg.classList.remove("visible"), 2000);
  }

  // Badge au chargement de la page
  mettreAJourCompteurHeader();
});
