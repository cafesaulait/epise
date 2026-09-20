import { Component, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';

@Component({
  imports: [FormsModule, RouterLink],
  selector: 'app-creationcompte',
  styleUrl: './creationcompte.scss',
  templateUrl: './creationcompte.html',
})
export class Creationcompte {
  nom = '';
  prenom = '';
  email = '';
  mdp = '';
  mdpConfirm = '';
  role: 'beneficiaire' | 'donateur' = 'beneficiaire';

  erreur = signal<string | null>(null);
  message = signal<string | null>(null);
  enCours = signal(false);

  afficherVerification = signal(false);

  constructor(
    private auth: AuthService,
    private router: Router,
  ) {}

  inscrire(): void {
    this.erreur.set(null);
    this.message.set(null);

    if (!this.nom || !this.prenom || !this.email || !this.mdp || !this.mdpConfirm) {
      this.erreur.set('Veuillez remplir tous les champs.');
      return;
    }

    if (this.mdp !== this.mdpConfirm) {
      this.erreur.set('Les mots de passe ne sont pas identiques.');
      return;
    }

    this.afficherVerification.set(true);
  }

  confirmerEtCreerCompte(): void {
    this.enCours.set(true);

    this.auth.inscription(this.nom, this.prenom, this.email, this.mdp, this.role).subscribe({
      next: (res) => {
        this.enCours.set(false);
        this.afficherVerification.set(false);

        this.message.set(`Votre compte a bien été créé. Bienvenue ${res.utilisateur.prenom} !`);

        this.router.navigate(['/mon-compte']);
      },

      error: (err) => {
        this.enCours.set(false);
        this.afficherVerification.set(false);

        if (err.status === 409) {
          this.erreur.set('Cette adresse e-mail est déjà utilisée.');
        } else if (err.status === 400) {
          this.erreur.set(err.error?.error ?? 'Les informations saisies sont invalides.');
        } else {
          this.erreur.set('Une erreur est survenue lors de la création du compte.');
        }
      },
    });
  }

  annulerVerification(): void {
    this.afficherVerification.set(false);
  }
}
