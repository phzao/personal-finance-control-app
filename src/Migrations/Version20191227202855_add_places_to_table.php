<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191227202855_add_places_to_table extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE places (id UUID DEFAULT uuid_generate_v4(), user_id UUID NOT NULL, description VARCHAR(255) DEFAULT \'home\' NOT NULL, status VARCHAR(20) DEFAULT \'enable\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, UNIQUE (user_id, description), PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FEAF6C55A76ED395 ON places (user_id)');
        $this->addSql('CREATE INDEX status_type_idx ON places (status)');
        $this->addSql('CREATE INDEX user_description_idx ON places (user_id, description)');
        $this->addSql('CREATE INDEX user_status_idx ON places (user_id, status)');
        $this->addSql('COMMENT ON COLUMN places.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN places.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE places ADD CONSTRAINT FK_FEAF6C55A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX idx_2cad560edb38439e RENAME TO IDX_2CAD560EA76ED395');
        $this->addSql('CREATE INDEX email_status_type_idx ON users (email, status)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE places');
        $this->addSql('ALTER INDEX idx_2cad560ea76ed395 RENAME TO idx_2cad560edb38439e');
        $this->addSql('DROP INDEX email_status_type_idx');
    }
}
