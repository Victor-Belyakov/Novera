<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260529220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create finance categories and finances tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE finance_categories (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2B76FF302B36786B ON finance_categories (title)');
        $this->addSql('CREATE TABLE finances (id SERIAL NOT NULL, category_id INT DEFAULT NULL, created_by_id INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, amount NUMERIC(12, 2) NOT NULL, type VARCHAR(32) NOT NULL, operation_date DATE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2FE4A00812469DE2 ON finances (category_id)');
        $this->addSql('CREATE INDEX IDX_2FE4A008B03A8386 ON finances (created_by_id)');
        $this->addSql('COMMENT ON COLUMN finances.type IS \'(DC2Type:App\\Finance\\Domain\\Enum\\FinanceTypeEnum)\'');
        $this->addSql('ALTER TABLE finances ADD CONSTRAINT FK_2FE4A00812469DE2 FOREIGN KEY (category_id) REFERENCES finance_categories (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE finances ADD CONSTRAINT FK_2FE4A008B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE finances DROP CONSTRAINT FK_2FE4A00812469DE2');
        $this->addSql('ALTER TABLE finances DROP CONSTRAINT FK_2FE4A008B03A8386');
        $this->addSql('DROP TABLE finances');
        $this->addSql('DROP TABLE finance_categories');
    }
}
