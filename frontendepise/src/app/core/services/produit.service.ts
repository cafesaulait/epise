import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface Produit {
  id_produit: number;
  nom: string;
  description: string;
  image: string;
  stock: number;
  id_categorie: number;
  date_ajout: string;
}

@Injectable({ providedIn: 'root' })
export class ProduitService {
  private readonly apiUrl = `${environment.apiUrl}/produits`;

  constructor(private http: HttpClient) {}

  getAll(): Observable<Produit[]> {
    return this.http.get<Produit[]>(this.apiUrl);
  }

  getOne(id: number): Observable<Produit> {
    return this.http.get<Produit>(`${this.apiUrl}/${id}`);
  }
}
