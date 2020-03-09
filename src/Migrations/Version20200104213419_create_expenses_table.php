<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200104213419_create_expenses_table extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE expenses (id UUID DEFAULT uuid_generate_v4(), credit_card_id UUID DEFAULT NULL, registered_by_id UUID NOT NULL, paid_by_id UUID DEFAULT NULL, place_id UUID NOT NULL, description VARCHAR(255) NOT NULL, due_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, paid_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, total_installments SMALLINT DEFAULT 1 NOT NULL, installment_number SMALLINT DEFAULT 1 NOT NULL, status VARCHAR(20) DEFAULT \'pending\' NOT NULL, payment_type VARCHAR(30) NOT NULL, value NUMERIC(15, 2) DEFAULT NULL, amount_paid NUMERIC(15, 2) DEFAULT NULL, token_group VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2496F35B27E92E18 ON expenses (registered_by_id)');
        $this->addSql('CREATE INDEX IDX_2496F35B7F9BC654 ON expenses (paid_by_id)');
        $this->addSql('CREATE INDEX IDX_2496F35BDA6A219 ON expenses (place_id)');
        $this->addSql('CREATE INDEX IDX_2496F35B7048FD0F ON expenses (credit_card_id)');
        $this->addSql('CREATE INDEX expenses_status_type_idx ON expenses (status)');
        $this->addSql('CREATE INDEX expenses_status_paid_at_type_idx ON expenses (status, paid_at)');
        $this->addSql('CREATE INDEX expenses_value_status_type_idx ON expenses (value, status)');
        $this->addSql('CREATE INDEX expenses_payment_type_idx ON expenses (payment_type)');
        $this->addSql('CREATE INDEX expenses_payment_type_paid_at_idx ON expenses (payment_type, paid_at)');
        $this->addSql('CREATE INDEX expenses_due_date_idx ON expenses (due_date)');
        $this->addSql('CREATE INDEX expenses_created_at_idx ON expenses (created_at)');
        $this->addSql('CREATE INDEX expenses_status_payment_type_idx ON expenses (status, payment_type)');
        $this->addSql('CREATE INDEX expenses_registered_by_description_idx ON expenses (registered_by_id, description)');
        $this->addSql('CREATE INDEX expenses_paid_by_description_idx ON expenses (paid_by_id, description)');
        $this->addSql('CREATE INDEX expenses_registered_by_status_idx ON expenses (registered_by_id, status)');
        $this->addSql('COMMENT ON COLUMN expenses.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN expenses.registered_by_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN expenses.credit_card_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN expenses.paid_by_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN expenses.place_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE expenses ADD CONSTRAINT FK_2496F35B7048FD0F FOREIGN KEY (credit_card_id) REFERENCES credit_cards (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expenses ADD CONSTRAINT FK_2496F35B27E92E18 FOREIGN KEY (registered_by_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expenses ADD CONSTRAINT FK_2496F35B7F9BC654 FOREIGN KEY (paid_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expenses ADD CONSTRAINT FK_2496F35BDA6A219 FOREIGN KEY (place_id) REFERENCES places (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE expenses');
    }
}
