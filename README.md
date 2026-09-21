# EPISE

Projet d'epicerie solidaire compose d'un frontend Angular et d'une API PHP/MySQL.

## Architecture

- Frontend Angular : [epise-unc.netlify.app](https://epise-unc.netlify.app/)
- Version anglaise : [epise-unc.netlify.app/en/](https://epise-unc.netlify.app/en/)
- API et backoffice PHP : [backoffice-epise-unc.infinityfree.me](https://backoffice-epise-unc.infinityfree.me/)
- Base de donnees : MySQL sur InfinityFree

Le frontend n'accede jamais directement a MySQL. Il appelle l'API HTTPS, qui
retourne des donnees JSON.

## Installation locale

### API

Placer le dossier `api` dans un serveur PHP avec MySQL, puis configurer les
variables `EPISE_DB_HOST`, `EPISE_DB_USER`, `EPISE_DB_PASSWORD` et
`EPISE_DB_NAME` avec les valeurs indiquees par InfinityFree. En local, les
valeurs par defaut utilisent MySQL sur `localhost` avec la base `episebdd`.

Le point d'entree est `api/index.php`. Le fichier `api/.htaccess` permet les
URLs propres comme `/produits` et `/categories`.

### Frontend

```powershell
cd frontendepise
npm install
npm run start:fr
```

Frontend francais local : `http://localhost:4200/`

Pour lancer la version anglaise localement :

```powershell
npm run start:en
```

Frontend anglais local : `http://localhost:4201/`

## Build Netlify

```powershell
npm run build:netlify
```

Le script genere les deux variantes :

- `dist/index.html` : francais ;
- `dist/en/index.html` : anglais.

Netlify publie `dist`. La configuration des routes se trouve dans
`frontendepise/netlify.toml`.

## Routes principales de l'API

```text
GET    /produits
GET    /produits/{id}
POST   /produits              admin
PUT    /produits/{id}         admin
DELETE /produits/{id}         admin

GET    /categories
GET    /categories/{id}

GET    /connexion
POST   /connexion
POST   /connexion/deconnexion

POST   /utilisateurs
GET    /utilisateurs/{id}
PUT    /utilisateurs/{id}
DELETE /utilisateurs/{id}

GET    /panier                 session utilisateur
POST   /panier                 beneficiaire
PUT    /panier                 beneficiaire
DELETE /panier                 beneficiaire

GET    /dons
POST   /dons                   donateur
GET    /commandes
GET    /horaires
```

Les corps des requetes sont envoyes en JSON. Les reponses de l'API sont
egalement au format JSON. Les codes HTTP utilises comprennent notamment
`200`, `201`, `204`, `400`, `401`, `403`, `404`, `405`, `409` et `500`.

## CORS et sessions

L'origine Netlify autorisee est :

```text
https://epise-unc.netlify.app
```

Les appels Angular utilisent `withCredentials` pour conserver la session PHP.
L'API doit donc etre accessible en HTTPS et envoyer les en-tetes CORS avec
l'origine exacte du frontend.

## Images

Les images fixes du frontend restent dans `frontendepise/public/asset/img/`.
Les images de produits et de categories gerees par le backoffice sont stockees
sur le serveur API :

```text
api/assets/img/produits/
api/assets/img/categories/
```

Elles sont accessibles publiquement avec :

```text
https://backoffice-epise-unc.infinityfree.me/assets/img/produits/NOM_IMAGE.png
https://backoffice-epise-unc.infinityfree.me/assets/img/categories/NOM_IMAGE.png
```

Le backoffice cree un nom aleatoire pour chaque upload et limite les types
acceptes aux formats JPG, PNG, WEBP et GIF.

## Design patterns

### Singleton

`api/app/ConnexionBDD.php` utilise un Singleton pour partager une connexion
MySQL unique entre les modeles. Le constructeur est prive et la connexion est
recuperee avec `ConnexionBDD::getInstance()`.

### Fabrique

`api/factories/ProduitFactory.php` utilise une Fabrique pour centraliser la
creation d'un produit issu d'un don. Elle valide les donnees, execute la
creation SQL et retourne l'identifiant du produit.

## API RESTful

La partie JSON de l'API manipule des ressources identifiees par URL et utilise
les verbes HTTP standards : GET, POST, PUT et DELETE. Elle renvoie des codes HTTP
significatifs et des donnees JSON. Le frontend et le serveur sont separes.

La qualification la plus precise est donc : API RESTful pour les endpoints JSON,
avec une authentification par session PHP. Le projet complet comprend aussi un
backoffice HTML, ce qui signifie qu'il ne s'agit pas d'une API REST purement
stateless dans tous ses usages.

## Securite et configuration

Ne pas publier dans Git :

- le mot de passe MySQL ;
- les identifiants administrateur ;
- des fichiers de configuration contenant des secrets.

Les identifiants a fournir pour la demonstration doivent etre transmis
separement.

Pour une documentation detaillee destinee au dossier de remise, consulter
`DOCUMENTATION_PROJET.txt`.
