<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210324081544 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE configuration (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) NOT NULL, value VARCHAR(128) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, etd_id INT DEFAULT NULL, etd_line_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8A8E26E9C8ED0533 (etd_id), UNIQUE INDEX UNIQ_8A8E26E9E838358A (etd_line_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation_message (id INT AUTO_INCREMENT NOT NULL, conversation_id INT NOT NULL, write_by_user_id INT NOT NULL, content LONGTEXT NOT NULL, date_add DATETIME NOT NULL, INDEX IDX_2DEB3E759AC0396 (conversation_id), INDEX IDX_2DEB3E755C9973C7 (write_by_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etd (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, closed TINYINT(1) NOT NULL, date_add DATETIME NOT NULL, date_update DATETIME NOT NULL, status ENUM(\'WAITING_PURCHASER\', \'WAITING_VENDOR\', \'CLOSED\') NOT NULL COMMENT \'(DC2Type:etdstatustype)\', etd_date DATETIME NOT NULL, total_etdlines_count INT NOT NULL, not_validated_etdlines_count INT NOT NULL, etd_changed_etdlines_count INT NOT NULL, qty_changed_etdlines_count INT NOT NULL, ship_by_changed_etdlines_count INT NOT NULL, INDEX IDX_B0D1C96AF603EE73 (vendor_id), UNIQUE INDEX etd_index (vendor_id, etd_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etd_history (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, etd_id INT NOT NULL, closed TINYINT(1) NOT NULL, status ENUM(\'WAITING_PURCHASER\', \'WAITING_VENDOR\', \'CLOSED\') NOT NULL COMMENT \'(DC2Type:etdstatustype)\', date_add DATETIME NOT NULL, INDEX IDX_82F930839D86650F (user_id_id), INDEX IDX_82F93083C8ED0533 (etd_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etdline (id INT AUTO_INCREMENT NOT NULL, etd_id INT NOT NULL, parent_etdline_id INT DEFAULT NULL, pi_no VARCHAR(20) NOT NULL, item_reference VARCHAR(50) NOT NULL, outstanding_qty NUMERIC(38, 20) NOT NULL, delivery_date DATETIME NOT NULL, etd_date DATETIME NOT NULL, ship_by VARCHAR(10) NOT NULL, outstanding_qty_confirmed NUMERIC(38, 20) NOT NULL, date_add DATETIME NOT NULL, date_update DATETIME NOT NULL, order_type INT NOT NULL, store_code VARCHAR(50) NOT NULL, stock_after NUMERIC(38, 20) DEFAULT NULL, your_reference VARCHAR(35) DEFAULT NULL, status ENUM(\'INITIAL\', \'APPROVED\', \'REJECTED\', \'WAITING_FOR_APPROVAL\') NOT NULL COMMENT \'(DC2Type:etdlinestatustype)\', etd_date_confirmed DATETIME NOT NULL, ship_by_confirmed VARCHAR(10) NOT NULL, navision_doc_no VARCHAR(20) NOT NULL, navision_line_no INT NOT NULL, comments VARCHAR(150) DEFAULT NULL, INDEX IDX_48B3498CC8ED0533 (etd_id), INDEX IDX_48B3498CBAFEB5B6 (parent_etdline_id), UNIQUE INDEX etdline_index (navision_doc_no, navision_line_no), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etdline_history (id INT AUTO_INCREMENT NOT NULL, etd_line_id INT NOT NULL, status ENUM(\'INITIAL\', \'APPROVED\', \'REJECTED\', \'WAITING_FOR_APPROVAL\') NOT NULL COMMENT \'(DC2Type:etdlinestatustype)\', ship_by VARCHAR(10) NOT NULL, etd_date DATETIME NOT NULL, outstanding_qty NUMERIC(38, 20) NOT NULL, date_add DATETIME NOT NULL, exported_to_navision TINYINT(1) NOT NULL, INDEX IDX_7228435AE838358A (etd_line_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etdline_tag (id INT AUTO_INCREMENT NOT NULL, etdline_id INT NOT NULL, completed TINYINT(1) NOT NULL, etd_changed TINYINT(1) NOT NULL, ship_by_changed TINYINT(1) NOT NULL, qty_changed TINYINT(1) NOT NULL, partial TINYINT(1) NOT NULL, closed TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_21C9256CFDBD31E6 (etdline_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etdline_tag_history (id INT AUTO_INCREMENT NOT NULL, etd_line_tag_id INT NOT NULL, completed TINYINT(1) NOT NULL, etd_changed TINYINT(1) NOT NULL, ship_by_changed TINYINT(1) NOT NULL, qty_changed TINYINT(1) NOT NULL, partial TINYINT(1) NOT NULL, closed TINYINT(1) NOT NULL, date_add DATETIME NOT NULL, INDEX IDX_83757B3170173524 (etd_line_tag_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, vendor_id INT DEFAULT NULL, email VARCHAR(80) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, type ENUM(\'VENDOR\', \'PURCHASER\') DEFAULT NULL COMMENT \'(DC2Type:usertype)\', code VARCHAR(50) NOT NULL, enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649F603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor (id INT AUTO_INCREMENT NOT NULL, no VARCHAR(20) NOT NULL, name VARCHAR(50) NOT NULL, address VARCHAR(50) DEFAULT NULL, address2 VARCHAR(50) DEFAULT NULL, city VARCHAR(30) DEFAULT NULL, post_code VARCHAR(20) DEFAULT NULL, contact VARCHAR(50) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, telex VARCHAR(20) DEFAULT NULL, territory_code VARCHAR(10) DEFAULT NULL, currency_code VARCHAR(10) DEFAULT NULL, country_region_code VARCHAR(10) DEFAULT NULL, email VARCHAR(80) NOT NULL, qc VARCHAR(30) DEFAULT NULL, enabled TINYINT(1) NOT NULL, start_etddate DATE DEFAULT NULL, end_etddate DATE DEFAULT NULL, UNIQUE INDEX UNIQ_F52233F667AA281F (no), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9C8ED0533 FOREIGN KEY (etd_id) REFERENCES etd (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9E838358A FOREIGN KEY (etd_line_id) REFERENCES etdline (id)');
        $this->addSql('ALTER TABLE conversation_message ADD CONSTRAINT FK_2DEB3E759AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE conversation_message ADD CONSTRAINT FK_2DEB3E755C9973C7 FOREIGN KEY (write_by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE etd ADD CONSTRAINT FK_B0D1C96AF603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id)');
        $this->addSql('ALTER TABLE etd_history ADD CONSTRAINT FK_82F930839D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE etd_history ADD CONSTRAINT FK_82F93083C8ED0533 FOREIGN KEY (etd_id) REFERENCES etd (id)');
        $this->addSql('ALTER TABLE etdline ADD CONSTRAINT FK_48B3498CC8ED0533 FOREIGN KEY (etd_id) REFERENCES etd (id)');
        $this->addSql('ALTER TABLE etdline ADD CONSTRAINT FK_48B3498CBAFEB5B6 FOREIGN KEY (parent_etdline_id) REFERENCES etdline (id)');
        $this->addSql('ALTER TABLE etdline_history ADD CONSTRAINT FK_7228435AE838358A FOREIGN KEY (etd_line_id) REFERENCES etdline (id)');
        $this->addSql('ALTER TABLE etdline_tag ADD CONSTRAINT FK_21C9256CFDBD31E6 FOREIGN KEY (etdline_id) REFERENCES etdline (id)');
        $this->addSql('ALTER TABLE etdline_tag_history ADD CONSTRAINT FK_83757B3170173524 FOREIGN KEY (etd_line_tag_id) REFERENCES etdline_tag (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E759AC0396');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9C8ED0533');
        $this->addSql('ALTER TABLE etd_history DROP FOREIGN KEY FK_82F93083C8ED0533');
        $this->addSql('ALTER TABLE etdline DROP FOREIGN KEY FK_48B3498CC8ED0533');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9E838358A');
        $this->addSql('ALTER TABLE etdline DROP FOREIGN KEY FK_48B3498CBAFEB5B6');
        $this->addSql('ALTER TABLE etdline_history DROP FOREIGN KEY FK_7228435AE838358A');
        $this->addSql('ALTER TABLE etdline_tag DROP FOREIGN KEY FK_21C9256CFDBD31E6');
        $this->addSql('ALTER TABLE etdline_tag_history DROP FOREIGN KEY FK_83757B3170173524');
        $this->addSql('ALTER TABLE conversation_message DROP FOREIGN KEY FK_2DEB3E755C9973C7');
        $this->addSql('ALTER TABLE etd_history DROP FOREIGN KEY FK_82F930839D86650F');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE etd DROP FOREIGN KEY FK_B0D1C96AF603EE73');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F603EE73');
        $this->addSql('DROP TABLE configuration');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE conversation_message');
        $this->addSql('DROP TABLE etd');
        $this->addSql('DROP TABLE etd_history');
        $this->addSql('DROP TABLE etdline');
        $this->addSql('DROP TABLE etdline_history');
        $this->addSql('DROP TABLE etdline_tag');
        $this->addSql('DROP TABLE etdline_tag_history');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE vendor');
    }
}
