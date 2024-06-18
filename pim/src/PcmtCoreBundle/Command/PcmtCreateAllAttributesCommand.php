<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Command;

use PcmtCoreBundle\Command\Helper\GsCodesHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Exception\Exception;

class PcmtCreateAllAttributesCommand extends ContainerAwareCommand
{
    /**
     * run inside terminal in fpm docker: bin/console $defaultName
     */
    /** @var string */
    protected static $defaultName = 'pcmt:generate-ref-data-attr-all';

    public function configure(): void
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $codeList = GsCodesHelper::getGsCodes();
        $output->writeln([
            'All Reference Data Attribute Creator',
            '============',
        ]);
        $bar = new ProgressBar($output, count($codeList));
        $bar->setFormat('very_verbose');
        $bar->start();
        foreach ($codeList as $code) {
            // create as Attribute
            try {
                $command = $this->getApplication()->find('pcmt:generate-ref-data-attr');
                $arguments = [
                    'command'       => 'pcmt:generate-ref-data-attr',
                    'ref-data-name' => $code,
                ];
                $greetInput = new ArrayInput($arguments);
                $command->run($greetInput, $output);
            } catch (Exception $e) {
                $output->writeln($e);
            }
            $bar->advance();
        }
        $bar->finish();
    }
}