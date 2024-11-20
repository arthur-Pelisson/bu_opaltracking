<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210507085019 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etdline ADD CONSTRAINT FK_48B3498CC8ED0533 FOREIGN KEY (etd_id) REFERENCES etd (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE etdline ADD CONSTRAINT FK_48B3498CBAFEB5B6 FOREIGN KEY (parent_etdline_id) REFERENCES etdline (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE etdline_history DROP FOREIGN KEY FK_7228435AE838358A');
        $this->addSql('ALTER TABLE etdline_history ADD CONSTRAINT FK_7228435AE838358A FOREIGN KEY (etd_line_id) REFERENCES etdline (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE etdline_tag DROP FOREIGN KEY FK_21C9256CFDBD31E6');
        $this->addSql('ALTER TABLE etdline_tag ADD CONSTRAINT FK_21C9256CFDBD31E6 FOREIGN KEY (etdline_id) REFERENCES etdline (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE etdline_tag_history DROP FOREIGN KEY FK_83757B3170173524');
        $this->addSql('ALTER TABLE etdline_tag_history ADD CONSTRAINT FK_83757B3170173524 FOREIGN KEY (etd_line_tag_id) REFERENCES etdline_tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etdline DROP FOREIGN KEY FK_48B3498CC8ED0533');
        $this->addSql('ALTER TABLE etdline DROP FOREIGN KEY FK_48B3498CBAFEB5B6');
        $this->addSql('ALTER TABLE etdline_history DROP FOREIGN KEY FK_7228435AE838358A');
        $this->addSql('ALTER TABLE etdline_history ADD CONSTRAINT FK_7228435AE838358A FOREIGN KEY (etd_line_id) REFERENCES etdline (id)');
        $this->addSql('ALTER TABLE etdline_tag DROP FOREIGN KEY FK_21C9256CFDBD31E6');
        $this->addSql('ALTER TABLE etdline_tag ADD CONSTRAINT FK_21C9256CFDBD31E6 FOREIGN KEY (etdline_id) REFERENCES etdline (id)');
        $this->addSql('ALTER TABLE etdline_tag_history DROP FOREIGN KEY FK_83757B3170173524');
        $this->addSql('ALTER TABLE etdline_tag_history ADD CONSTRAINT FK_83757B3170173524 FOREIGN KEY (etd_line_tag_id) REFERENCES etdline_tag (id)');
    }
}
