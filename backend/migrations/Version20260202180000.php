<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260202180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add stripe_session_id to orders table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE orders ADD stripe_session_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE orders DROP stripe_session_id');
    }
}
