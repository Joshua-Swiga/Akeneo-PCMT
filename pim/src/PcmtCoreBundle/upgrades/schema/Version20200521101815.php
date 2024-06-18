<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200521101815 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS pcmt_catalog_attribute_group_access;');

        $sql = <<<SQL
CREATE TABLE pcmt_catalog_attribute_group_access (id INT AUTO_INCREMENT NOT NULL, user_group_id SMALLINT DEFAULT NULL, attribute_group_id INT DEFAULT NULL, level VARCHAR(255) NOT NULL, INDEX IDX_DD4A9AD41ED93D47 (user_group_id), INDEX IDX_DD4A9AD462D643B7 (attribute_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC;
ALTER TABLE pcmt_catalog_attribute_group_access ADD CONSTRAINT FK_DD4A9AD41ED93D47 FOREIGN KEY (user_group_id) REFERENCES oro_access_group (id) ON DELETE CASCADE;
ALTER TABLE pcmt_catalog_attribute_group_access ADD CONSTRAINT FK_DD4A9AD462D643B7 FOREIGN KEY (attribute_group_id) REFERENCES pim_catalog_attribute_group (id) ON DELETE CASCADE;
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE pcmt_catalog_attribute_group_access;');
    }
}
