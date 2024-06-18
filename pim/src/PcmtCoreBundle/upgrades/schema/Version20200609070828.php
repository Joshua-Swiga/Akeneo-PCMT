<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200609070828 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->getTable('pim_catalog_attribute_option');

        if (!$table->hasColumn('type')) {
            $sql = <<<SQL
ALTER TABLE pim_catalog_attribute_option ADD type VARCHAR(255) DEFAULT NULL;
SQL;
            $this->addSql($sql);
        }

        if (!$table->hasColumn('type_config')) {
            $sql = <<<SQL
ALTER TABLE pim_catalog_attribute_option ADD type_config JSON DEFAULT NULL COMMENT '(DC2Type:json_array)'
SQL;
            $this->addSql($sql);
        }

        if (!$table->hasColumn('constraints')) {
            $sql = <<<SQL
ALTER TABLE pim_catalog_attribute_option ADD constraints JSON DEFAULT NULL COMMENT '(DC2Type:json_array)';
SQL;
            $this->addSql($sql);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
