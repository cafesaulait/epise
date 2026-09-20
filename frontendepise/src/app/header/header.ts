import { Component, OnInit, effect } from '@angular/core';

import { RouterLink } from '@angular/router';

import { PanierService } from '../core/services/panier.service';

import { AuthService } from '../core/services/auth.service';

@Component({
  imports: [RouterLink],
  selector: 'app-header',
  styleUrl: './header.scss',
  templateUrl: './header.html',
})
export class Header implements OnInit {
  constructor(
    public panierService: PanierService,
    private auth: AuthService,
  ) {
    effect(() => {
      const utilisateur = this.auth.utilisateur();

      if (utilisateur) {
        this.panierService.getPanier().subscribe({
          error: () => {
            this.panierService.mettreAJourCompteur(0);
          },
        });
      } else {
        this.panierService.mettreAJourCompteur(0);
      }
    });
  }

  ngOnInit(): void {}

  get langueActuelle(): 'fr' | 'en' {
    return window.location.pathname === '/en' || window.location.pathname.startsWith('/en/')
      ? 'en'
      : 'fr';
  }

  get lienAutreLangue(): string {
    if (this.langueActuelle === 'en') {
      return window.location.pathname.replace(/^\/en(?=\/|$)/, '') || '/';
    }

    return `/en${window.location.pathname === '/' ? '/' : window.location.pathname}`;
  }
}
