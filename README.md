# LudoPlanet

Application web complète avec architecture microservices conteneurisée avec Docker.

## 🏗️ Architecture

Le projet est composé de trois services principaux :

- **Database** : MySQL 8.0
- **Backend** : Symfony (PHP 8.4 FPM)
- **Frontend** : SvelteKit avec Vite

## 🐳 Conteneurisation Docker

### Prérequis

- Docker
- Docker Compose

### Structure du projet

```
ludoplanet/
├── Docker-compose.yml      # Configuration Docker Compose
├── database/               # Volume de données MySQL
├── backend/                # Application Symfony
│   └── Dockerfile
└── frontend/               # Application SvelteKit
    └── Dockerfile
```

### Services

#### Database (MySQL)

- **Image** : `mysql:8.0`
- **Port** : `3307:3307`
- **Volume** : `./database:/var/lib/mysql` (persistance des données)
- **Variables d'environnement** :
  - `MYSQL_ROOT_PASSWORD`: root
  - `MYSQL_DATABASE`: ludoplanet

#### Backend (Symfony)

- **Image** : `php:8.4-fpm-alpine`
- **Port** : `9000:9000`
- **Volume** : `./backend:/app` (montage du code source)
- **Dépendances** : Composer installé dans l'image
- **Dockerfile** : Installation de Composer et des dépendances PHP

#### Frontend (SvelteKit)

- **Image** : `node:20-alpine`
- **Port** : `4173:4173`
- **Build** : Construction de l'application avec `npm run build`
- **Preview** : Serveur de prévisualisation avec `npm run preview --host`
- **Dockerfile** : Installation des dépendances Node.js et build de production

## 🚀 Démarrage rapide

### Lancer tous les services

```bash
docker compose up
```

### Lancer en arrière-plan

```bash
docker compose up -d
```

### Reconstruire les images

```bash
# Reconstruire tous les services
docker compose up --build

# Reconstruire un service spécifique
docker compose up --build frontend
```

### Arrêter les services

```bash
# Arrêter les conteneurs
docker compose down

# Arrêter et supprimer les volumes (⚠️ supprime les données de la base)
docker compose down -v
```

## 📝 Commandes utiles

### Voir les logs

```bash
# Tous les services
docker compose logs

# Un service spécifique
docker compose logs frontend
docker compose logs backend
docker compose logs database

# Logs en temps réel
docker compose logs -f frontend
```

### Vérifier l'état des conteneurs

```bash
docker compose ps
```

### Redémarrer un service

```bash
docker compose restart frontend
docker compose restart backend
docker compose restart database
```

### Accéder au shell d'un conteneur

```bash
# Frontend
docker compose exec frontend sh

# Backend
docker compose exec backend sh

# Database
docker compose exec database bash
```

### Reconstruire complètement

Pour forcer une reconstruction complète sans cache :

```bash
docker compose down
docker compose build --no-cache
docker compose up
```

## 🌐 Accès aux services

Une fois les conteneurs démarrés :

- **Frontend** : http://localhost:4173
- **Backend** : http://localhost:9000
- **Database** : localhost:3307
  - Utilisateur : `root`
  - Mot de passe : `root`
  - Base de données : `ludoplanet`

## 🔧 Configuration

### Variables d'environnement

Les variables d'environnement sont configurées dans le `Docker-compose.yml` pour la base de données. Pour le backend et le frontend, utilisez les fichiers `.env` dans leurs dossiers respectifs.

**Note** : Les fichiers `.env` ne sont pas accessibles depuis l'extérieur pour des raisons de sécurité. Toute modification doit être effectuée manuellement.

### Ports

Si vous devez modifier les ports, éditez le fichier `Docker-compose.yml` :

```yaml
ports:
  - "VOTRE_PORT:PORT_CONTENEUR"
```

## 🐛 Dépannage

### Le frontend ne répond pas

1. Vérifiez que le conteneur est démarré : `docker compose ps`
2. Vérifiez les logs : `docker compose logs frontend`
3. Assurez-vous qu'aucun processus local n'utilise le port 4173
4. Le serveur preview doit utiliser `--host` pour être accessible depuis l'extérieur

### Le backend ne répond pas

1. Vérifiez les logs : `docker compose logs backend`
2. Vérifiez que la base de données est démarrée : `docker compose ps database`
3. Vérifiez la configuration de connexion à la base de données dans les fichiers `.env` du backend

### Problèmes de base de données

1. **Erreur de connexion** : Vérifiez que le conteneur database est démarré
2. **Données perdues** : Les données sont persistées dans `./database/`. Si le dossier est supprimé, les données seront perdues
3. **Réinitialiser la base** : 
   ```bash
   docker compose down -v
   docker compose up -d database
   ```

### Erreurs de build

Si le build échoue :

1. Vérifiez les logs de build : `docker compose logs [service]`
2. Vérifiez que toutes les dépendances sont correctement définies
3. Reconstruisez sans cache : `docker compose build --no-cache [service]`

### Problèmes de volumes

- **Frontend** : Les `node_modules` ne sont pas montés depuis Windows pour éviter les conflits de plateforme
- **Backend** : Le code source est monté pour permettre le développement avec hot-reload
- **Database** : Le volume `./database` persiste les données MySQL

## 📦 Structure des Dockerfiles

### Backend Dockerfile

```dockerfile
FROM php:8.4-fpm-alpine
# Installation de Composer
# Installation des dépendances PHP
# Exposition du port 9000
```

### Frontend Dockerfile

```dockerfile
FROM node:20-alpine
# Installation des dépendances Node.js
# Build de l'application
# Serveur preview sur le port 4173
```

## 🔐 Sécurité

- Les mots de passe par défaut sont définis dans `Docker-compose.yml` (à modifier en production)
- Les fichiers `.env` ne doivent pas être commités dans le dépôt Git
- En production, utilisez des secrets Docker ou un gestionnaire de secrets

## 📚 Ressources

- [Documentation Docker](https://docs.docker.com/)
- [Documentation Docker Compose](https://docs.docker.com/compose/)
- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Documentation SvelteKit](https://kit.svelte.dev/docs)

## 🚧 Développement

### Mode développement vs Production

- **Développement** : Utilisez `npm run dev` en local pour le frontend avec hot-reload
- **Production** : Le Dockerfile build l'application et la sert en mode preview

### Modifications du code

- **Backend** : Les modifications sont reflétées immédiatement grâce au volume monté
- **Frontend** : Les modifications nécessitent un rebuild de l'image Docker