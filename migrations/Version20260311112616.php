<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260311112616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bike_compatibilities (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, reason LONGTEXT DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, bike_from_id INT NOT NULL, bike_to_id INT NOT NULL, INDEX IDX_EBBACD86C2724036 (bike_from_id), INDEX IDX_EBBACD8699985331 (bike_to_id), UNIQUE INDEX unique_compatibility (bike_from_id, bike_to_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE bike_features (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, specification VARCHAR(100) DEFAULT NULL, is_active TINYINT NOT NULL, category_id INT NOT NULL, INDEX IDX_E7122C9B12469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE bike_images (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, alt_text VARCHAR(100) DEFAULT NULL, type VARCHAR(50) NOT NULL, position INT NOT NULL, is_active TINYINT NOT NULL, uploaded_at DATETIME NOT NULL, bike_id INT NOT NULL, INDEX IDX_8CB0869ED5A4816F (bike_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE bike_prices (id INT AUTO_INCREMENT NOT NULL, price_ht NUMERIC(10, 2) NOT NULL, price_ttc NUMERIC(10, 2) DEFAULT NULL, margin_rate NUMERIC(5, 2) DEFAULT NULL, valid_from DATETIME DEFAULT NULL, valid_until DATETIME DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, variant_id INT NOT NULL, segment_id INT NOT NULL, INDEX IDX_886455AD3B69A9AF (variant_id), INDEX IDX_886455ADDB296AAD (segment_id), UNIQUE INDEX unique_variant_segment (variant_id, segment_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE bike_specifications (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, value LONGTEXT NOT NULL, unit VARCHAR(50) DEFAULT NULL, position INT NOT NULL, variant_id INT NOT NULL, INDEX IDX_5FFF42B93B69A9AF (variant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE bike_variants (id INT AUTO_INCREMENT NOT NULL, color VARCHAR(50) NOT NULL, size VARCHAR(10) NOT NULL, base_price NUMERIC(8, 2) NOT NULL, weight INT DEFAULT NULL, `condition` VARCHAR(20) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, bike_id INT NOT NULL, motor_id INT DEFAULT NULL, INDEX IDX_EB4AA369D5A4816F (bike_id), INDEX IDX_EB4AA36980D58D71 (motor_id), UNIQUE INDEX unique_bike_variant (bike_id, color, size), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE bikes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, features LONGTEXT DEFAULT NULL, model_year INT DEFAULT NULL, segment_level VARCHAR(50) NOT NULL, is_active TINYINT NOT NULL, is_featured TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, category_id INT NOT NULL, brand_id INT NOT NULL, UNIQUE INDEX UNIQ_F6FAF01C989D9B62 (slug), INDEX IDX_F6FAF01C12469DE2 (category_id), INDEX IDX_F6FAF01C44F5D008 (brand_id), INDEX IDX_F6FAF01C989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE bikes_equipments (bike_id INT NOT NULL, bike_feature_id INT NOT NULL, INDEX IDX_825B5B60D5A4816F (bike_id), INDEX IDX_825B5B602E0300F4 (bike_feature_id), PRIMARY KEY (bike_id, bike_feature_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE brands (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, logo_url VARCHAR(255) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, website LONGTEXT DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_7EA24434989D9B62 (slug), INDEX IDX_7EA24434989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, icon VARCHAR(100) DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_3AF34668989D9B62 (slug), INDEX IDX_3AF34668989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE customer_segments (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, discount_rate NUMERIC(5, 2) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE feature_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, is_active TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE motors (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, wattage INT NOT NULL, torque INT DEFAULT NULL, battery_capacity INT DEFAULT NULL, `range` INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, brand_id INT NOT NULL, INDEX IDX_6D19DD9F44F5D008 (brand_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE package_items (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, price_override NUMERIC(10, 2) DEFAULT NULL, position INT NOT NULL, package_id INT NOT NULL, variant_id INT NOT NULL, INDEX IDX_741AEEAAF44CABFF (package_id), INDEX IDX_741AEEAA3B69A9AF (variant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE packages (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, total_price_ht NUMERIC(10, 2) NOT NULL, package_discount NUMERIC(10, 2) DEFAULT NULL, is_active TINYINT NOT NULL, is_featured TINYINT NOT NULL, valid_from DATETIME DEFAULT NULL, valid_until DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BB5C0A7989D9B62 (slug), INDEX IDX_9BB5C0A7989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reviews (id INT AUTO_INCREMENT NOT NULL, author_name VARCHAR(100) NOT NULL, author_email VARCHAR(100) DEFAULT NULL, rating INT NOT NULL, title VARCHAR(200) NOT NULL, content LONGTEXT NOT NULL, is_approved TINYINT NOT NULL, created_at DATETIME NOT NULL, approved_at DATETIME DEFAULT NULL, bike_id INT NOT NULL, INDEX IDX_6970EB0FD5A4816F (bike_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stocks (id INT AUTO_INCREMENT NOT NULL, warehouse VARCHAR(100) NOT NULL, quantity INT NOT NULL, reorder_level INT DEFAULT NULL, last_restock_date DATETIME NOT NULL, updated_at DATETIME NOT NULL, variant_id INT NOT NULL, INDEX IDX_56F798053B69A9AF (variant_id), UNIQUE INDEX unique_variant_warehouse (variant_id, warehouse), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE bike_compatibilities ADD CONSTRAINT FK_EBBACD86C2724036 FOREIGN KEY (bike_from_id) REFERENCES bikes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_compatibilities ADD CONSTRAINT FK_EBBACD8699985331 FOREIGN KEY (bike_to_id) REFERENCES bikes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_features ADD CONSTRAINT FK_E7122C9B12469DE2 FOREIGN KEY (category_id) REFERENCES feature_categories (id)');
        $this->addSql('ALTER TABLE bike_images ADD CONSTRAINT FK_8CB0869ED5A4816F FOREIGN KEY (bike_id) REFERENCES bikes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_prices ADD CONSTRAINT FK_886455AD3B69A9AF FOREIGN KEY (variant_id) REFERENCES bike_variants (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_prices ADD CONSTRAINT FK_886455ADDB296AAD FOREIGN KEY (segment_id) REFERENCES customer_segments (id)');
        $this->addSql('ALTER TABLE bike_specifications ADD CONSTRAINT FK_5FFF42B93B69A9AF FOREIGN KEY (variant_id) REFERENCES bike_variants (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_variants ADD CONSTRAINT FK_EB4AA369D5A4816F FOREIGN KEY (bike_id) REFERENCES bikes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_variants ADD CONSTRAINT FK_EB4AA36980D58D71 FOREIGN KEY (motor_id) REFERENCES motors (id)');
        $this->addSql('ALTER TABLE bikes ADD CONSTRAINT FK_F6FAF01C12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE bikes ADD CONSTRAINT FK_F6FAF01C44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id)');
        $this->addSql('ALTER TABLE bikes_equipments ADD CONSTRAINT FK_825B5B60D5A4816F FOREIGN KEY (bike_id) REFERENCES bikes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bikes_equipments ADD CONSTRAINT FK_825B5B602E0300F4 FOREIGN KEY (bike_feature_id) REFERENCES bike_features (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE motors ADD CONSTRAINT FK_6D19DD9F44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id)');
        $this->addSql('ALTER TABLE package_items ADD CONSTRAINT FK_741AEEAAF44CABFF FOREIGN KEY (package_id) REFERENCES packages (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE package_items ADD CONSTRAINT FK_741AEEAA3B69A9AF FOREIGN KEY (variant_id) REFERENCES bike_variants (id)');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0FD5A4816F FOREIGN KEY (bike_id) REFERENCES bikes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stocks ADD CONSTRAINT FK_56F798053B69A9AF FOREIGN KEY (variant_id) REFERENCES bike_variants (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_compatibilities DROP FOREIGN KEY FK_EBBACD86C2724036');
        $this->addSql('ALTER TABLE bike_compatibilities DROP FOREIGN KEY FK_EBBACD8699985331');
        $this->addSql('ALTER TABLE bike_features DROP FOREIGN KEY FK_E7122C9B12469DE2');
        $this->addSql('ALTER TABLE bike_images DROP FOREIGN KEY FK_8CB0869ED5A4816F');
        $this->addSql('ALTER TABLE bike_prices DROP FOREIGN KEY FK_886455AD3B69A9AF');
        $this->addSql('ALTER TABLE bike_prices DROP FOREIGN KEY FK_886455ADDB296AAD');
        $this->addSql('ALTER TABLE bike_specifications DROP FOREIGN KEY FK_5FFF42B93B69A9AF');
        $this->addSql('ALTER TABLE bike_variants DROP FOREIGN KEY FK_EB4AA369D5A4816F');
        $this->addSql('ALTER TABLE bike_variants DROP FOREIGN KEY FK_EB4AA36980D58D71');
        $this->addSql('ALTER TABLE bikes DROP FOREIGN KEY FK_F6FAF01C12469DE2');
        $this->addSql('ALTER TABLE bikes DROP FOREIGN KEY FK_F6FAF01C44F5D008');
        $this->addSql('ALTER TABLE bikes_equipments DROP FOREIGN KEY FK_825B5B60D5A4816F');
        $this->addSql('ALTER TABLE bikes_equipments DROP FOREIGN KEY FK_825B5B602E0300F4');
        $this->addSql('ALTER TABLE motors DROP FOREIGN KEY FK_6D19DD9F44F5D008');
        $this->addSql('ALTER TABLE package_items DROP FOREIGN KEY FK_741AEEAAF44CABFF');
        $this->addSql('ALTER TABLE package_items DROP FOREIGN KEY FK_741AEEAA3B69A9AF');
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0FD5A4816F');
        $this->addSql('ALTER TABLE stocks DROP FOREIGN KEY FK_56F798053B69A9AF');
        $this->addSql('DROP TABLE bike_compatibilities');
        $this->addSql('DROP TABLE bike_features');
        $this->addSql('DROP TABLE bike_images');
        $this->addSql('DROP TABLE bike_prices');
        $this->addSql('DROP TABLE bike_specifications');
        $this->addSql('DROP TABLE bike_variants');
        $this->addSql('DROP TABLE bikes');
        $this->addSql('DROP TABLE bikes_equipments');
        $this->addSql('DROP TABLE brands');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE customer_segments');
        $this->addSql('DROP TABLE feature_categories');
        $this->addSql('DROP TABLE motors');
        $this->addSql('DROP TABLE package_items');
        $this->addSql('DROP TABLE packages');
        $this->addSql('DROP TABLE reviews');
        $this->addSql('DROP TABLE stocks');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
