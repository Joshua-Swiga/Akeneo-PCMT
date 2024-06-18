<?php
/**
 * This handler downloads xml files from external urls.
 * It may be used to download fresh xmls with reference data.
 * The xmls used in the system are committed to repository in folder ...
 *
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Command\Handler;

use PcmtCoreBundle\Registry\PcmtConnectorJobParametersRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcmtReferenceDataDownloadHandler extends ContainerAwareCommand
{
    protected const DEFAULT_JOB_CODE = 'reference_data_download_xmls';

    /** @var string */
    protected static $defaultName = 'pcmt:handler:download_reference_data';

    public function configure(): void
    {
        $this->addArgument('code', InputArgument::OPTIONAL, 'Can override default job code name.');
        $this->addArgument('dirPath', InputArgument::OPTIONAL, 'Can override default directory where xmls are saved.');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        try {
            $this->createJobIfNotExists($output);
            $command = $this->getApplication()->find('akeneo:batch:job');
            $arguments = [
                'code' => $input->getArgument('code') ?? self::DEFAULT_JOB_CODE,
            ];
            if ($dirPath = null !== $input->getArgument('dirPath')) {
                $arguments['-c'] = $dirPath;
            }

            $input = new ArrayInput($arguments);

            $command->run($input, $output);
        } catch (\Throwable $exception) {
            $output->writeln($exception->getMessage());
            die;
        }
    }

    private function createJobIfNotExists(OutputInterface $output): void
    {
        $jobCreatror = $this->getApplication()->find('pcmt:job-creator');
        $arguments = [
            'jobName' => PcmtConnectorJobParametersRegistry::JOB_REFERENCE_DATA_DOWNLOAD_NAME,
        ];
        $input = new ArrayInput($arguments);
        $jobCreatror->run($input, $output);
    }
}
