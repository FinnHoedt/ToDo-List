<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329205156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access DROP CONSTRAINT FK_6FE2BE76EA1EBC33
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access DROP CONSTRAINT FK_6FE2BE7659EC7D60
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ADD category_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ALTER todo_id SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ALTER assignee_id SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ADD CONSTRAINT FK_6FE2BE7612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ADD CONSTRAINT FK_6FE2BE76EA1EBC33 FOREIGN KEY (todo_id) REFERENCES todo (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ADD CONSTRAINT FK_6FE2BE7659EC7D60 FOREIGN KEY (assignee_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6FE2BE7612469DE2 ON todo_access (category_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access DROP CONSTRAINT FK_6FE2BE7612469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access DROP CONSTRAINT fk_6fe2be76ea1ebc33
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access DROP CONSTRAINT fk_6fe2be7659ec7d60
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6FE2BE7612469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access DROP category_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ALTER todo_id DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ALTER assignee_id DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ADD CONSTRAINT fk_6fe2be76ea1ebc33 FOREIGN KEY (todo_id) REFERENCES todo (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE todo_access ADD CONSTRAINT fk_6fe2be7659ec7d60 FOREIGN KEY (assignee_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }
}
