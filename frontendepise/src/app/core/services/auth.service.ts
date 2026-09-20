import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { tap } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface Utilisateur {
  id_utilisateur: number;
  nom: string;
  prenom: string;
  email: string;
  role: 'donateur' | 'beneficiaire';
  mdp_verifie: number;
  date_inscription: string;
}

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly apiUrlConnexion = `${environment.apiUrl}/connexion`;
  private readonly apiUrlUtilisateurs = `${environment.apiUrl}/utilisateurs`;

  utilisateur = signal<Utilisateur | null>(null);

  constructor(private http: HttpClient) {}

  chargerSession() {
    return this.http
      .get<{ utilisateur: Utilisateur | null }>(this.apiUrlConnexion, { withCredentials: true })
      .pipe(tap((res) => this.utilisateur.set(res.utilisateur)));
  }

  login(email: string, mdp: string) {
    return this.http
      .post<{ utilisateur: Utilisateur }>(
        this.apiUrlConnexion,
        { email, mdp },
        { withCredentials: true },
      )
      .pipe(tap((res) => this.utilisateur.set(res.utilisateur)));
  }

  inscription(nom: string, prenom: string, email: string, mdp: string, role: string) {
    return this.http
      .post<{ utilisateur: Utilisateur }>(
        this.apiUrlUtilisateurs,
        { nom, prenom, email, mdp, role },
        { withCredentials: true },
      )
      .pipe(tap((res) => this.utilisateur.set(res.utilisateur)));
  }

  modifierInfos(nom: string, prenom: string, email: string) {
    const id = this.utilisateur()?.id_utilisateur;
    return this.http
      .put<{ utilisateur: Utilisateur }>(
        `${this.apiUrlUtilisateurs}/${id}`,
        { nom, prenom, email },
        { withCredentials: true },
      )
      .pipe(tap((res) => this.utilisateur.set(res.utilisateur)));
  }

  changerMotDePasse(mdpActuel: string, nouveauMdp: string) {
    return this.http.post<{ message: string }>(
      `${this.apiUrlUtilisateurs}/motDePasse`,
      { mdp_actuel: mdpActuel, nouveau_mdp: nouveauMdp },
      { withCredentials: true },
    );
  }

  logout() {
    return this.http
      .post(`${this.apiUrlConnexion}/deconnexion`, {}, { withCredentials: true })
      .pipe(tap(() => this.utilisateur.set(null)));
  }

  supprimerCompte(id_utilisateur: number) {
    return this.http
      .delete<{ message: string }>(`${this.apiUrlUtilisateurs}/${id_utilisateur}`, {
        withCredentials: true,
      })
      .pipe(tap(() => this.utilisateur.set(null)));
  }
}
