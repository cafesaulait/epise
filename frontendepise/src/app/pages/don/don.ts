import { Component, OnInit, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';

import { Categorie, CategorieService } from '../../core/services/categorie.service';
import { DonService, ProduitDon } from '../../core/services/don.service';
import { Horaire, HoraireService } from '../../core/services/horaire.service';
import { AuthService } from '../../core/services/auth.service';

interface LigneDon {
  id: number;
  id_categorie: string;
  categorie_proposee: string;
  nom_produit: string;
  quantite: number | null;
}

@Component({
  imports: [FormsModule],
  selector: 'app-don',
  styleUrl: './don.scss',
  templateUrl: './don.html',
})
export class Don implements OnInit {
  private categorieService = inject(CategorieService);
  private donService = inject(DonService);
  private horaireService = inject(HoraireService);
  private router = inject(Router);

  public auth = inject(AuthService);

  dateMinimum = '';
  heureMinimum = '';
  heureMaximum = '';

  categories: Categorie[] = [];
  horaires: Horaire[] = [];

  lignes: LigneDon[] = [
    {
      id: 1,
      id_categorie: '',
      categorie_proposee: '',
      nom_produit: '',
      quantite: null,
    },
    {
      id: 2,
      id_categorie: '',
      categorie_proposee: '',
      nom_produit: '',
      quantite: null,
    },
  ];

  date = '';
  heure = '';
  commentaire = '';
  certification = false;

  messageErreur = '';
  messageSucces = '';
  envoiEnCours = false;

  tentativeEnvoi = false;

  private prochainId = 3;

  ngOnInit(): void {
    this.definirDateMinimum();

    this.categorieService.getAll().subscribe({
      next: (categories) => {
        this.categories = categories;
      },
      error: () => {
        this.messageErreur = 'Impossible de charger les catégories. Veuillez réessayer.';
      },
    });

    this.horaireService.getAll().subscribe({
      next: (horaires) => {
        this.horaires = horaires;
      },
      error: () => {
        this.messageErreur = 'Impossible de charger les horaires de l’EPISE. Veuillez réessayer.';
      },
    });

    if (!this.auth.utilisateur()) {
      alert(
        'Vous devez vous connecter à votre compte donateur ou vous en créer un pour faire un don.',
      );
    }
  }

  private definirDateMinimum(): void {
    const maintenant = new Date();

    const annee = maintenant.getFullYear();
    const mois = String(maintenant.getMonth() + 1).padStart(2, '0');
    const jour = String(maintenant.getDate()).padStart(2, '0');

    this.dateMinimum = `${annee}-${mois}-${jour}`;
  }

  dateSelectionnee(): Horaire | undefined {
    if (!this.date) {
      return undefined;
    }

    const dateChoisie = new Date(`${this.date}T12:00:00`);

    const jourJavaScript = dateChoisie.getDay();

    const jourBdd = jourJavaScript === 0 ? 7 : jourJavaScript;

    return this.horaires.find((horaire) => Number(horaire.jour) === jourBdd);
  }

  heureDansUnCreneau(
    heureChoisie: string,
    ouverture: string | null,
    fermeture: string | null,
  ): boolean {
    if (!ouverture || !fermeture) {
      return false;
    }

    const heure = heureChoisie.slice(0, 5);
    const debut = ouverture.slice(0, 5);
    const fin = fermeture.slice(0, 5);

    return heure >= debut && heure <= fin;
  }

  dateEtHeureDisponibles(): boolean {
    if (!this.date || !this.heure) {
      return false;
    }

    const maintenant = new Date();
    const passage = new Date(`${this.date}T${this.heure}`);

    if (passage <= maintenant) {
      return false;
    }

    const horaire = this.dateSelectionnee();

    if (!horaire) {
      return false;
    }

    if (!horaire.ouvert) {
      return false;
    }

    const premierCreneau = this.heureDansUnCreneau(
      this.heure,
      horaire.ouverture_1,
      horaire.fermeture_1,
    );

    const deuxiemeCreneau = this.heureDansUnCreneau(
      this.heure,
      horaire.ouverture_2,
      horaire.fermeture_2,
    );

    return premierCreneau || deuxiemeCreneau;
  }

  heureAErreur(): boolean {
    if (!this.date || !this.heure) {
      return false;
    }

    return !this.dateEtHeureDisponibles();
  }

  messageHoraire(): string {
    if (!this.date) {
      return 'Veuillez sélectionner une date de passage.';
    }

    const horaire = this.dateSelectionnee();

    if (!horaire) {
      return 'Aucun horaire n’est disponible pour cette date.';
    }

    if (!horaire.ouvert) {
      return 'L’EPISE est fermée ce jour-là. Veuillez choisir une autre date.';
    }

    const maintenant = new Date();
    const passage = new Date(`${this.date}T${this.heure}`);

    if (passage <= maintenant) {
      return 'L’heure choisie est déjà passée. Veuillez choisir un créneau à venir.';
    }

    return 'Veuillez choisir une heure comprise dans les horaires d’ouverture de l’EPISE.';
  }

  ajouterProduit(): void {
    this.lignes.push({
      id: this.prochainId++,
      id_categorie: '',
      categorie_proposee: '',
      nom_produit: '',
      quantite: null,
    });
  }

  supprimerProduit(id: number): void {
    if (this.lignes.length <= 1) {
      return;
    }

    this.lignes = this.lignes.filter((ligne) => ligne.id !== id);
  }

  estAutre(ligne: LigneDon): boolean {
    return ligne.id_categorie === 'autre';
  }

  envoyerDon(): void {
    this.tentativeEnvoi = true;
    this.messageErreur = '';
    this.messageSucces = '';

    if (!this.auth.utilisateur()) {
      alert(
        'Vous devez vous connecter à votre compte donateur ou vous en créer un pour faire un don.',
      );

      this.router.navigate(['/connexion']);
      return;
    }

    if (this.auth.utilisateur()?.role !== 'donateur') {
      this.messageErreur = 'Vous devez utiliser un compte donateur pour faire un don.';
      return;
    }

    if (!this.date || !this.heure) {
      this.messageErreur = 'Veuillez sélectionner une date et une heure de passage.';
      return;
    }

    if (!this.dateEtHeureDisponibles()) {
      this.messageErreur = this.messageHoraire();
      return;
    }

    if (!this.certification) {
      this.messageErreur = 'Vous devez certifier que les produits sont non ouverts.';
      return;
    }

    for (const ligne of this.lignes) {
      if (!ligne.id_categorie) {
        this.messageErreur = 'Veuillez sélectionner une catégorie pour chaque produit.';
        return;
      }

      if (ligne.id_categorie === 'autre' && !ligne.categorie_proposee.trim()) {
        this.messageErreur = 'Veuillez indiquer la nouvelle catégorie proposée.';
        return;
      }

      if (!ligne.nom_produit.trim()) {
        this.messageErreur = 'Veuillez renseigner le nom de chaque produit.';
        return;
      }

      if (!ligne.quantite || ligne.quantite < 1) {
        this.messageErreur = 'La quantité doit être supérieure ou égale à 1.';
        return;
      }
    }

    const produits: ProduitDon[] = this.lignes.map((ligne) => ({
      id_produit: null,

      id_categorie: ligne.id_categorie === 'autre' ? null : Number(ligne.id_categorie),

      categorie_proposee: ligne.id_categorie === 'autre' ? ligne.categorie_proposee.trim() : null,

      nom_produit: ligne.nom_produit.trim(),

      quantite: ligne.quantite as number,
    }));

    this.envoiEnCours = true;

    this.donService.envoyerDon(this.date, this.heure, this.commentaire, produits).subscribe({
      next: () => {
        this.envoiEnCours = false;

        this.messageSucces =
          'Nous avons bien reçu votre demande de don. Vous recevrez une confirmation dans Mon compte → Mes dons pour savoir si votre don a été accepté.';

        this.tentativeEnvoi = false;
        this.date = '';
        this.heure = '';
        this.commentaire = '';
        this.certification = false;

        this.lignes = [
          {
            id: 1,
            id_categorie: '',
            categorie_proposee: '',
            nom_produit: '',
            quantite: null,
          },
        ];

        window.scrollTo({ top: 0, behavior: 'smooth' });
      },

      error: (erreur) => {
        this.envoiEnCours = false;

        console.error('Erreur lors de l’envoi du don :', erreur);

        this.messageErreur =
          erreur?.error?.error ?? 'Une erreur est survenue lors de l’envoi du don.';
      },
    });
  }
}
