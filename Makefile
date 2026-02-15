# LudoPlanet — commandes Docker Compose
# Usage : depuis la racine du projet

.PHONY: migration migration-generate import import-full prune enrich up down logs

# Exécuter les migrations (php bin/console doctrine:migrations:migrate)
migration:
	docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction

# Créer une nouvelle migration (doctrine:migrations:generate — pas de MakerBundle en Docker)
migration-generate:
	docker compose exec backend php bin/console doctrine:migrations:generate --no-interaction

# Réimporter les jeux depuis le CSV BGG (20 par catégorie). À faire après « make migration ».
import:
	docker compose exec backend php scripts/import_bgg.php /data/archive/bgg_dataset.csv --limit-per-category=20

# Tout le CSV (sans limite) — déconseillé si beaucoup de lignes
import-full:
	docker compose exec backend php scripts/import_bgg.php /data/archive/bgg_dataset.csv

# Garder seulement 20 jeux par catégorie (supprime le reste). À faire après avoir la colonne slug.
prune:
	docker compose exec backend php scripts/prune_games_by_category.php --limit=20

# Enrichir les jeux : descriptions, prix, image par catégorie (enfants, ambiance, plateau, cartes, expert).
enrich:
	docker compose exec backend php scripts/enrich_bgg_data.php --limit=500

up:
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f
