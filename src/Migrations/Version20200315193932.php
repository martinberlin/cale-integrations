<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200315193932 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add API 6: AWS Cloudwatch monitoring';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO `app_api` (`id`, `url_name`, `name`, `url`, `request_parameters`, `response_type`, `is_location_api`, `documentation_url`, `default_json_settings`, `api_cat_id`, `auth_note`,
 `json_route`, `edit_route`) 
VALUES ('6', 
'aws-cloudwatch', 'Amazon Cloudwatch monitoring', '', NULL, 'png', '0', 'https://aws.amazon.com/cloudwatch', NULL, '6', NULL, 
'App\\\Controller\\\Render\\\CRenderController::render_png', 'b_api_wizard_aws-cloudwatch')");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DELETE FROM app_api WHERE id=6");
    }
}
