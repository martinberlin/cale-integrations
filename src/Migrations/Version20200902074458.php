<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200902074458 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("INSERT INTO `app_api` (`id`, `url_name`, `name`, `url`, `request_parameters`, `response_type`, `is_location_api`, `documentation_url`, `default_json_settings`, `api_cat_id`, `auth_note`, `json_route`, `edit_route`) VALUES ('8', '', 'Image gallery', NULL, NULL, 'HTML', '0', NULL, NULL, '9', NULL, 'App\\\Controller\\\Render\\\GRenderController::render_gallery', 'b_api_image_gallery')");
        $this->addSql('CREATE TABLE app_user_api_gallery (user_id INT NOT NULL, user_api_id VARCHAR(40) NOT NULL, 
image_id INT NOT NULL, caption VARCHAR(140) DEFAULT NULL, 
position INT NOT NULL, 
extension VARCHAR(4) NOT NULL,
kb INT DEFAULT NULL,
INDEX IDX_97302469A76ED395 (user_id), INDEX IDX_9730246931C42205 (user_api_id), UNIQUE INDEX user_api_name_idx (user_id, user_api_id, image_id),
PRIMARY KEY(user_id, user_api_id, image_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_user_api_gallery ADD CONSTRAINT FK_97302469A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE app_user_api_gallery ADD CONSTRAINT FK_9730246931C42205 FOREIGN KEY (user_api_id) REFERENCES app_int_api (uuid)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DELETE FROM app_api where id=8');
        $this->addSql('DROP TABLE app_user_api_gallery');
    }
}
