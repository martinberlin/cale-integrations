<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200321103945 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add new type column to Display. Add new Waveshare display GDEW0213I5F 2.13" b/w flexible';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('update `display` set `type`="eink" WHERE class_name LIKE "GDE%"');

        $this->addSql("INSERT INTO `display` (`id`, `class_name`, `name`, `brand`, `width`, `height`, `gray_levels`, `active_size`, `time_of_refresh`, `manual_url`, `purchase_url`, `type`) 
VALUES ('10', 'GDEW0213I5F', '2.13 Flexible', 'Waveshare', '212', '104', '2', '23.7*48.5', '2', 'http://www.waveshare.com/wiki/2.13inch_e-Paper_HAT_(D)', 'https://www.waveshare.com/2.13inch-e-paper-d.htm', 'eink')");

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM display where id=10");

    }
}
