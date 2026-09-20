import { Component, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';

@Component({
  imports: [FormsModule, RouterLink],
  selector: 'app-connexion',
  styleUrl: './connexion.scss',
  templateUrl: './connexion.html',
})
export class Connexion {
  email = '';
  mdp = '';
  erreur = signal<string | null>(null);
  enCours = signal(false);

  constructor(
    private auth: AuthService,
    private router: Router,
  ) {}

  seConnecter(): void {
    if (!this.email || !this.mdp) {
      this.erreur.set('Veuillez remplir tous les champs');
      return;
    }

    this.erreur.set(null);
    this.enCours.set(true);

    this.auth.login(this.email, this.mdp).subscribe({
      next: () => {
        this.enCours.set(false);
        this.router.navigate(['/mon-compte']);
      },
      error: (err) => {
        this.enCours.set(false);
        this.erreur.set(
          err.status === 401 ? 'Email ou mot de passe incorrect' : 'Erreur de connexion',
        );
      },
    });
  }
}
