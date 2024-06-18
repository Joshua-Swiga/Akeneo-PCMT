<?php

declare(strict_types=1);

namespace PcmtRulesBundle\Provider;

use Akeneo\Platform\Bundle\ImportExportBundle\Datagrid\JobDatagridProvider;

class PcmtJobDatagridProvider extends JobDatagridProvider
{
    public function getRulesJobChoices(): array
    {
        return $this->getJobChoices('rules');
    }

    public function getRulesConnectorChoices(): array
    {
        return $this->getConnectorChoices('rules');
    }
}
