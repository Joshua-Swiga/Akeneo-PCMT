<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20201023080635 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            ALTER TABLE `pcmt_cis_subscription` MODIFY `gtin` VARCHAR(255) DEFAULT NULL;
            ALTER TABLE `pcmt_cis_subscription` MODIFY `gpc_category_code` VARCHAR(255) DEFAULT NULL;
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
