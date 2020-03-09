<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191228200016_add_indexes_to_place_table extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX user_description_idx');
        $this->addSql('ALTER TABLE places ALTER description DROP NOT NULL');
        $this->addSql('ALTER INDEX places_user_id_description_key RENAME TO UNIQ_FEAF6C55A76ED3956DE44026');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE places ALTER description SET NOT NULL');
        $this->addSql('CREATE INDEX user_description_idx ON places (user_id, description)');
        $this->addSql('ALTER INDEX uniq_feaf6c55a76ed3956de44026 RENAME TO places_user_id_description_key');
    }
}
