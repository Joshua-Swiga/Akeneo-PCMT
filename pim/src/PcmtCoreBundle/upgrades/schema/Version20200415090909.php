<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200415090909 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
DELETE FROM akeneo_pim.akeneo_batch_job_instance 
WHERE code='reference_data_download_xmls'
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
INSERT INTO akeneo_pim.akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
VALUES (
        'reference_data_download_xmls', 
        'Reference data Xmls download', 
        'reference_data_download_xmls', 
        0, 
        'Pcmt Connector', 
        'a:2:{s:7:"dirPath";s:53:"src/PcmtCoreBundle/Resources/reference_data/gs1Codes/";s:8:"filePath";s:12:"any_path.xml";}',
        'import'
        );
SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<SQL
DELETE FROM akeneo_pim.akeneo_batch_job_instance 
WHERE code='reference_data_download_xmls'
SQL;
        $this->addSql($sql);
    }
}
