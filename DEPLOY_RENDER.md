# Déploiement sur Render — Radio ISDB API

Backend Laravel 13 + Filament v4. Conteneur Docker (Apache + mod_php 8.3),
base PostgreSQL, health check sur `/up`.

Fichiers fournis : `Dockerfile`, `.dockerignore`, `docker/entrypoint.sh`, `render.yaml`.

---

## 1. Pousser ce dossier sur GitHub

Le dossier `isdb-radio-api/` n'est pas encore un dépôt Git. Depuis `isdb-radio-api/` :

```bash
git init -b main
git add -A
git commit -m "chore: backend Radio ISDB + déploiement Render"
```

Crée un dépôt **vide** sur GitHub (sans README) : https://github.com/new
(par ex. `Edem-K11/isdb-radio-api`), puis :

```bash
git remote add origin https://github.com/Edem-K11/isdb-radio-api.git
git push -u origin main
```

> Le `.gitignore` exclut déjà `.env`, `/vendor`, les `*.sqlite` et les dossiers
> d'upload : aucun secret ni fichier lourd n'est envoyé.

## 2. Créer le service sur Render

1. https://dashboard.render.com → **New** → **Blueprint**.
2. Sélectionne le dépôt `isdb-radio-api`. Render lit `render.yaml` et propose :
   - un **PostgreSQL** `isdb-radio-db` (plan free) ;
   - un **Web Service** Docker `isdb-radio-api` (plan free).
3. **Apply**. Le premier build prend ~5–8 min.

## 3. Renseigner les variables secrètes

Dans **isdb-radio-api → Environment**, remplis les variables marquées « sync:false » :

| Variable | Valeur |
|---|---|
| `APP_KEY` | lance `php artisan key:generate --show` en local et colle la valeur `base64:…` |
| `APP_URL` | l'URL du service, ex. `https://isdb-radio-api.onrender.com` (visible après le 1ᵉʳ déploiement) |
| `ADMIN_EMAIL` | ton email admin réel |
| `ADMIN_PASSWORD` | un mot de passe fort |

`DB_URL` est branché automatiquement sur la base par le blueprint.

Après avoir défini `APP_URL`, clique **Manual Deploy → Deploy latest commit** pour
que l'URL soit prise dans le cache de config.

## 4. Données initiales (automatique)

Aucune commande à lancer : au démarrage, le conteneur exécute
`migrate --force` puis `db:seed --force`. Le seed :

- crée le compte admin + le contenu de démo (catégories, émissions, réglages)
  **au tout premier déploiement** ;
- resynchronise ensuite le compte admin depuis `ADMIN_EMAIL` / `ADMIN_PASSWORD`
  **à chaque déploiement** (pratique pour changer le mot de passe) ;
- ne réécrit **jamais** ce que tu as modifié dans le back-office.

Vérifie après le déploiement :

```
curl https://isdb-radio-api.onrender.com/api/v1/app-config
curl https://isdb-radio-api.onrender.com/api/v1/stream
curl "https://isdb-radio-api.onrender.com/api/v1/episodes?per_page=3"
```

Admin : `https://isdb-radio-api.onrender.com/admin` (identifiants `ADMIN_EMAIL` / `ADMIN_PASSWORD`).

> Changer le mot de passe admin plus tard : modifie `ADMIN_PASSWORD` dans
> l'onglet Environment de Render et redéploie.

## 5. Reconstruire l'APK avec la nouvelle URL

Dans `ISDBradio/` :

```bash
flutter build apk --release --dart-define=API_BASE_URL=https://isdb-radio-api.onrender.com/api/v1
```

`build/app/outputs/flutter-apk/app-release.apk` → à partager aux testeurs.
L'URL est figée dans l'APK : si l'URL Render change, il faut reconstruire.

---

## Limites du plan gratuit (à connaître)

| Sujet | Réalité free tier | Solution |
|---|---|---|
| **Mise en veille** | le service s'endort après 15 min sans trafic, ~50 s de réveil | plan **Starter** (~7 $/mois) = toujours actif |
| **PostgreSQL** | supprimé **30 jours** après création | plan Postgres payant, **ou** base gratuite permanente [Neon](https://neon.tech) / [Supabase](https://supabase.com) → colle son URL dans `DB_URL` |
| **Fichiers uploadés** (jaquettes, audio) | le disque est **effacé à chaque déploiement** | voir ci-dessous |

### Uploads persistants

**Option A — disque Render** (nécessite un plan payant) : décommente le bloc
`disk:` dans `render.yaml` (monté sur `storage/app/public`).

**Option B — stockage objet S3 (recommandé)** : [Cloudflare R2](https://developers.cloudflare.com/r2/)
offre 10 Go gratuits. Ajoute ces variables et passe `FILESYSTEM_DISK=s3` :

```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=…
AWS_SECRET_ACCESS_KEY=…
AWS_DEFAULT_REGION=auto
AWS_BUCKET=isdb-radio
AWS_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_URL=https://<ton-domaine-public-r2>
```

## Notes

- `APP_DEBUG=false` en production (déjà dans `render.yaml`). Ne jamais l'activer publiquement.
- Les logs partent sur `stderr` → visibles dans l'onglet **Logs** de Render.
- Pas de worker de file d'attente nécessaire : le calcul de durée audio se fait
  en `afterResponse()`. Si tu ajoutes des jobs, crée un service Render de type
  *Background Worker* avec `php artisan queue:work`.
- Rate limiting API : 60 req/min/IP (`routes/api.php`).
- Upload audio d'une émission : **200 Mo maximum** (`docker/php.ini`). Au-delà,
  héberge le fichier ailleurs et utilise le champ « URL audio externe ». Sur le
  plan gratuit (512 Mo RAM) éviter les très gros fichiers.
- Changer le mot de passe admin : modifie `ADMIN_PASSWORD` dans l'onglet
  Environment puis redéploie (le seed resynchronise le compte à chaque boot).
- Le Shell Render n'est pas disponible sur le plan gratuit — c'est pourquoi
  migrations et seed tournent automatiquement au démarrage du conteneur.
