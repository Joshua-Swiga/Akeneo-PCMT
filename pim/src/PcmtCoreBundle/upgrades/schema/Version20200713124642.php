<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200713124642 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
DELETE FROM akeneo_pim.akeneo_batch_job_instance 
WHERE code='job_drafts_bulk_reject'
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
INSERT INTO akeneo_pim.akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
VALUES (
        'job_drafts_bulk_reject', 
        'Drafts bulk reject', 
        'job_drafts_bulk_reject', 
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
WHERE code='job_drafts_bulk_reject'
SQL;
        $this->addSql($sql);
    }
}
