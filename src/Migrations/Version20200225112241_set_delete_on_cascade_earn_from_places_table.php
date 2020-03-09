<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200225112241_set_delete_on_cascade_earn_from_places_table extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE earns DROP CONSTRAINT FK_716D45D7DA6A219');
        $this->addSql('ALTER TABLE earns ADD CONSTRAINT FK_716D45D7DA6A219 FOREIGN KEY (place_id) REFERENCES places (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE earns DROP CONSTRAINT FK_716D45D7DA6A219');
        $this->addSql('ALTER TABLE earns ADD CONSTRAINT FK_716D45D7DA6A219 FOREIGN KEY (place_id) REFERENCES places (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
