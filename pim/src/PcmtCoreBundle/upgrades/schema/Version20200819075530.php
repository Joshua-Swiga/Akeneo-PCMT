<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200819075530 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $sql = <<<SQL
    DROP TABLE IF EXISTS `pcmt_rule`;

    CREATE TABLE `pcmt_rule` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `source_family_id` int(11) DEFAULT NULL,
      `destination_family_id` int(11) DEFAULT NULL,
      `key_attribute_id` int(11) DEFAULT NULL,
      `unique_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
      `created` datetime NOT NULL COMMENT '(DC2Type:datetime)',
      `updated` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
      PRIMARY KEY (`id`),
      UNIQUE KEY `UNIQ_EFC3391BE3C68343` (`unique_id`),
      KEY `IDX_EFC3391BABC0C5A4` (`source_family_id`),
      KEY `IDX_EFC3391B92DEE690` (`destination_family_id`),
      KEY `IDX_EFC3391B596850D3` (`key_attribute_id`),
      CONSTRAINT `FK_EFC3391B596850D3` FOREIGN KEY (`key_attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE SET NULL,
      CONSTRAINT `FK_EFC3391B92DEE690` FOREIGN KEY (`destination_family_id`) REFERENCES `pim_catalog_family` (`id`) ON DELETE SET NULL,
      CONSTRAINT `FK_EFC3391BABC0C5A4` FOREIGN KEY (`source_family_id`) REFERENCES `pim_catalog_family` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
