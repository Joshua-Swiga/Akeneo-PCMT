<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200616094358 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE IF EXISTS pcmt_catalog_product_draft_category;
        ');

        $sql = '
CREATE TABLE pcmt_catalog_product_draft_category 
(
    draft_id INT NOT NULL, 
    category_id INT NOT NULL, 
    INDEX IDX_79F1B890E2F3C5D1 (draft_id), 
    INDEX IDX_79F1B89012469DE2 (category_id), 
    PRIMARY KEY(draft_id, category_id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC;
';
        $this->addSql($sql);
        $this->addSql('
            ALTER TABLE pcmt_catalog_product_draft_category 
            ADD CONSTRAINT FK_79F1B890E2F3C5D1 FOREIGN KEY (draft_id) REFERENCES pcmt_catalog_product_draft (id) ON DELETE CASCADE;
        ');
        $this->addSql('
            ALTER TABLE pcmt_catalog_product_draft_category 
            ADD CONSTRAINT FK_79F1B89012469DE2 FOREIGN KEY (category_id) REFERENCES pim_catalog_category (id) ON DELETE CASCADE;
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS pcmt_catalog_product_draft_category;');
    }
}
