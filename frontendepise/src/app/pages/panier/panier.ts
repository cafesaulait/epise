import { Component, OnInit, signal, computed } from '@angular/core';

import { RouterLink } from '@angular/router';

import { PanierService, PanierContenu, PanierItem } from '../../core/services/panier.service';

import { CommandeService } from '../../core/services/commande.service';

import { AuthService } from '../../core/services/auth.service';
import { environment } from '../../../environments/environment';

@Component({
  imports: [RouterLink],
  selector: 'app-panier',
  styleUrl: './panier.scss',
  templateUrl: './panier.html',
})
export class Panier implements OnInit {
  readonly imageBaseUrl = `${environment.apiUrl}/assets/img`;
  panier = signal<PanierContenu | null>(null);

  chargement = signal(true);

  erreur = signal<string | null>(null);

  commandeEnCours = signal(false);

  totalArticles = computed(() => {
    return this.panier()?.total_unites ?? 0;
  });

  constructor(
    private panierService: PanierService,
    private commandeService: CommandeService,
    public auth: AuthService,
  ) {}

  ngOnInit(): void {
    this.chargerPanier();
  }

  chargerPanier(): void {
    this.chargement.set(true);
    this.erreur.set(null);

    this.panierService.getPanier().subscribe({
      next: (panier) => {
        this.panier.set(panier);
        this.chargement.set(false);
      },

      error: (err) => {
        console.error('Erreur chargement panier :', err);

        this.chargement.set(false);

        this.erreur.set(
          err.status === 401
            ? 'Vous devez être connecté pour accéder à votre panier.'
            : 'Impossible de charger votre panier.',
        );
      },
    });
  }

  augmenter(item: PanierItem): void {
    if (this.totalArticles() >= 5) {
      alert('Vous ne pouvez pas avoir plus de 5 produits dans votre panier.');

      return;
    }

    if (item.quantite >= item.stock) {
      alert('Vous avez atteint la quantité disponible en stock.');

      return;
    }

    this.modifierQuantite(item, item.quantite + 1);
  }

  diminuer(item: PanierItem): void {
    this.modifierQuantite(item, item.quantite - 1);
  }

  modifierQuantite(item: PanierItem, nouvelleQuantite: number): void {
    if (nouvelleQuantite <= 0) {
      return;
    }

    this.panierService.modifierQuantite(item.id_produit, nouvelleQuantite).subscribe({
      next: () => {
        const contenu = this.panier();

        if (!contenu) {
          return;
        }

        const nouveauxItems = contenu.items.map((produit) =>
          produit.id_produit === item.id_produit
            ? {
                ...produit,
                quantite: nouvelleQuantite,
              }
            : produit,
        );

        const nouveauTotal = nouveauxItems.reduce((total, produit) => total + produit.quantite, 0);

        this.panier.set({
          ...contenu,
          items: nouveauxItems,
          total_unites: nouveauTotal,
        });

        this.panierService.mettreAJourCompteur(nouveauTotal);
      },

      error: (err) => {
        alert(err.error?.error ?? 'Impossible de modifier la quantité.');
      },
    });
  }

  retirer(item: PanierItem): void {
    this.panierService.retirer(item.id_produit).subscribe({
      next: () => {
        const contenu = this.panier();

        if (!contenu) {
          return;
        }

        const nouveauxItems = contenu.items.filter(
          (produit) => produit.id_produit !== item.id_produit,
        );

        const nouveauTotal = nouveauxItems.reduce((total, produit) => total + produit.quantite, 0);

        this.panier.set({
          ...contenu,
          items: nouveauxItems,
          total_unites: nouveauTotal,
        });

        this.panierService.mettreAJourCompteur(nouveauTotal);
      },

      error: (err) => {
        alert(err.error?.error ?? 'Impossible de supprimer ce produit.');
      },
    });
  }

  viderPanier(): void {
    const contenu = this.panier();

    if (!contenu || contenu.items.length === 0) {
      return;
    }

    if (!confirm('Voulez-vous vraiment vider votre panier ?')) {
      return;
    }

    this.panierService.vider(contenu.items).subscribe({
      next: () => {
        this.chargerPanier();
      },

      error: () => {
        alert('Impossible de vider le panier.');
      },
    });
  }

  validerCommande(): void {
    const utilisateur = this.auth.utilisateur();

    if (!utilisateur) {
      alert('Vous devez être connecté pour commander.');

      return;
    }

    if (utilisateur.role !== 'beneficiaire') {
      alert(
        'Seuls les bénéficiaires peuvent commander. Si vous vous êtes trompé, vous pouvez modifier votre rôle sur votre compte.',
      );

      return;
    }

    const contenu = this.panier();

    if (!contenu || contenu.items.length === 0) {
      alert('Votre panier est vide.');

      return;
    }

    if (contenu.total_unites > 5) {
      alert('Une commande ne peut pas contenir plus de 5 produits.');

      return;
    }

    this.commandeEnCours.set(true);

    this.commandeService.commander().subscribe({
      next: (resultat) => {
        this.commandeEnCours.set(false);

        alert(
          `Votre panier est validé !

            Votre numéro de commande est : ${resultat.id_commande}

            Vous pouvez la récupérer à tout moment à l'EPISE.

            Vous pouvez annuler votre commande en allant dans "Voir mes commandes" tant qu'elle n'a pas été récupérée.`,
        );

        this.panier.set({
          id_panier: 0,
          items: [],
          total_unites: 0,
        });

        this.panierService.mettreAJourCompteur(0);
      },

      error: (err) => {
        this.commandeEnCours.set(false);

        alert(err.error?.error ?? 'Impossible de valider votre commande.');
      },
    });
  }

  imprimerListe(): void {
    window.print();
  }
}
