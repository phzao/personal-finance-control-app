<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191230215001_add_earn_and_credit_card_table extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE credit_cards (id UUID DEFAULT uuid_generate_v4(), user_id UUID NOT NULL, last_digits INT NOT NULL, card_banner VARCHAR(20) NOT NULL, due_date SMALLINT DEFAULT 1 NOT NULL, is_default BOOLEAN DEFAULT \'false\' NOT NULL, limit_value NUMERIC(15, 2) DEFAULT NULL, validity DATE DEFAULT NULL, description VARCHAR(50) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(20) DEFAULT \'enable\' NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5CADD653A76ED395 ON credit_cards (user_id)');
        $this->addSql('CREATE INDEX credit_cards_status_type ON credit_cards (status)');
        $this->addSql('CREATE INDEX credit_cards_user_description ON credit_cards (user_id, description)');
        $this->addSql('CREATE INDEX credit_cards_user_status ON credit_cards (user_id, status)');
        $this->addSql('COMMENT ON COLUMN credit_cards.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN credit_cards.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE earns (id UUID DEFAULT uuid_generate_v4(), place_id UUID NOT NULL, description VARCHAR(50) DEFAULT NULL, earn_at DATE NOT NULL, value NUMERIC(15, 2) NOT NULL, confirmed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type VARCHAR(30) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX earns_place_description_idx ON earns (place_id, description)');
        $this->addSql('CREATE INDEX earns_place_type ON earns (place_id, type)');
        $this->addSql('CREATE INDEX earns_place_earn_at ON earns (place_id, earn_at)');
        $this->addSql('CREATE INDEX earns_place_confirmed_at ON earns (place_id, confirmed_at)');
        $this->addSql('CREATE INDEX earns_place_place ON earns (place_id)');
        $this->addSql('COMMENT ON COLUMN earns.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN earns.place_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE credit_cards ADD CONSTRAINT FK_5CADD653A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE earns ADD CONSTRAINT FK_716D45D7DA6A219 FOREIGN KEY (place_id) REFERENCES places (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE credit_cards');
        $this->addSql('DROP TABLE earns');
    }
}
