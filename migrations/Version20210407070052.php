<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210407070052 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation_message ADD count_validate_ship_by_changed INT DEFAULT NULL, ADD count_validate_etddate_changed INT DEFAULT NULL, ADD count_validate_qty_changed INT DEFAULT NULL, ADD count_approved_changed INT DEFAULT NULL, ADD count_rejected_changed INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation_message DROP count_validate_ship_by_changed, DROP count_validate_etddate_changed, DROP count_validate_qty_changed, DROP count_approved_changed, DROP count_rejected_changed');
    }
}
