<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260215220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make first_name, last_name, address, phone_number, birth_date nullable on users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users MODIFY first_name VARCHAR(100) DEFAULT NULL, MODIFY last_name VARCHAR(100) DEFAULT NULL, MODIFY address VARCHAR(255) DEFAULT NULL, MODIFY phone_number VARCHAR(30) DEFAULT NULL, MODIFY birth_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users MODIFY first_name VARCHAR(100) NOT NULL, MODIFY last_name VARCHAR(100) NOT NULL, MODIFY address VARCHAR(255) NOT NULL, MODIFY phone_number VARCHAR(30) NOT NULL, MODIFY birth_date DATE NOT NULL');
    }
}
