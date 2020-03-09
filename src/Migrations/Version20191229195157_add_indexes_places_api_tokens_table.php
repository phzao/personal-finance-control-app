<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191229195157_add_indexes_places_api_tokens_table extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE places ALTER id DROP DEFAULT');
        $this->addSql('CREATE INDEX description_idx ON places (description)');
        $this->addSql('ALTER TABLE api_tokens ALTER id DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2CAD560E5F37A13B ON api_tokens (token)');
        $this->addSql('CREATE INDEX users_expired_at_idx ON api_tokens (user_id, expired_at)');
        $this->addSql('ALTER INDEX idx_2cad560ea76ed395 RENAME TO users_idx');
        $this->addSql('ALTER TABLE users ALTER id DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX UNIQ_2CAD560E5F37A13B');
        $this->addSql('DROP INDEX users_expired_at_idx');
        $this->addSql('ALTER TABLE api_tokens ALTER id SET DEFAULT \'uuid_generate_v4()\'');
        $this->addSql('ALTER INDEX users_idx RENAME TO idx_2cad560ea76ed395');
        $this->addSql('ALTER TABLE users ALTER id SET DEFAULT \'uuid_generate_v4()\'');
        $this->addSql('DROP INDEX description_idx');
        $this->addSql('ALTER TABLE places ALTER id SET DEFAULT \'uuid_generate_v4()\'');
    }
}
