import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface PanierItem {
  id_produit: number;
  quantite: number;
  nom: string;
  description: string;
  image: string;
  stock: number;
}

export interface PanierContenu {
  id_panier: number;
  items: PanierItem[];
  total_unites: number;
}

@Injectable({
  providedIn: 'root',
})
export class PanierService {
  private readonly apiUrl = `${environment.apiUrl}/panier`;

  nombreProduits = signal(0);

  constructor(private http: HttpClient) {}

  getPanier(): Observable<PanierContenu> {
    return this.http
      .get<PanierContenu>(this.apiUrl, {
        withCredentials: true,
      })
      .pipe(
        tap((panier) => {
          this.nombreProduits.set(panier.total_unites);
        }),
      );
  }

  ajouter(id_produit: number, quantite: number = 1): Observable<{ message: string }> {
    return this.http
      .post<{ message: string }>(
        this.apiUrl,
        {
          id_produit,
          quantite,
        },
        {
          withCredentials: true,
        },
      )
      .pipe(
        tap(() => {
          this.nombreProduits.update((nombre) => nombre + quantite);
        }),
      );
  }

  modifierQuantite(id_produit: number, quantite: number): Observable<{ message: string }> {
    return this.http.put<{ message: string }>(
      this.apiUrl,
      {
        id_produit,
        quantite,
      },
      {
        withCredentials: true,
      },
    );
  }

  mettreAJourCompteur(total: number): void {
    this.nombreProduits.set(total);
  }

  retirer(id_produit: number): Observable<void> {
    return this.http.delete<void>(this.apiUrl, {
      withCredentials: true,
      body: {
        id_produit,
      },
    });
  }

  vider(items: PanierItem[]): Observable<void[]> {
    const suppressions = items.map((item) => this.retirer(item.id_produit));

    return new Observable<void[]>((subscriber) => {
      if (suppressions.length === 0) {
        this.nombreProduits.set(0);
        subscriber.next([]);
        subscriber.complete();
        return;
      }

      let termine = 0;
      const resultats: void[] = [];

      suppressions.forEach((requete) => {
        requete.subscribe({
          next: (resultat) => {
            resultats.push(resultat);
            termine++;

            if (termine === suppressions.length) {
              this.nombreProduits.set(0);

              subscriber.next(resultats);
              subscriber.complete();
            }
          },

          error: (erreur) => {
            subscriber.error(erreur);
          },
        });
      });
    });
  }
}
