<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200415043027 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
DELETE FROM akeneo_pim.akeneo_batch_job_instance 
WHERE code='reference_data_import_xml'
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
INSERT INTO akeneo_pim.akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
VALUES (
        'reference_data_import_xml', 
        'Reference data Xmls import', 
        'reference_data_import_xml', 
        0, 
        'Pcmt Connector', 
        'a:5:{s:8:"filePath";s:3:"ALL";s:7:"dirPath";s:53:"src/PcmtCoreBundle/Resources/reference_data/gs1Codes/";s:13:"uploadAllowed";a:2:{i:0;a:4:{s:7:"message";s:40:"This value should be of type {{ type }}.";s:4:"type";s:4:"bool";s:7:"payload";N;s:6:"groups";a:1:{i:0;s:7:"Default";}}i:1;a:3:{s:7:"message";s:26:"This value should be true.";s:7:"payload";N;s:6:"groups";a:1:{i:0;s:15:"UploadExecution";}}}s:16:"decimalSeparator";a:3:{s:7:"message";s:31:"This value should not be blank.";s:7:"payload";N;s:6:"groups";a:1:{i:0;s:7:"Default";}}s:10:"xmlMapping";N;}', 
        'import'
        );
SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<SQL
DELETE FROM akeneo_pim.akeneo_batch_job_instance 
WHERE code='reference_data_import_xml'
SQL;
        $this->addSql($sql);
    }
}
