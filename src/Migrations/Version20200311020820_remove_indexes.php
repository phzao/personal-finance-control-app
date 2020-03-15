<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200311020820_remove_indexes extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX categories_user_status_idx');
        $this->addSql('DROP INDEX places_user_status_idx');
        $this->addSql('DROP INDEX credit_cards_user_description');
        $this->addSql('DROP INDEX credit_cards_user_status');
        $this->addSql('DROP INDEX earns_place_type');
        $this->addSql('DROP INDEX earns_place_description_idx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX credit_cards_user_description ON credit_cards (user_id, description)');
        $this->addSql('CREATE INDEX credit_cards_user_status ON credit_cards (user_id, status)');
        $this->addSql('CREATE INDEX earns_place_type ON earns (place_id, type)');
        $this->addSql('CREATE INDEX earns_place_description_idx ON earns (place_id, description)');
        $this->addSql('ALTER INDEX idx_716d45d7da6a219 RENAME TO earns_place_place');
        $this->addSql('CREATE INDEX places_user_status_idx ON places (user_id, status)');
        $this->addSql('CREATE INDEX categories_user_status_idx ON categories (user_id, status)');;
    }
}
