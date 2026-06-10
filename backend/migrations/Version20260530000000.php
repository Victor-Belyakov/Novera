<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260530000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create health metric types and entries tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE health_metric_types (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(64) NOT NULL, value_kind VARCHAR(32) NOT NULL, unit VARCHAR(64) DEFAULT NULL, is_active BOOLEAN DEFAULT TRUE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D91C2E8C989D9B62 ON health_metric_types (slug)');
        $this->addSql('CREATE TABLE health_metric_entries (id SERIAL NOT NULL, user_id INT NOT NULL, metric_type_id INT NOT NULL, recorded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, value_number NUMERIC(10, 2) DEFAULT NULL, value_text TEXT DEFAULT NULL, note TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C27C0731A76ED395 ON health_metric_entries (user_id)');
        $this->addSql('CREATE INDEX IDX_C27C0731D3073E92 ON health_metric_entries (metric_type_id)');
        $this->addSql('ALTER TABLE health_metric_entries ADD CONSTRAINT FK_C27C0731A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE health_metric_entries ADD CONSTRAINT FK_C27C0731D3073E92 FOREIGN KEY (metric_type_id) REFERENCES health_metric_types (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE health_metric_entries DROP CONSTRAINT FK_C27C0731A76ED395');
        $this->addSql('ALTER TABLE health_metric_entries DROP CONSTRAINT FK_C27C0731D3073E92');
        $this->addSql('DROP TABLE health_metric_entries');
        $this->addSql('DROP TABLE health_metric_types');
    }
}
