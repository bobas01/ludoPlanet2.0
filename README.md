# LudoPlanet

Application web de catalogue et vente de jeux de société : frontend SvelteKit, API Symfony, authentification JWT, paiement Stripe, espace client et dashboard admin.

---

## Sommaire

- [Stack technique](#-stack-technique)
- [Démarrage avec Docker](#-démarrage-avec-docker)
- [Développement local](#-développement-local)
- [Variables d'environnement](#-variables-denvironnement)
- [Tests](#-tests)
- [Sécurité](#-sécurité)
- [Structure du projet](#-structure-du-projet)
- [Ressources](#-ressources)

---

## Stack technique

| Couche              | Techno                                                                |
| ------------------- | --------------------------------------------------------------------- |
| **Frontend**        | SvelteKit 2, Svelte 5, Tailwind CSS, composants type shadcn (bits-ui) |
| **Backend**         | Symfony 8, PHP 8.4, API REST JSON                                     |
| **Base de données** | MySQL 8 (Doctrine ORM)                                                |
| **Auth**            | JWT (Lexik), cookie httpOnly                                          |
| **Paiement**        | Stripe (Checkout Session)                                             |
| **Outils**          | Docker Compose, PHPUnit, Vitest                                       |

Fonctionnalités principales : catalogue jeux (filtres, détail), panier, checkout Stripe, compte utilisateur (profil, commandes), dashboard admin (jeux, domaines, mécaniques, images catégories, commandes), bannière cookies, rate limiting login/register.

---

## Démarrage avec Docker

### Prérequis

- Docker
- Docker Compose

### Lancer les services

```bash
docker compose up
```

Ou en arrière-plan :

```bash
docker compose up -d
```

### Accès aux services

| Service                  | URL                   |
| ------------------------ | --------------------- |
| **Frontend**             | http://localhost:4173 |
| **Backend**              | http://localhost:8000 |
| **phpMyAdmin**           | http://localhost:8080 |
| **Mailpit** (emails dev) | http://localhost:8025 |

**Base de données** : `localhost:3307` — user `root`, password `root`, base `ludoplanet`.

### Commandes utiles

```bash
# Reconstruire les images
docker compose up --build

# Voir les logs
docker compose logs -f backend

# Arrêter
docker compose down

# Arrêter et supprimer les volumes (⚠️ données MySQL perdues)
docker compose down -v
```

### Premier démarrage backend (migrations, JWT)

Après le premier `docker compose up`, exécuter dans le conteneur backend :

```bash
docker compose exec backend sh
# Dans le conteneur :
php bin/console doctrine:migrations:migrate --no-interaction
# Générer les clés JWT si besoin (voir section Développement local)
exit
```

---

## Développement local

Sans Docker : backend et frontend sur la machine locale, base MySQL soit locale soit via Docker.

### Backend (Symfony)

**Prérequis** : PHP 8.4, Composer, extensions PHP **curl** et **sodium**, MySQL.

```bash
cd backend
cp .env .env.local   # puis éditer .env.local
composer install
```

**Clés JWT** (Lexik) :

```bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

Indiquer le passphrase dans `.env` : `JWT_PASSPHRASE=...`

**Base de données** : renseigner `DATABASE_URL` dans `.env` ou `.env.local`.  
- **Backend lancé en local** (`symfony serve` ou `php -S`) : utiliser `127.0.0.1` et le port **3307** (MySQL exposé par Docker), ex. `mysql://root:root@127.0.0.1:3307/ludoplanet`.  
- **Backend dans Docker** : le host est `db` (nom du service dans le réseau Docker).  
Si tu as l’erreur « getaddrinfo for db failed » ou « Hôte inconnu », c’est que ton `.env` pointe vers `db` alors que tu lances le backend en local — remplace par `127.0.0.1:3307`. Puis :

```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load  # optionnel
```

**Lancer le serveur** (obligatoire : utiliser le routeur pour que `/games`, `/api/*`, etc. passent par Symfony et aient les en-têtes CORS) :

```bash
php -S localhost:8000 -t public public/router.php
```

### Frontend (SvelteKit)

**Prérequis** : Node.js 20+.

```bash
cd frontend
npm install
npm run dev
```

Frontend : http://localhost:5173. L’API est appelée vers `http://localhost:8000` en mode dev (voir `frontend/src/lib/api.ts` et `BASE_URL`).

---

## Variables d'environnement

### Backend (`.env` / `.env.local`)

| Variable                                               | Description                                               |
| ------------------------------------------------------ | --------------------------------------------------------- |
| `DATABASE_URL`                                         | Connexion MySQL                                           |
| `APP_SECRET`                                           | Secret Symfony                                            |
| `JWT_SECRET_KEY` / `JWT_PUBLIC_KEY` / `JWT_PASSPHRASE` | Clés JWT (Lexik)                                          |
| `JWT_TTL`                                              | Durée de vie du token (secondes)                          |
| `STRIPE_SECRET_KEY` / `STRIPE_WEBHOOK_SECRET`          | Stripe                                                    |
| `FRONTEND_URL`                                         | URL du front (redirects Stripe, CORS en prod)             |
| `CORS_ALLOWED_ORIGINS`                                 | Origines CORS en prod (ex. `https://mondomaine.com`)      |
| `MAILER_DSN`                                           | Envoi d’emails (ex. `smtp://...` ou `null://null` en dev) |

### Frontend

En dev, l’API est fixée dans `src/lib/api.ts` (ex. `http://localhost:8000`). En prod, utiliser une variable d’environnement Vite (ex. `VITE_API_URL`) si besoin.

---

## Tests

### Backend (PHPUnit)

```bash
cd backend
php bin/phpunit
```

Ou un fichier précis :

```bash
php bin/phpunit tests/Controller/AdminGameControllerTest.php -c phpunit.dist.xml
```

En environnement test, le rate limiting sur `/api/login` et `/api/register` est désactivé pour ne pas bloquer la suite.

### Frontend (Vitest)

```bash
cd frontend
npm run test
```

---

## Sécurité

- **Backend** : voir `backend/SECURITY.md` (CORS, rate limit, JWT, contrôle d’accès, logs, etc.).
- **Frontend** : JWT en cookie httpOnly, pas de token en localStorage pour l’auth.

---

## Structure du projet

```
ludoPlanet2.0/
├── backend/                 # API Symfony
│   ├── config/
│   ├── migrations/
│   ├── public/
│   ├── src/
│   │   ├── Controller/
│   │   ├── Entity/
│   │   ├── EventSubscriber/
│   │   └── Security/
│   ├── tests/
│   ├── .env
│   ├── composer.json
│   └── Dockerfile
├── frontend/                # SvelteKit
│   ├── src/
│   │   ├── lib/
│   │   │   ├── components/
│   │   │   ├── stores/
│   │   │   └── api.ts
│   │   └── routes/
│   ├── package.json
│   └── Dockerfile
├── database/                # Volume MySQL (créé par Docker)
├── Docker-compose.yml
└── README.md
```

---

## Dépannage

- **Backend : extensions PHP manquantes**  
  Activer `curl` et `sodium` dans le `php.ini` utilisé en CLI (`php --ini`). Sur Windows, décommenter `extension=curl` et `extension=sodium` (ou `php_curl.dll` / `php_sodium.dll`).

- **Composer : contraintes plateforme**  
  En local, si PHP n’a pas les extensions :  
  `composer install --ignore-platform-req=ext-curl --ignore-platform-req=ext-sodium` (à n’utiliser qu’en dev si besoin).

- **CORS / 401 en dev**  
  Vérifier que le front appelle la même origine (ou une autorisée) et que les credentials sont envoyés (`credentials: 'include'`). En prod, définir `CORS_ALLOWED_ORIGINS`.

- **Stripe webhook en local**  
  Utiliser Stripe CLI pour tunnel :  
  `stripe listen --forward-to localhost:8000/api/stripe/webhook`

---

## Ressources

- [Symfony](https://symfony.com/doc/current/index.html)
- [SvelteKit](https://kit.svelte.dev/docs)
- [Docker Compose](https://docs.docker.com/compose/)
- [Stripe](https://stripe.com/docs)
- [Lexik JWT](https://github.com/lexik/LexikJWTAuthenticationBundle)
