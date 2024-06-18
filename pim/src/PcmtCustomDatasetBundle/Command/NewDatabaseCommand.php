<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Command;

use Akeneo\Platform\Bundle\InstallerBundle\Command\DatabaseCommand;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use PcmtCustomDatasetBundle\Event\InstallerEvents;

class NewDatabaseCommand extends DatabaseCommand
{
    /**
     * Launches all commands needed after fixtures loading
     *
     * @throws \Exception
     */
    protected function launchCommands(): self
    {
        parent::launchCommands();

        if ('PcmtCustomDatasetBundle:pcmt_global' === $this->getContainer()->getParameter('installer_data')) {
            // in following command we use already imported xmls stored in code repository
            $this->commandExecutor->runCommand('pcmt:handler:import_reference_data');
            $this->commandExecutor->runCommand('pcmt:custom-dataset:create');
            $this->getEventDispatcher()->dispatch(
                InstallerEvents::POST_DATA_IMPORTED,
                new InstallerEvent($this->commandExecutor)
            );
        }

        return $this;
    }
}