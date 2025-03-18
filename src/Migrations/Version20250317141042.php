<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250317141042 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add ApiLog table';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE app_apilog (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, api VARCHAR(40) DEFAULT NULL,
temperature FLOAT NOT NULL, humidity FLOAT NOT NULL, co2 INT NOT NULL, light NUMERIC(2, 0) DEFAULT NULL, 
timezone VARCHAR(100) DEFAULT NULL , timestamp INT NULL, datestamp DATETIME DEFAULT NULL,
INDEX IDX_58EBF94DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_apilog ADD CONSTRAINT FK_58EBF94DA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE app_user CHANGE paid_total paid_total INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE app_apilog ADD CONSTRAINT FK_58EBF94DAD05D80F FOREIGN KEY (api) REFERENCES app_int_api (uuid)');
        $this->addSql('CREATE INDEX IDX_58EBF94DAD05D80F ON app_apilog (api)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE app_apilog');
        $this->addSql('ALTER TABLE app_user CHANGE paid_total paid_total INT DEFAULT 0');
    }
}
