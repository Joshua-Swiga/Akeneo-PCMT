<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Step;

use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use PcmtCoreBundle\Connector\Job\InvalidItems\UrlInvalidItem;
use PcmtCoreBundle\Connector\Job\JobParameters\DefaultValueProvider\ReferenceDataXmlDownloadProvider;
use PcmtCoreBundle\Connector\Job\JobParameters\DefaultValueProvider\ReferenceDataXmlImportProvider;
use PcmtCoreBundle\Service\Builder\PathBuilder;
use PcmtCoreBundle\Util\Adapter\DirectoryCreator;
use PcmtCoreBundle\Validator\Directory\DirectoryPathValidator;
use PcmtSharedBundle\Connector\Job\InvalidItems\SimpleInvalidItem;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;

class ReferenceDataXmlDownloadStep extends AbstractStep
{
    /** @var string */
    private $rootDirectory;

    /** @var string */
    private $tmpDirectory;

    /** @var string */
    private $failedDirectory;

    /** @var string */
    private $configDirectory;

    /** @var ClientInterface */
    protected $guzzleClient;

    /** @var PathBuilder */
    private $pathBuilder;

    /** @var DirectoryPathValidator */
    private $directoryValidator;

    /** @var LoggerInterface */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        DirectoryPathValidator $directoryValidator,
        PathBuilder $pathBuilder
    ) {
        $this->guzzleClient = new Client();
        $this->pathBuilder = $pathBuilder;
        $this->directoryValidator = $directoryValidator;
        parent::__construct($name, $eventDispatcher, $jobRepository);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    protected function doExecute(StepExecution $stepExecution): void
    {
        $jobParameters = $stepExecution->getJobParameters();
        $dirPath = $jobParameters->get('dirPath');
        if ($dirPath) {
            $this->rootDirectory = $dirPath;
            $this->tmpDirectory = $this->rootDirectory . ReferenceDataXmlImportProvider::WORK_DIR;
            $this->failedDirectory = $this->rootDirectory . ReferenceDataXmlImportProvider::FAILED_DIR;
            $this->configDirectory = $this->rootDirectory . 'config/';
            $this->pathBuilder->setPath($this->rootDirectory);
        }
        $this->directoryValidator->validate('reference_data_files_path', $this->rootDirectory);

        $this->createDirectories($this->rootDirectory);
        DirectoryCreator::createDirectory($this->rootDirectory);

        $configFile = $this->configDirectory . ReferenceDataXmlDownloadProvider::CONFIG_FILE_NAME;
        try {
            $yml = Yaml::parseFile($configFile);
            $urls = $yml['urls'];
        } catch (\Throwable $exception) {
            $invalidItem = new SimpleInvalidItem(
                [
                    'File' => $configFile,
                ]
            );
            $stepExecution->addWarning(
                $exception->getMessage(),
                [],
                $invalidItem
            );

            return;
        }
        foreach ($urls as $url) {
            $this->processUrl($url, $stepExecution, 1);
        }
    }

    private function processUrl(string $url, StepExecution $stepExecution, int $attempt): void
    {
        $this->logger->info('Processing ' . $url.', attempt: '. $attempt);
        $matches = [];
        preg_match('/cl:(.*?)&/', $url, $matches) . '.xml';
        $className = $matches[1];
        $fileName = $className . '.xml';
        try {
            $tmpFilePath = $this->tmpDirectory . $fileName;
            $tmpFile = fopen($tmpFilePath, 'w');
            $response = $this->guzzleClient->get($url, [
                'save_to' => $tmpFile,
                'timeout' => 5,
            ]);
            fclose($tmpFile);
            $this->logger->info('Finished successfully');

            $stepExecution->incrementSummaryInfo($response->getStatusCode());
            $stepExecution->incrementSummaryInfo($className);
        } catch (\Throwable $exception) {
            if ($attempt < 4) {
                $this->processUrl($url, $stepExecution, ++$attempt);

                return;
            }
            $this->logger->info('FAILED');
            $invalidItem = new UrlInvalidItem($url);
            $stepExecution->addWarning(
                $exception->getMessage(),
                [],
                $invalidItem
            );
            $stepExecution->incrementSummaryInfo('failed');
            try {
                rename($tmpFilePath, $this->failedDirectory . $this->pathBuilder->getFileNameWithTime($fileName));
            } catch (\Throwable $exception) {
            }
        }
    }

    private function createDirectories(string $path): bool
    {
        $path_split = explode('/', $path);
        $buildPath = '';
        foreach ($path_split as $pathElem) {
            if ('' === $pathElem) {
                continue;
            }
            $buildPath .= $pathElem . '/';
            if (is_dir($buildPath)) {
                continue;
            }
            mkdir($buildPath, 0777);
        }

        return true;
    }
}