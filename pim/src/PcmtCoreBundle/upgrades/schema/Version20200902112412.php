<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20200902112412 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
UPDATE akeneo_pim.akeneo_batch_job_instance 
SET label = 'PCMT E2Open/GDSN Import' 
WHERE code='pcmt_e2open_import'
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
    }
}
