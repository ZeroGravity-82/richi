<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200213113002 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account DROP FOREIGN KEY FK_7D3656A4727ACA70');
        $this->addSql('DROP INDEX IDX_7D3656A4727ACA70 ON account');
        $this->addSql('DROP INDEX account_uq ON account');
        $this->addSql('ALTER TABLE account DROP parent_id');
        $this->addSql('CREATE UNIQUE INDEX account_uq ON account (user_id, name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX account_uq ON account');
        $this->addSql('ALTER TABLE account ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A4727ACA70 FOREIGN KEY (parent_id) REFERENCES account (id)');
        $this->addSql('CREATE INDEX IDX_7D3656A4727ACA70 ON account (parent_id)');
        $this->addSql('CREATE UNIQUE INDEX account_uq ON account (user_id, parent_id, name)');
    }
}
