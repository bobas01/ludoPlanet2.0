<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260215210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add slug column to games table if missing';
    }

    public function up(Schema $schema): void
    {
        $hasSlug = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'games' AND COLUMN_NAME = 'slug'"
        ) > 0;

        if ($hasSlug) {
            return;
        }

        $this->addSql('ALTER TABLE games ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE games SET slug = CONCAT(\'game-\', bgg_id) WHERE slug IS NULL');
        $this->addSql('ALTER TABLE games MODIFY slug VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX games_slug_unique ON games (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX games_slug_unique ON games');
        $this->addSql('ALTER TABLE games DROP slug');
    }
}
