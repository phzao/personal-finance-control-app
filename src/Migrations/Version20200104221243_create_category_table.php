<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200104221243_create_category_table extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE categories (id UUID DEFAULT uuid_generate_v4(), user_id UUID NOT NULL, description VARCHAR(255) NOT NULL, status VARCHAR(20) DEFAULT \'enable\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3AF34668A76ED395 ON categories (user_id)');
        $this->addSql('CREATE INDEX categories_status_type_idx ON categories (status)');
        $this->addSql('CREATE INDEX categories_user_status_idx ON categories (user_id, status)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3AF34668A76ED3956DE44026 ON categories (user_id, description)');
        $this->addSql('COMMENT ON COLUMN categories.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN categories.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expenses ADD category_id UUID NOT NULL');
        $this->addSql('COMMENT ON COLUMN expenses.category_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE expenses ADD CONSTRAINT FK_2496F35B12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2496F35B12469DE2 ON expenses (category_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE expenses DROP CONSTRAINT FK_2496F35B12469DE2');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP INDEX IDX_2496F35B12469DE2');
        $this->addSql('ALTER TABLE expenses DROP category_id');
    }
}
