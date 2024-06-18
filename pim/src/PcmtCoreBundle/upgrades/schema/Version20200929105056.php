<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200929105056 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
DROP TABLE IF EXISTS `pcmt_cis_subscription`;

CREATE TABLE `pcmt_cis_subscription` (
      `id` INT AUTO_INCREMENT NOT NULL, 
      `target_market_country_code_id` INT DEFAULT NULL, 
      `data_recipients_gln` VARCHAR(255) NOT NULL, 
      `data_sources_gln` VARCHAR(255) NOT NULL, 
      `gtin` VARCHAR(255) NOT NULL, 
      `gpc_category_code` VARCHAR(255) NOT NULL, 
      `created` DATETIME NOT NULL COMMENT '(DC2Type:datetime)', 
      `updated` DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime)', 
      INDEX IDX_BFB4391724329ED9 (`target_market_country_code_id`), 
      PRIMARY KEY(`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC;

ALTER TABLE `pcmt_cis_subscription`
    ADD CONSTRAINT FK_BFB4391724329ED9 FOREIGN KEY (`target_market_country_code_id`) 
        REFERENCES pcmt_reference_data_gs1_codes (`id`) 
        ON DELETE SET NULL;
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
    }
}
