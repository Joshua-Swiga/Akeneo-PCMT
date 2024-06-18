<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20220704204334 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
DROP TABLE IF EXISTS `pcmt_fhir_attribute_mapping`;

CREATE TABLE `pcmt_fhir_attribute_mapping` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `attribute_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `mapping` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `searchunique_idx` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
    }
}
