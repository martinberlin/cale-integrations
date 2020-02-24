<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200224141053 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'New API: Internal iCAL parser';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `app_api` (`id`, `url_name`, `name`, `url`, `request_parameters`, `response_type`, `is_location_api`, `documentation_url`, `default_json_settings`, `api_cat_id`, `auth_note`, `json_route`, edit_route) 
        VALUES 
        ('4', 'cale-ical', 'CALE iCal API', 'https://cale.es', NULL, 'html', NULL, NULL, NULL, NULL, '', 'App\\\Controller\\\Render\\\ARenderController::render_int_ical', 'b_api_wizard_cale-ical')"
        );
        $this->addSql("UPDATE `app_api` set auth_note='Oauth', edit_route='b_api_wizard_cale-google' WHERE url_name='cale-google'");
        $this->addSql("UPDATE `app_api` set auth_note='Authorization code', edit_route='b_api_customize_location' WHERE url_name='weather-darksky'");
        $this->addSql("UPDATE `app_api` set edit_route='b_api_wizard_cale-timetree' WHERE url_name='cale-timetree'");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DELETE FROM app_api WHERE id=4");
    }
}
