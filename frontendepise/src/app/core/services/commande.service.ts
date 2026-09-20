import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface Commande {
  id_commande: number;
  id_utilisateur: number;
  date_commande: string;
  mode: 'en_ligne' | 'magasin';
  statut: 'en_attente' | 'confirmee' | 'annulee' | 'recuperee';
}

@Injectable({
  providedIn: 'root',
})
export class CommandeService {
  private readonly apiUrl = `${environment.apiUrl}/commandes`;

  constructor(private http: HttpClient) {}

  getMesCommandes(): Observable<Commande[]> {
    return this.http.get<Commande[]>(this.apiUrl, { withCredentials: true });
  }

  commander(): Observable<{
    id_commande: number;
    message?: string;
  }> {
    return this.http.post<{
      id_commande: number;
      message?: string;
    }>(this.apiUrl, {}, { withCredentials: true });
  }

  annuler(id_commande: number): Observable<{
    message: string;
  }> {
    return this.http.delete<{
      message: string;
    }>(`${this.apiUrl}/${id_commande}`, { withCredentials: true });
  }
}
