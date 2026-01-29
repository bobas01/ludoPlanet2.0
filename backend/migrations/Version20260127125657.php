<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260127125657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_3AF346685E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE domains (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8C7BBF9D5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE game_images (id INT AUTO_INCREMENT NOT NULL, image_url VARCHAR(2048) NOT NULL, is_primary TINYINT NOT NULL, game_id INT NOT NULL, INDEX IDX_9D2A13A2E48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE games (bgg_id INT NOT NULL, name VARCHAR(255) NOT NULL, year_published INT DEFAULT NULL, min_players INT DEFAULT NULL, max_players INT DEFAULT NULL, play_time INT DEFAULT NULL, min_age INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, price_cents INT DEFAULT NULL, users_rated INT DEFAULT NULL, rating_average NUMERIC(4, 2) DEFAULT NULL, bgg_rank INT DEFAULT NULL, complexity_average NUMERIC(4, 2) DEFAULT NULL, owned_users INT DEFAULT NULL, PRIMARY KEY (bgg_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE game_mechanics (game_id INT NOT NULL, mechanic_id INT NOT NULL, INDEX IDX_E000F04FE48FD905 (game_id), INDEX IDX_E000F04F9A67DB00 (mechanic_id), PRIMARY KEY (game_id, mechanic_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE game_domains (game_id INT NOT NULL, domain_id INT NOT NULL, INDEX IDX_19B9C0B2E48FD905 (game_id), INDEX IDX_19B9C0B2115F0EE5 (domain_id), PRIMARY KEY (game_id, domain_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE game_categories (game_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_D42F8185E48FD905 (game_id), INDEX IDX_D42F818512469DE2 (category_id), PRIMARY KEY (game_id, category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE mechanics (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_32A6314D5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE game_images ADD CONSTRAINT FK_9D2A13A2E48FD905 FOREIGN KEY (game_id) REFERENCES games (bgg_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_mechanics ADD CONSTRAINT FK_E000F04FE48FD905 FOREIGN KEY (game_id) REFERENCES games (bgg_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_mechanics ADD CONSTRAINT FK_E000F04F9A67DB00 FOREIGN KEY (mechanic_id) REFERENCES mechanics (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_domains ADD CONSTRAINT FK_19B9C0B2E48FD905 FOREIGN KEY (game_id) REFERENCES games (bgg_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_domains ADD CONSTRAINT FK_19B9C0B2115F0EE5 FOREIGN KEY (domain_id) REFERENCES domains (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_categories ADD CONSTRAINT FK_D42F8185E48FD905 FOREIGN KEY (game_id) REFERENCES games (bgg_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_categories ADD CONSTRAINT FK_D42F818512469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_images DROP FOREIGN KEY FK_9D2A13A2E48FD905');
        $this->addSql('ALTER TABLE game_mechanics DROP FOREIGN KEY FK_E000F04FE48FD905');
        $this->addSql('ALTER TABLE game_mechanics DROP FOREIGN KEY FK_E000F04F9A67DB00');
        $this->addSql('ALTER TABLE game_domains DROP FOREIGN KEY FK_19B9C0B2E48FD905');
        $this->addSql('ALTER TABLE game_domains DROP FOREIGN KEY FK_19B9C0B2115F0EE5');
        $this->addSql('ALTER TABLE game_categories DROP FOREIGN KEY FK_D42F8185E48FD905');
        $this->addSql('ALTER TABLE game_categories DROP FOREIGN KEY FK_D42F818512469DE2');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE domains');
        $this->addSql('DROP TABLE game_images');
        $this->addSql('DROP TABLE games');
        $this->addSql('DROP TABLE game_mechanics');
        $this->addSql('DROP TABLE game_domains');
        $this->addSql('DROP TABLE game_categories');
        $this->addSql('DROP TABLE mechanics');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
