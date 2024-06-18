<?php
/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Command;

use PcmtRulesBundle\Malawi\RuleProcessStep;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MalawiMappingProductsCommand extends ContainerAwareCommand
{
    /**
     * run inside terminal in fpm docker: bin/console pcmt:malawi:map_products
     *
     * @var string
     */
    protected static $defaultName = 'pcmt:malawi:map_products';

    public function configure(): void
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        /** @var RuleProcessStep $service */
        $service = $this->getContainer()->get('pcmt.malawi.rule_process_step');
        $service->execute();
    }
}
