import { Component, OnInit, signal } from '@angular/core';
import { RouterLink, Router } from '@angular/router';
import { ProduitService, Produit } from '../../core/services/produit.service';
import { CategorieService, Categorie } from '../../core/services/categorie.service';
import { PanierService } from '../../core/services/panier.service';
import { AuthService } from '../../core/services/auth.service';
import { environment } from '../../../environments/environment';

@Component({
  imports: [RouterLink],
  selector: 'app-main',
  styleUrl: './main.scss',
  templateUrl: './main.html',
})
export class Main implements OnInit {
  readonly imageBaseUrl = `${environment.apiUrl}/assets/img`;
  nouveautes = signal<Produit[]>([]);
  categories = signal<Categorie[]>([]);

  constructor(
    private produitService: ProduitService,
    private categorieService: CategorieService,
    private panierService: PanierService,
    public auth: AuthService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.produitService.getAll().subscribe({
      next: (produits) => {
        const tries = [...produits].sort(
          (a, b) => new Date(b.date_ajout).getTime() - new Date(a.date_ajout).getTime(),
        );
        this.nouveautes.set(tries.slice(0, 10));
      },
      error: (err) => console.error('Erreur chargement des nouveautés :', err),
    });

    this.categorieService.getAll().subscribe({
      next: (categories) => this.categories.set(categories),
      error: (err) => console.error('Erreur chargement des catégories :', err),
    });
  }

  ajouterAuPanier(produit: Produit): void {
    if (!this.auth.utilisateur()) {
      this.router.navigate(['/mon-compte']);
      return;
    }
    this.panierService.ajouter(produit.id_produit, 1).subscribe({
      next: () => alert(`${produit.nom} ajouté au panier`),
      error: (err) => console.error('Erreur ajout au panier :', err),
    });
  }
}
