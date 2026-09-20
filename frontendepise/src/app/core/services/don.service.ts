import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface ProduitDon {
  id_produit?: number | null;
  id_categorie?: number | null;
  categorie_proposee?: string | null;
  nom_produit: string;
  description?: string;
  image?: string | null;
  quantite: number;
}

export interface Don {
  id_don: number;
  id_utilisateur: number;
  date_don: string;
  commentaire: string | null;
  statut: string;
  produits: ProduitDon[];
}

@Injectable({
  providedIn: 'root',
})
export class DonService {
  private readonly apiUrl = `${environment.apiUrl}/dons`;

  constructor(private http: HttpClient) {}

  envoyerDon(
    date_passage: string,
    heure_passage: string,
    commentaire: string,
    produits: ProduitDon[],
  ): Observable<{ id_don: number }> {
    return this.http.post<{ id_don: number }>(
      this.apiUrl,
      {
        date_passage,
        heure_passage,
        commentaire,
        produits,
      },
      { withCredentials: true },
    );
  }
}
