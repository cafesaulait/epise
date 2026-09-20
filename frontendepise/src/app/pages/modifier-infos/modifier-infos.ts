import { Component, OnInit, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';

@Component({
  imports: [FormsModule, RouterLink],
  selector: 'app-modifier-infos',
  styleUrl: './modifier-infos.scss',
  templateUrl: './modifier-infos.html',
})
export class ModifierInfos implements OnInit {
  nom = '';
  prenom = '';
  email = '';

  erreur = signal<string | null>(null);
  message = signal<string | null>(null);
  enCours = signal(false);

  constructor(
    public auth: AuthService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    const u = this.auth.utilisateur();
    if (!u) {
      this.router.navigate(['/connexion']);
      return;
    }
    this.nom = u.nom;
    this.prenom = u.prenom;
    this.email = u.email;
  }

  enregistrer(): void {
    this.erreur.set(null);
    this.message.set(null);

    if (!this.nom || !this.prenom || !this.email) {
      this.erreur.set('Veuillez remplir tous les champs.');
      return;
    }

    this.enCours.set(true);
    this.auth.modifierInfos(this.nom, this.prenom, this.email).subscribe({
      next: () => {
        this.enCours.set(false);
        this.message.set('Vos informations ont bien été mises à jour.');
      },
      error: (err) => {
        this.enCours.set(false);
        this.erreur.set(err.error?.error ?? 'Erreur lors de la mise à jour.');
      },
    });
  }
}
