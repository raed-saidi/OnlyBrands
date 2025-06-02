<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250530220051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        
        // First, check if the FK_52EA1F098D9F6D38 constraint exists before trying to drop it
        $this->addSql(<<<'SQL'
            SET @constraint_exists = (
                SELECT COUNT(1) FROM information_schema.table_constraints 
                WHERE constraint_name = 'FK_52EA1F098D9F6D38' 
                AND table_name = 'order_item' 
                AND constraint_type = 'FOREIGN KEY'
            );
            
            SET @sql = IF(@constraint_exists > 0, 
                'ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38', 
                'SELECT 1');
            
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        
        // Similarly check if FK_F5299398A76ED395 exists before dropping
        $this->addSql(<<<'SQL'
            SET @constraint_exists = (
                SELECT COUNT(1) FROM information_schema.table_constraints 
                WHERE constraint_name = 'FK_F5299398A76ED395' 
                AND table_name = 'order' 
                AND constraint_type = 'FOREIGN KEY'
            );
            
            SET @sql = IF(@constraint_exists > 0, 
                'ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395', 
                'SELECT 1');
            
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        
        // Now create the new shop_orders table if it doesn't exist
        $this->addSql(<<<'SQL'
            CREATE TABLE IF NOT EXISTS shop_orders (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, total NUMERIC(10, 2) NOT NULL, status VARCHAR(50) NOT NULL, shipping_address LONGTEXT NOT NULL, billing_address LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_608DDB6CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        
        // Check if the FK_608DDB6CA76ED395 constraint already exists before adding it
        $this->addSql(<<<'SQL'
            SET @constraint_exists = (
                SELECT COUNT(1) FROM information_schema.table_constraints 
                WHERE constraint_name = 'FK_608DDB6CA76ED395' 
                AND table_name = 'shop_orders' 
                AND constraint_type = 'FOREIGN KEY'
            );
            
            SET @sql = IF(@constraint_exists = 0, 
                'ALTER TABLE shop_orders ADD CONSTRAINT FK_608DDB6CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)', 
                'SELECT 1');
            
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        
        // Drop the old order table if it exists
        $this->addSql(<<<'SQL'
            DROP TABLE IF EXISTS `order`
        SQL);
        
        // Check if the FK_52EA1F098D9F6D38 constraint already exists before adding it
        $this->addSql(<<<'SQL'
            SET @constraint_exists = (
                SELECT COUNT(1) FROM information_schema.table_constraints 
                WHERE constraint_name = 'FK_52EA1F098D9F6D38' 
                AND table_name = 'order_item' 
                AND constraint_type = 'FOREIGN KEY'
            );
            
            SET @sql = IF(@constraint_exists = 0, 
                'ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES shop_orders (id)', 
                'SELECT 1');
            
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        // Check if columns already exist before trying to add/drop them
        $this->addSql(<<<'SQL'
            -- Check if image_name column exists
            SET @column_exists = (
                SELECT COUNT(1) FROM information_schema.columns
                WHERE table_name = 'product' AND column_name = 'image_name'
            );
            
            -- Check if updated_at column exists
            SET @updated_at_exists = (
                SELECT COUNT(1) FROM information_schema.columns
                WHERE table_name = 'product' AND column_name = 'updated_at'
            );
            
            -- Check if image column exists
            SET @image_exists = (
                SELECT COUNT(1) FROM information_schema.columns
                WHERE table_name = 'product' AND column_name = 'image'
            );
            
            -- Build dynamic SQL based on column existence
            SET @add_image_name = IF(@column_exists = 0, 'ADD image_name VARCHAR(255) DEFAULT NULL', '');
            SET @add_updated_at = IF(@updated_at_exists = 0, 'ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'', '');
            SET @drop_image = IF(@image_exists > 0, 'DROP image', '');
            
            -- Construct final SQL with proper commas
            SET @has_adds = LENGTH(CONCAT(@add_image_name, @add_updated_at)) > 0;
            SET @has_drops = LENGTH(@drop_image) > 0;
            
            -- Only execute if there are changes to make
            SET @has_changes = @has_adds OR @has_drops;
            
            -- Build parts of the SQL
            SET @add_part = CONCAT(
                IF(@add_image_name != '', @add_image_name, ''),
                IF(@add_image_name != '' AND @add_updated_at != '', ', ', ''),
                IF(@add_updated_at != '', @add_updated_at, '')
            );
            
            -- Final SQL with proper placement of commas between ADD and DROP
            SET @final_sql = IF(@has_changes,
                CONCAT('ALTER TABLE product ', 
                       @add_part,
                       IF(@has_adds AND @has_drops, ', ', ''),
                       @drop_image),
                'SELECT 1'
            );
            
            -- Execute if we have changes
            PREPARE stmt FROM @final_sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        
        // First, check if the FK_52EA1F098D9F6D38 constraint exists before trying to drop it
        $this->addSql(<<<'SQL'
            SET @constraint_exists = (
                SELECT COUNT(1) FROM information_schema.table_constraints 
                WHERE constraint_name = 'FK_52EA1F098D9F6D38' 
                AND table_name = 'order_item' 
                AND constraint_type = 'FOREIGN KEY'
            );
            
            SET @sql = IF(@constraint_exists > 0, 
                'ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38', 
                'SELECT 1');
            
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        
        // Create the original order table if it doesn't exist
        $this->addSql(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, total NUMERIC(10, 2) NOT NULL, status VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, shipping_address LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, billing_address LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        
        // Check if the FK_F5299398A76ED395 constraint already exists before adding it
        $this->addSql(<<<'SQL'
            SET @constraint_exists = (
                SELECT COUNT(1) FROM information_schema.table_constraints 
                WHERE constraint_name = 'FK_F5299398A76ED395' 
                AND table_name = 'order' 
                AND constraint_type = 'FOREIGN KEY'
            );
            
            SET @sql = IF(@constraint_exists = 0, 
                'ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION', 
                'SELECT 1');
            
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        
        // Check if FK_608DDB6CA76ED395 exists before dropping
        $this->addSql(<<<'SQL'
            SET @constraint_exists = (
                SELECT COUNT(1) FROM information_schema.table_constraints 
                WHERE constraint_name = 'FK_608DDB6CA76ED395' 
                AND table_name = 'shop_orders' 
                AND constraint_type = 'FOREIGN KEY'
            );
            
            SET @sql = IF(@constraint_exists > 0, 
                'ALTER TABLE shop_orders DROP FOREIGN KEY FK_608DDB6CA76ED395', 
                'SELECT 1');
            
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        
        // Drop shop_orders table
        $this->addSql(<<<'SQL'
            DROP TABLE IF EXISTS shop_orders
        SQL);
        
        // Modify product table
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD image LONGBLOB DEFAULT NULL, DROP image_name, DROP updated_at
        SQL);
        
        // Check if the FK_52EA1F098D9F6D38 constraint already exists before adding it back
        $this->addSql(<<<'SQL'
            SET @constraint_exists = (
                SELECT COUNT(1) FROM information_schema.table_constraints 
                WHERE constraint_name = 'FK_52EA1F098D9F6D38' 
                AND table_name = 'order_item' 
                AND constraint_type = 'FOREIGN KEY'
            );
            
            SET @sql = IF(@constraint_exists = 0, 
                'ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON UPDATE NO ACTION ON DELETE NO ACTION', 
                'SELECT 1');
            
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }
}
