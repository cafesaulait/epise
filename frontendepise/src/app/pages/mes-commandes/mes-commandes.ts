import { Component, OnInit, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { RouterLink } from '@angular/router';
import { CommandeService, Commande } from '../../core/services/commande.service';

@Component({
  imports: [DatePipe, RouterLink],
  selector: 'app-mes-commandes',
  styleUrl: './mes-commandes.scss',
  templateUrl: './mes-commandes.html',
})
export class MesCommandes implements OnInit {
  commandes = signal<Commande[]>([]);

  chargement = signal(true);

  erreur = signal<string | null>(null);

  constructor(private commandeService: CommandeService) {}

  ngOnInit(): void {
    this.chargerCommandes();
  }

  chargerCommandes(): void {
    this.chargement.set(true);
    this.erreur.set(null);

    this.commandeService.getMesCommandes().subscribe({
      next: (commandes) => {
        this.commandes.set(commandes);
        this.chargement.set(false);
      },

      error: (err) => {
        console.error('Erreur chargement commandes :', err);

        this.erreur.set(err.error?.error ?? 'Impossible de charger vos commandes.');

        this.chargement.set(false);
      },
    });
  }

  annulerCommande(commande: Commande): void {
    if (commande.statut !== 'en_attente') {
      return;
    }

    const confirmation = confirm(
      `Voulez-vous vraiment annuler la commande n°${commande.id_commande} ?`,
    );

    if (!confirmation) {
      return;
    }

    this.commandeService.annuler(commande.id_commande).subscribe({
      next: () => {
        alert(`La commande n°${commande.id_commande} a bien été annulée.`);

        this.chargerCommandes();
      },

      error: (err) => {
        alert(err.error?.error ?? "Impossible d'annuler cette commande.");
      },
    });
  }
}
