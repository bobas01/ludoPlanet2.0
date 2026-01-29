<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260129103133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart_items (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, cart_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_BEF484451AD5CDBF (cart_id), INDEX IDX_BEF48445E48FD905 (game_id), UNIQUE INDEX uq_cart_game (cart_id, game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE carts (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_4E004AACA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_items (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, unit_price_cents INT NOT NULL, created_at DATETIME NOT NULL, order_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_62809DB08D9F6D38 (order_id), INDEX IDX_62809DB0E48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE orders (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(50) NOT NULL, total_cents INT NOT NULL, currency VARCHAR(3) NOT NULL, shipping_full_name VARCHAR(255) DEFAULT NULL, shipping_address_line1 VARCHAR(255) DEFAULT NULL, shipping_address_line2 VARCHAR(255) DEFAULT NULL, shipping_postal_code VARCHAR(20) DEFAULT NULL, shipping_city VARCHAR(100) DEFAULT NULL, shipping_country VARCHAR(2) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_E52FFDEEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, roles JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT FK_BEF484451AD5CDBF FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT FK_BEF48445E48FD905 FOREIGN KEY (game_id) REFERENCES games (bgg_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carts ADD CONSTRAINT FK_4E004AACA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB08D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB0E48FD905 FOREIGN KEY (game_id) REFERENCES games (bgg_id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF484451AD5CDBF');
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF48445E48FD905');
        $this->addSql('ALTER TABLE carts DROP FOREIGN KEY FK_4E004AACA76ED395');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB08D9F6D38');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB0E48FD905');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEA76ED395');
        $this->addSql('DROP TABLE cart_items');
        $this->addSql('DROP TABLE carts');
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE users');
    }
}
