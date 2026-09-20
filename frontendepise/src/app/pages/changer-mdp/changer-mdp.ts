import { Component, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';

@Component({
  imports: [FormsModule, RouterLink],
  selector: 'app-changer-mdp',
  styleUrl: './changer-mdp.scss',
  templateUrl: './changer-mdp.html',
})
export class ChangerMdp {
  mdpActuel = '';
  nouveauMdp = '';
  nouveauMdpConfirm = '';

  erreur = signal<string | null>(null);
  message = signal<string | null>(null);
  enCours = signal(false);

  constructor(private auth: AuthService) {}

  enregistrer(): void {
    this.erreur.set(null);
    this.message.set(null);

    if (!this.mdpActuel || !this.nouveauMdp || !this.nouveauMdpConfirm) {
      this.erreur.set('Veuillez remplir tous les champs.');
      return;
    }
    if (this.nouveauMdp !== this.nouveauMdpConfirm) {
      this.erreur.set('Le nouveau mot de passe et sa confirmation ne correspondent pas.');
      return;
    }
    if (this.nouveauMdp.length < 8) {
      this.erreur.set('Le nouveau mot de passe doit contenir au moins 8 caractères.');
      return;
    }

    this.enCours.set(true);
    this.auth.changerMotDePasse(this.mdpActuel, this.nouveauMdp).subscribe({
      next: () => {
        this.enCours.set(false);
        this.message.set('Votre mot de passe a bien été modifié.');
        this.mdpActuel = '';
        this.nouveauMdp = '';
        this.nouveauMdpConfirm = '';
      },
      error: (err) => {
        this.enCours.set(false);
        this.erreur.set(err.error?.error ?? 'Erreur lors de la modification du mot de passe.');
      },
    });
  }
}
