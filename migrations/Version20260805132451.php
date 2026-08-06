<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260805132451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review DROP CONSTRAINT fk_794381c64584665a');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT fk_794381c619eb6921');
        $this->addSql('DROP INDEX idx_794381c619eb6921');
        $this->addSql('DROP INDEX idx_794381c64584665a');
        $this->addSql('ALTER TABLE review ADD auteur VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE review ADD is_visible BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE review DROP statut');
        $this->addSql('ALTER TABLE review DROP product_id');
        $this->addSql('ALTER TABLE review DROP client_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review ADD statut VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE review ADD product_id INT NOT NULL');
        $this->addSql('ALTER TABLE review ADD client_id INT NOT NULL');
        $this->addSql('ALTER TABLE review DROP auteur');
        $this->addSql('ALTER TABLE review DROP is_visible');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_794381c64584665a FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_794381c619eb6921 FOREIGN KEY (client_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_794381c619eb6921 ON review (client_id)');
        $this->addSql('CREATE INDEX idx_794381c64584665a ON review (product_id)');
    }
}
