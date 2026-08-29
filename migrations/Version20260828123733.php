<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260828123733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(50) NOT NULL, rue VARCHAR(255) NOT NULL, complement VARCHAR(255) DEFAULT NULL, code_postal VARCHAR(10) NOT NULL, ville VARCHAR(100) NOT NULL, pays VARCHAR(100) NOT NULL, is_default TINYINT NOT NULL, client_id INT NOT NULL, INDEX IDX_D4E6F8119EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE before_after (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, image_avant VARCHAR(255) DEFAULT NULL, image_apres VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, is_active TINYINT NOT NULL, position INT NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE blog_post (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, contenu LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, categorie VARCHAR(30) DEFAULT NULL, is_published TINYINT NOT NULL, published_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, auteur_id INT DEFAULT NULL, INDEX IDX_BA5AE01D60BB6FE6 (auteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, slug VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, parent_id INT DEFAULT NULL, INDEX IDX_64C19C1727ACA70 (parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE coffret (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, prix NUMERIC(10, 2) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE coffret_products (coffret_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_5DAE3E25CE07368D (coffret_id), INDEX IDX_5DAE3E254584665A (product_id), PRIMARY KEY (coffret_id, product_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contact_message (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, sujet VARCHAR(150) NOT NULL, message LONGTEXT NOT NULL, is_traite TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, numero_commande VARCHAR(50) NOT NULL, montant_total NUMERIC(10, 2) NOT NULL, stripe_payment_intent_id VARCHAR(255) DEFAULT NULL, statut VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, livraison_nom VARCHAR(100) NOT NULL, livraison_rue VARCHAR(255) NOT NULL, livraison_complement VARCHAR(255) DEFAULT NULL, livraison_code_postal VARCHAR(10) NOT NULL, livraison_ville VARCHAR(100) NOT NULL, livraison_pays VARCHAR(100) NOT NULL, livraison_telephone VARCHAR(20) NOT NULL, livraison_email VARCHAR(180) NOT NULL, delivery_method VARCHAR(30) NOT NULL, shipping_cost NUMERIC(10, 2) NOT NULL, client_id INT NOT NULL, INDEX IDX_F529939819EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, nom_produit VARCHAR(150) NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, quantite INT NOT NULL, commande_id INT NOT NULL, product_id INT DEFAULT NULL, INDEX IDX_52EA1F0982EA2E54 (commande_id), INDEX IDX_52EA1F094584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_status_history (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(30) NOT NULL, date DATETIME NOT NULL, commentaire LONGTEXT DEFAULT NULL, commande_id INT NOT NULL, changed_by_id INT DEFAULT NULL, INDEX IDX_471AD77E82EA2E54 (commande_id), INDEX IDX_471AD77E828AD0A0 (changed_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, conseils_utilisation LONGTEXT DEFAULT NULL, prix NUMERIC(10, 2) NOT NULL, prix_promo NUMERIC(10, 2) DEFAULT NULL, date_debut_promo DATETIME DEFAULT NULL, date_fin_promo DATETIME DEFAULT NULL, stock_quantity INT DEFAULT NULL, is_rupture TINYINT NOT NULL, is_nouveaute TINYINT NOT NULL, is_coffret TINYINT NOT NULL, is_mise_en_avant TINYINT NOT NULL, is_promo TINYINT NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, category_id INT NOT NULL, INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_image (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, position INT NOT NULL, is_main TINYINT NOT NULL, updated_at DATETIME DEFAULT NULL, product_id INT NOT NULL, INDEX IDX_64617F034584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, auteur VARCHAR(100) NOT NULL, note INT NOT NULL, commentaire LONGTEXT DEFAULT NULL, is_visible TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, contenu LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, meta_description VARCHAR(255) DEFAULT NULL, prix NUMERIC(10, 2) DEFAULT NULL, duree INT DEFAULT NULL, categorie VARCHAR(30) NOT NULL, is_published TINYINT NOT NULL, position INT NOT NULL, lien_fresha VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE site_setting (id INT AUTO_INCREMENT NOT NULL, cle VARCHAR(100) NOT NULL, valeur LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_SITE_SETTING_CLE (cle), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F8119EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE blog_post ADD CONSTRAINT FK_BA5AE01D60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE coffret_products ADD CONSTRAINT FK_5DAE3E25CE07368D FOREIGN KEY (coffret_id) REFERENCES coffret (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE coffret_products ADD CONSTRAINT FK_5DAE3E254584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939819EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F0982EA2E54 FOREIGN KEY (commande_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE order_status_history ADD CONSTRAINT FK_471AD77E82EA2E54 FOREIGN KEY (commande_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_status_history ADD CONSTRAINT FK_471AD77E828AD0A0 FOREIGN KEY (changed_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE product_image ADD CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F8119EB6921');
        $this->addSql('ALTER TABLE blog_post DROP FOREIGN KEY FK_BA5AE01D60BB6FE6');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE coffret_products DROP FOREIGN KEY FK_5DAE3E25CE07368D');
        $this->addSql('ALTER TABLE coffret_products DROP FOREIGN KEY FK_5DAE3E254584665A');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939819EB6921');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F0982EA2E54');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F094584665A');
        $this->addSql('ALTER TABLE order_status_history DROP FOREIGN KEY FK_471AD77E82EA2E54');
        $this->addSql('ALTER TABLE order_status_history DROP FOREIGN KEY FK_471AD77E828AD0A0');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE product_image DROP FOREIGN KEY FK_64617F034584665A');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE before_after');
        $this->addSql('DROP TABLE blog_post');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE coffret');
        $this->addSql('DROP TABLE coffret_products');
        $this->addSql('DROP TABLE contact_message');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('DROP TABLE order_status_history');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_image');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE site_setting');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
