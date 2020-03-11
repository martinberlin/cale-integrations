<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200310181134 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add API 5: Internal HTML content or image';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO `app_api` (`id`, `url_name`, `name`, `url`, `request_parameters`, `response_type`, `is_location_api`, `documentation_url`, `default_json_settings`, `api_cat_id`, `auth_note`,
 `json_route`, `edit_route`) 
VALUES ('5', 
'cale-html', 'CALE HTML editor', 'https://cale.es', NULL, 'html', '0', 'https://cale.es', NULL, '6', NULL, 
'App\\\Controller\\\Render\\\HRenderController::render_html', 'b_api_wizard_cale-html')");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DELETE FROM app_api WHERE id=5");
    }
}
