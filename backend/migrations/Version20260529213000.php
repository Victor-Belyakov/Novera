<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260529213000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop obsolete statistics table in favor of aggregated statistics service';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE statistics DROP CONSTRAINT IF EXISTS FK_60B32CB4A0CA4A5D');
        $this->addSql('ALTER TABLE statistics DROP CONSTRAINT IF EXISTS FK_60B32CB4A76ED395');
        $this->addSql('DROP TABLE IF EXISTS statistics');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE statistics (id SERIAL NOT NULL, habit_id INT DEFAULT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, source_type VARCHAR(32) NOT NULL, value INT NOT NULL, recorded_at DATE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_60B32CB4A0CA4A5D ON statistics (habit_id)');
        $this->addSql('CREATE INDEX IDX_60B32CB4A76ED395 ON statistics (user_id)');
        $this->addSql('ALTER TABLE statistics ADD CONSTRAINT FK_60B32CB4A0CA4A5D FOREIGN KEY (habit_id) REFERENCES habits (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statistics ADD CONSTRAINT FK_60B32CB4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
