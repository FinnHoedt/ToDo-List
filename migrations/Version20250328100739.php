<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250328100739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE todo_access (id SERIAL NOT NULL, todo_id INT DEFAULT NULL, prioritization INT NOT NULL, shared BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6FE2BE76EA1EBC33 ON todo_access (todo_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ADD CONSTRAINT FK_6FE2BE76EA1EBC33 FOREIGN KEY (todo_id) REFERENCES todo (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access DROP CONSTRAINT FK_6FE2BE76EA1EBC33
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE todo_access
        SQL);
    }
}
