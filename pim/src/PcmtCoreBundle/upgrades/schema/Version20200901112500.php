<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20200901112500 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
DELETE FROM akeneo_pim.akeneo_batch_job_instance 
WHERE code='pcmt_rule_process'
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
INSERT INTO akeneo_pim.akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
VALUES (
        'pcmt_rule_process', 
        'PCMT rule process', 
        'pcmt_rule_process', 
        0, 
        'Pcmt Connector', 
        'a:0:{}', 
        'mass_action'
        );
SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<SQL
DELETE FROM akeneo_pim.akeneo_batch_job_instance 
WHERE code='pcmt_rule_process'
SQL;
        $this->addSql($sql);
    }
}
