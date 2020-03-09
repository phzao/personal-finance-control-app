<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200209223704_add_new_indexes_after_add_credit_card_on_expenses_table extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE INDEX expenses_credit_card_id_due_date ON expenses (credit_card_id, due_date)');
        $this->addSql('CREATE INDEX expenses_credit_card_id_due_date_status ON expenses (credit_card_id, due_date, status)');
        $this->addSql('CREATE INDEX expenses_credit_card_id_value ON expenses (credit_card_id, value)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX expenses_credit_card_id_due_date');
        $this->addSql('DROP INDEX expenses_credit_card_id_due_date_status');
        $this->addSql('DROP INDEX expenses_credit_card_id_value');
    }
}
