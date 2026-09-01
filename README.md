# Radio ISDB — API & back-office

Back-end de l'application mobile **Radio ISDB** (Institut Supérieur Don Bosco).

- **API REST publique** (lecture seule, sans authentification) consommée par l'app Flutter.
- **Back-office Filament** (`/admin`) pour administrer le flux du direct, les émissions
  enregistrées, les catégories et les réglages de l'application — sans toucher au code.

Stack : Laravel 13 · Filament v4 · PHP 8.3 · SQLite (dev) / MySQL ou PostgreSQL (prod).

---

## Installation (développement)

```bash
cd isdb-radio-api
composer install
cp .env.example .env          # déjà fait au scaffold
php artisan key:generate      # déjà fait
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve             # http://127.0.0.1:8000
```

Le seed crée :

- la configuration du flux (URL de démonstration **Jazz Radio**) ;
- 6 catégories et 8 émissions de démo (+ 1 brouillon, + 1 programmée, pour tester le filtrage de l'API) ;
- un compte administrateur : **`admin@isdb.example`** / **`password`** (à changer).

> ⚠️ `ext-zip` doit être activé dans `php.ini` (requis par Filament). Sur Laragon :
> décommenter `extension=zip`.

---

## API publique

Base : `/api/v1` — limitée à 60 requêtes/minute par IP.

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/stream` | Configuration du direct (URL du flux, nom station, on/air, message hors antenne, logo). |
| `GET` | `/episodes` | Émissions publiées, paginées, triées par date décroissante. Query : `per_page` (max 50), `category` (slug), `search`. |
| `GET` | `/episodes/{slug}` | Détail d'une émission publiée (404 sinon). |
| `POST` | `/episodes/{slug}/play` | Incrémente le compteur d'écoutes (204). |
| `GET` | `/categories` | Catégories triées, avec nombre d'émissions publiées. |
| `GET` | `/app-config` | Liens, texte « à propos », version minimale supportée, URL politique de confidentialité. |

Toutes les réponses passent par des **API Resources** (jamais l'objet Eloquent brut).
Le titre du morceau en cours (now-playing) n'est **pas** fourni par l'API : l'app le lit
dans les métadonnées ICY du flux.

---

## Back-office `/admin`

| Section | Contenu |
|---|---|
| **Configuration › Diffusion en direct** | URL du flux + URL de secours, codec, à l'antenne (oui/non), nom & slogan de la station, message hors antenne, logo. Enregistrement unique. |
| **Configuration › Réglages de l'application** | Texte « à propos », liens (site, réseaux, Play Store, confidentialité), contact, version minimale supportée. Enregistrement unique. |
| **Contenu › Émissions** | CRUD complet : titre, description, catégorie, image de couverture, **fichier audio uploadé OU URL externe**, durée (auto-calculée pour les fichiers via getID3), publication + date. |
| **Contenu › Catégories** | Nom, couleur, ordre d'affichage (réordonnable). |

Accès réservé aux utilisateurs `is_admin = true` (`User::canAccessPanel()`).
Créer un admin : `php artisan make:filament-user` puis passer `is_admin` à `1`.

---

## Tests

```bash
php artisan test
```

- `tests/Feature/Api/*` : contrat des 4 endpoints, filtrage published/brouillon/programmé,
  pagination, filtres catégorie & recherche, compteur d'écoutes.
- `tests/Feature/Admin/PanelSmokeTest` : rendu de toutes les pages du back-office + contrôle d'accès.

---

## Mise en production (résumé)

1. Serveur : PHP 8.3+, Nginx + php-fpm, MySQL/PostgreSQL, HTTPS (Let's Encrypt).
2. `.env` : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...`, base de données réelle.
3. `composer install --no-dev --optimize-autoloader`
   `php artisan migrate --force`
   `php artisan config:cache route:cache view:cache`
   `php artisan storage:link`
4. Stockage des fichiers audio : passer le disque `public` vers un stockage S3-compatible
   (Infomaniak, Backblaze B2, Scaleway) dans `config/filesystems.php` avant une vraie mise en prod ;
   ajuster `upload_max_filesize` / `post_max_size` de PHP.
5. Ajouter la 2FA sur le back-office (`stephenjude/filament-two-factor-authentication` ou équivalent).
6. Renseigner `min_supported_version` pour activer la mise à jour forcée côté app.
