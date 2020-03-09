<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191230011936_fix_indexes_api_tokens_places_users_table extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX description_idx');
        $this->addSql('ALTER INDEX status_type_idx RENAME TO places_status_type_idx');
        $this->addSql('ALTER INDEX user_status_idx RENAME TO places_user_status_idx');
        $this->addSql('ALTER INDEX users_expired_at_idx RENAME TO apitokens_users_expired_at_idx');
        $this->addSql('ALTER INDEX users_idx RENAME TO apitokens_users_idx');
        $this->addSql('ALTER INDEX email_status_type_idx RENAME TO users_email_status_type_idx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER INDEX apitokens_users_idx RENAME TO users_idx');
        $this->addSql('ALTER INDEX apitokens_users_expired_at_idx RENAME TO users_expired_at_idx');
        $this->addSql('ALTER INDEX users_email_status_type_idx RENAME TO email_status_type_idx');
        $this->addSql('CREATE INDEX description_idx ON places (description)');
        $this->addSql('ALTER INDEX places_status_type_idx RENAME TO status_type_idx');
        $this->addSql('ALTER INDEX places_user_status_idx RENAME TO user_status_idx');
    }
}
