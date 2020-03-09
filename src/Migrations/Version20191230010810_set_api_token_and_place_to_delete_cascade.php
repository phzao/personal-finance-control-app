<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191230010810_set_api_token_and_place_to_delete_cascade extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE places DROP CONSTRAINT FK_FEAF6C55A76ED395');
        $this->addSql('ALTER TABLE places ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE places ADD CONSTRAINT FK_FEAF6C55A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE api_tokens DROP CONSTRAINT fk_2cad560edb38439e');
        $this->addSql('ALTER TABLE api_tokens ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE api_tokens ADD CONSTRAINT FK_2CAD560EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ALTER id DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE api_tokens DROP CONSTRAINT FK_2CAD560EA76ED395');
        $this->addSql('ALTER TABLE api_tokens ALTER id SET DEFAULT \'uuid_generate_v4()\'');
        $this->addSql('ALTER TABLE api_tokens ADD CONSTRAINT fk_2cad560edb38439e FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ALTER id SET DEFAULT \'uuid_generate_v4()\'');
        $this->addSql('ALTER TABLE places DROP CONSTRAINT fk_feaf6c55a76ed395');
        $this->addSql('ALTER TABLE places ALTER id SET DEFAULT \'uuid_generate_v4()\'');
        $this->addSql('ALTER TABLE places ADD CONSTRAINT fk_feaf6c55a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
