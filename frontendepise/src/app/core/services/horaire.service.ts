import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface Horaire {
  id_horaire: number;
  jour: number;
  nom_jour: string;
  ouvert: number;
  ouverture_1: string | null;
  fermeture_1: string | null;
  ouverture_2: string | null;
  fermeture_2: string | null;
}

@Injectable({
  providedIn: 'root',
})
export class HoraireService {
  private readonly apiUrl = `${environment.apiUrl}/horaires`;

  constructor(private http: HttpClient) {}

  getAll(): Observable<Horaire[]> {
    return this.http.get<Horaire[]>(this.apiUrl, {
      withCredentials: true,
    });
  }
}
