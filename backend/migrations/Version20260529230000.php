<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260529230000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create finance plans table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE finance_plans (id SERIAL NOT NULL, category_id INT DEFAULT NULL, created_by_id INT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(32) NOT NULL, planned_amount NUMERIC(12, 2) NOT NULL, period_start DATE NOT NULL, period_end DATE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5391B35D12469DE2 ON finance_plans (category_id)');
        $this->addSql('CREATE INDEX IDX_5391B35DB03A8386 ON finance_plans (created_by_id)');
        $this->addSql('COMMENT ON COLUMN finance_plans.type IS \'(DC2Type:App\\Finance\\Domain\\Enum\\FinanceTypeEnum)\'');
        $this->addSql('ALTER TABLE finance_plans ADD CONSTRAINT FK_5391B35D12469DE2 FOREIGN KEY (category_id) REFERENCES finance_categories (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE finance_plans ADD CONSTRAINT FK_5391B35DB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE finance_plans DROP CONSTRAINT FK_5391B35D12469DE2');
        $this->addSql('ALTER TABLE finance_plans DROP CONSTRAINT FK_5391B35DB03A8386');
        $this->addSql('DROP TABLE finance_plans');
    }
}
