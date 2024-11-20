<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210331130709 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etdline ADD original_line_outstanding_qty NUMERIC(38, 20) DEFAULT NULL, ADD exported_to_navision TINYINT(1) NOT NULL, ADD read_only TINYINT(1) NOT NULL, CHANGE stock_after available_qty NUMERIC(38, 20) DEFAULT NULL');
        $this->addSql('ALTER TABLE etdline_history DROP exported_to_navision');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etdline ADD stock_after NUMERIC(38, 20) DEFAULT NULL, DROP available_qty, DROP original_line_outstanding_qty, DROP exported_to_navision, DROP read_only');
        $this->addSql('ALTER TABLE etdline_history ADD exported_to_navision TINYINT(1) NOT NULL');
    }
}
