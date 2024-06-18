<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200519095541 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
ALTER TABLE pcmt_catalog_category_access DROP FOREIGN KEY FK_65E086021ED93D47;
ALTER TABLE pcmt_catalog_category_access ADD CONSTRAINT FK_A996A2F71ED93D47 FOREIGN KEY (user_group_id) REFERENCES oro_access_group (id) ON DELETE CASCADE;
ALTER TABLE pcmt_catalog_category_access DROP FOREIGN KEY FK_65E0860212469DE2;
ALTER TABLE pcmt_catalog_category_access ADD CONSTRAINT FK_A996A2F712469DE2 FOREIGN KEY (category_id) REFERENCES pim_catalog_category (id) ON DELETE CASCADE;
SQL;
        $this->addSql($sql);

        // this up() migration is auto-generated, please modify it to your needs
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
