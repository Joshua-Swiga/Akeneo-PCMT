<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Command\Handler;

use DirectoryIterator;
use PcmtCoreBundle\Service\Builder\PathBuilder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcmtReferenceDataImportHandler extends ContainerAwareCommand
{
    protected const DEFAULT_JOB_CODE = 'reference_data_import_xml';

    /** @var string */
    protected $dir;

    /** @var PathBuilder */
    private $pathBuilder;

    /** @var string */
    protected static $defaultName = 'pcmt:handler:import_reference_data';

    public function __construct()
    {
        $this->dir = 'src/PcmtCoreBundle/Resources/reference_data/gs1Codes/';
        $this->pathBuilder = new PathBuilder();

        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument('dirPath', InputArgument::OPTIONAL, 'Can override default directory where xmls are saved.');
        $this->addArgument('code', InputArgument::OPTIONAL, 'Can override default job code name.');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        foreach (new DirectoryIterator($this->dir) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir() || '.gitignore' === $fileInfo->getFilename()) {
                continue;
            }

            $currentFile = $fileInfo->getPath() . '/' . $fileInfo->getFilename();
            $this->pathBuilder->setPath($currentFile);
            try {
                $totalPath = str_replace('/', '\/', $currentFile);
                $arguments['code'] = $input->getArgument('code') ?? self::DEFAULT_JOB_CODE;
                $arguments['--config'] = sprintf('{"filePath": "%s"}', $totalPath);
                $this->executeCommand($output, $arguments);
            } catch (\Throwable $exception) {
                $output->writeln($exception->getMessage());
                continue;
            }
            $output->writeln($this->pathBuilder->getFileName(false));
        }
    }

    private function executeCommand(OutputInterface $output, array $arguments): int
    {
        try {
            $command = $this->getApplication()->find('akeneo:batch:job');
            $input = new ArrayInput($arguments);

            return $command->run($input, $output);
        } catch (\Throwable $exception) {
            $output->writeln($exception);
            die;
        }
    }
}