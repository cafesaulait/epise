import { Component, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';

@Component({
  imports: [RouterLink],
  selector: 'app-moncompte',
  styleUrl: './moncompte.scss',
  templateUrl: './moncompte.html',
})
export class MonCompte implements OnInit {
  constructor(public auth: AuthService) {}

  ngOnInit(): void {
  }

  seDeconnecter(): void {
    this.auth.logout().subscribe();
  }

  supprimerCompte(): void {
    const utilisateur = this.auth.utilisateur();
    if (!utilisateur) {
      return;
    }

    if (confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
      this.auth.supprimerCompte(utilisateur.id_utilisateur).subscribe({
        next: () => {
          alert('Votre compte a été supprimé.');
        },
        error: (err) => {
          console.error('Erreur suppression compte :', err);
          alert(err.error?.error ?? 'Impossible de supprimer votre compte.');
        },
      });
    }
  }
}
