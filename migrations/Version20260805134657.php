<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260805134657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE before_after ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE before_after ALTER image_avant DROP NOT NULL');
        $this->addSql('ALTER TABLE before_after ALTER image_apres DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE before_after DROP updated_at');
        $this->addSql('ALTER TABLE before_after ALTER image_avant SET NOT NULL');
        $this->addSql('ALTER TABLE before_after ALTER image_apres SET NOT NULL');
    }
}
