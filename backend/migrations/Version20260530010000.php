<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260530010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create telegram link tokens table and add unique index for user telegram id';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE telegram_link_tokens (id SERIAL NOT NULL, user_id INT NOT NULL, token VARCHAR(64) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, consumed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_telegram_link_token ON telegram_link_tokens (token)');
        $this->addSql('CREATE INDEX IDX_C4B9D55AA76ED395 ON telegram_link_tokens (user_id)');
        $this->addSql('ALTER TABLE telegram_link_tokens ADD CONSTRAINT FK_C4B9D55AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USERS_TELEGRAM_ID ON users (telegram_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_USERS_TELEGRAM_ID');
        $this->addSql('ALTER TABLE telegram_link_tokens DROP CONSTRAINT FK_C4B9D55AA76ED395');
        $this->addSql('DROP TABLE telegram_link_tokens');
    }
}
