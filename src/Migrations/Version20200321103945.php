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
    // Add TFT displays
        $this->addSql("INSERT INTO `display` VALUES
(11, 'ST7735_DRIVER', 'st7735', 'x', 128, 128, 0, NULL, 0, 'https://www.amazon.com/Display-Module-128X128-ColorTFT-Screen/dp/B07KPYKP9C/?ref=singandkaraok', 'https://www.amazon.com/Display-Module-128X128-ColorTFT-Screen/dp/B07KPYKP9C/?ref=singandkaraok', 'tft'),
(12, 'ILI9341_DRIVER', '2.2 inch QVGA', 'TTGO T4, Nextion, Phoncoo and others', 320, 240, 0, NULL, NULL, 'https://www.amazon.com/Display-Module-240x320-ILI9341-Interface/dp/B07KFC67T6/?ref=singandkaraok', 'https://www.amazon.com/Display-Module-240x320-ILI9341-Interface/dp/B07KFC67T6/?ref=singandkaraok', 'tft'),
(13, 'ILI9486_DRIVER', '3.5 inch Touch', 'Kuman & others', 480, 320, 0, '0', 0, 'https://www.amazon.com/Kuman-Arduino-Screen-Tutorials-Mega2560/dp/B075FP83V5/?ref=singandkaraok', 'https://www.amazon.com/Kuman-Arduino-Screen-Tutorials-Mega2560/dp/B075FP83V5/?ref=singandkaraok', 'tft'),
(14, 'ST7796_DRIVER', '4 inch RPI RGB 65K color', 'Surenoo & others', 480, 320, 0, '48.9 * 73.4', 0, 'https://www.surenoo.com', 'https://www.aliexpress.com/item/4000234986822.html', 'tft'),
(15, 'SSD1963', '5 inch 800x480 LCD Display', 'x', 800, 480, 0, '0', 0, 'https://ecksteinimg.de/Datasheet/CP11009.zip', 'https://eckstein-shop.de/50-800x480-TFT-LCD-Display-mit-Touchscreen-SSD1963-MCU-Arduino-Kompatibel?gclid=Cj0KCQjw9tbzBRDVARIsAMBplx8is7mDfRPw7VRkfcGkOgpwAnsQRKvD5sqCehjZus05Rq6s08XyIgAaAkGCEALw_wcB', 'tft')
");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM display where id BETWEEN 10 AND 15");

    }
}
