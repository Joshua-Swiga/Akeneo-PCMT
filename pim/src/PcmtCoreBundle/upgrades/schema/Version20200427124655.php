<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20200427124655 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS pcmt_catalog_category_access;');

        $sql = <<<SQL
CREATE TABLE pcmt_catalog_category_access (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, user_group_id SMALLINT DEFAULT NULL, level VARCHAR(255) NOT NULL, INDEX IDX_65E0860212469DE2 (category_id), INDEX IDX_65E086021ED93D47 (user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC;
ALTER TABLE pcmt_catalog_category_access ADD CONSTRAINT FK_65E0860212469DE2 FOREIGN KEY (category_id) REFERENCES pim_catalog_category (id);
ALTER TABLE pcmt_catalog_category_access ADD CONSTRAINT FK_65E086021ED93D47 FOREIGN KEY (user_group_id) REFERENCES oro_access_group (id);
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS pcmt_catalog_category_access;');
    }
}
