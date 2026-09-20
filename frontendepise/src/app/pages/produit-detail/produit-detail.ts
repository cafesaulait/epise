import { Component, OnInit, signal } from '@angular/core';
import { ActivatedRoute, RouterLink, Router } from '@angular/router';
import { ProduitService, Produit } from '../../core/services/produit.service';
import { PanierService } from '../../core/services/panier.service';
import { AuthService } from '../../core/services/auth.service';

@Component({
  imports: [RouterLink],
  selector: 'app-produit-detail',
  styleUrl: './produit-detail.scss',
  templateUrl: './produit-detail.html',
})
export class ProduitDetail implements OnInit {
  produit = signal<Produit | null>(null);
  introuvable = signal(false);

  constructor(
    private produitService: ProduitService,
    private panierService: PanierService,
    public auth: AuthService,
    private router: Router,
    private route: ActivatedRoute,
  ) {}

  ngOnInit(): void {
    this.route.paramMap.subscribe((params) => {
      const id = Number(params.get('id'));
      if (!id) {
        this.introuvable.set(true);
        return;
      }
      this.produitService.getOne(id).subscribe({
        next: (produit) => this.produit.set(produit),
        error: () => this.introuvable.set(true),
      });
    });
  }

  ajouterAuPanier(produit: Produit): void {
    if (!this.auth.utilisateur()) {
      this.router.navigate(['/connexion']);

      return;
    }

    this.panierService.ajouter(produit.id_produit, 1).subscribe({
      next: () => {
        alert(`${produit.nom} ajouté au panier`);
      },

      error: (err) => {
        console.error('Erreur ajout au panier :', err);

        alert(err.error?.error ?? 'Impossible d’ajouter ce produit au panier.');
      },
    });
  }
}
