<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329205544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE todo DROP CONSTRAINT fk_5a0eb6a012469de2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_5a0eb6a012469de2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo DROP category_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access DROP CONSTRAINT FK_6FE2BE7612469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ALTER category_id SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ADD CONSTRAINT FK_6FE2BE7612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo ADD category_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo ADD CONSTRAINT fk_5a0eb6a012469de2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_5a0eb6a012469de2 ON todo (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access DROP CONSTRAINT fk_6fe2be7612469de2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ALTER category_id DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ADD CONSTRAINT fk_6fe2be7612469de2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }
}
