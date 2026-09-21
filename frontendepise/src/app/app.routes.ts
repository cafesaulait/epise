import { Routes } from '@angular/router';
import { Main } from './pages/main/main';
import { Catalogue } from './pages/catalogue/catalogue';
import { Panier } from './pages/panier/panier';
import { Don } from './pages/don/don';
import { MonCompte } from './pages/moncompte/moncompte';
import { ProduitDetail } from './pages/produit-detail/produit-detail';
import { Connexion } from './pages/connexion/connexion';
import { Creationcompte } from './pages/creationcompte/creationcompte';
import { ModifierInfos } from './pages/modifier-infos/modifier-infos';
import { ChangerMdp } from './pages/changer-mdp/changer-mdp';
import { MesCommandes } from './pages/mes-commandes/mes-commandes';
import { MesDons } from './pages/mes-dons/mes-dons';
import { MentionsLegales } from './pages/mentions-legales/mentions-legales';
import { PolitiqueConfidentialites } from './pages/politique-confidentialites/politique-confidentialites';
import { Page404 } from './pages/page404/page404';


export const routes: Routes = [
  { path: '', component: Main },
  { path: 'catalogue', component: Catalogue },
  { path: 'produits/:id', component: ProduitDetail },
  { path: 'panier', component: Panier },
  { path: 'don', component: Don },
  { path: 'mon-compte', component: MonCompte },
  { path: 'mon-compte/modifier', component: ModifierInfos },
  { path: 'mon-compte/changer-mdp', component: ChangerMdp },
  { path: 'mon-compte/commandes', component: MesCommandes },
  { path: 'mon-compte/dons', component: MesDons },
  { path: 'connexion', component: Connexion },
  { path: 'creation-compte', component: Creationcompte },
  { path: 'mentions-legales', component: MentionsLegales },
  { path: 'politique-confidentialites', component: PolitiqueConfidentialites },
  { path: '**', component: Page404 },
];
