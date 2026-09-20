import { Component, OnInit, signal, computed } from '@angular/core';
import { SlicePipe } from '@angular/common';
import { ActivatedRoute, RouterLink, Router } from '@angular/router';
import { ProduitService, Produit } from '../../core/services/produit.service';
import { CategorieService, Categorie } from '../../core/services/categorie.service';
import { PanierService } from '../../core/services/panier.service';
import { AuthService } from '../../core/services/auth.service';
import { environment } from '../../../environments/environment';

@Component({
  imports: [SlicePipe, RouterLink],
  selector: 'app-catalogue',
  styleUrl: './catalogue.scss',
  templateUrl: './catalogue.html',
})
export class Catalogue implements OnInit {
  readonly imageBaseUrl = `${environment.apiUrl}/assets/img`;
  produits = signal<Produit[]>([]);
  categories = signal<Categorie[]>([]);
  recherche = signal<string>('');

  nouveautes = computed(() =>
    [...this.produits()]
      .sort((a, b) => new Date(b.date_ajout).getTime() - new Date(a.date_ajout).getTime())
      .slice(0, 10),
  );

  produitsFiltres = computed(() => {
    const terme = this.recherche().trim().toLowerCase();
    if (!terme) return this.produits();
    return this.produits().filter((p) => p.nom.toLowerCase().includes(terme));
  });

  constructor(
    private produitService: ProduitService,
    private categorieService: CategorieService,
    private panierService: PanierService,
    public auth: AuthService,
    private router: Router,
    private route: ActivatedRoute,
  ) {}

  ngOnInit(): void {
    this.route.queryParams.subscribe((params) => {
      this.recherche.set(params['recherche'] ?? '');
    });

    this.produitService.getAll().subscribe({
      next: (produits) => this.produits.set(produits),
      error: (err) => console.error('Erreur chargement des produits :', err),
    });

    this.categorieService.getAll().subscribe({
      next: (categories) => this.categories.set(categories),
      error: (err) => console.error('Erreur chargement des catégories :', err),
    });
  }

  produitsDeCategorie(id_categorie: number): Produit[] {
    return this.produits().filter((p) => p.id_categorie === id_categorie);
  }

  ajouterAuPanier(produit: Produit): void {
    if (!this.auth.utilisateur()) {
      alert(
        'Vous devez vous connecter à votre compte étudiant ou vous en créer un pour passer commande.',
      );

      this.router.navigate(['/connexion']);
      return;
    }

    if (this.auth.utilisateur()?.role !== 'beneficiaire') {
      alert('Vous devez utiliser un compte étudiant bénéficiaire pour passer commande.');
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
