<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260612191814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_related_products (product_source INT NOT NULL, product_target INT NOT NULL, PRIMARY KEY (product_source, product_target))');
        $this->addSql('CREATE INDEX IDX_9BB5700B3DF63ED7 ON product_related_products (product_source)');
        $this->addSql('CREATE INDEX IDX_9BB5700B24136E58 ON product_related_products (product_target)');
        $this->addSql('CREATE TABLE product_coffret_items (coffret_id INT NOT NULL, item_id INT NOT NULL, PRIMARY KEY (coffret_id, item_id))');
        $this->addSql('CREATE INDEX IDX_DC632213CE07368D ON product_coffret_items (coffret_id)');
        $this->addSql('CREATE INDEX IDX_DC632213126F525E ON product_coffret_items (item_id)');
        $this->addSql('ALTER TABLE product_related_products ADD CONSTRAINT FK_9BB5700B3DF63ED7 FOREIGN KEY (product_source) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_related_products ADD CONSTRAINT FK_9BB5700B24136E58 FOREIGN KEY (product_target) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_coffret_items ADD CONSTRAINT FK_DC632213CE07368D FOREIGN KEY (coffret_id) REFERENCES product (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_coffret_items ADD CONSTRAINT FK_DC632213126F525E FOREIGN KEY (item_id) REFERENCES product (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_related_products DROP CONSTRAINT FK_9BB5700B3DF63ED7');
        $this->addSql('ALTER TABLE product_related_products DROP CONSTRAINT FK_9BB5700B24136E58');
        $this->addSql('ALTER TABLE product_coffret_items DROP CONSTRAINT FK_DC632213CE07368D');
        $this->addSql('ALTER TABLE product_coffret_items DROP CONSTRAINT FK_DC632213126F525E');
        $this->addSql('DROP TABLE product_related_products');
        $this->addSql('DROP TABLE product_coffret_items');
    }
}
