<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Step;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use DirectoryIterator;
use PcmtCoreBundle\Connector\Job\InvalidItems\XmlInvalidItem;
use PcmtCoreBundle\Connector\Job\JobParameters\DefaultValueProvider\ReferenceDataXmlImportProvider;
use PcmtCoreBundle\Connector\Job\Reader\File\ReferenceDataXmlReaderInterface;
use PcmtCoreBundle\Service\Builder\PathBuilder;

class ReferenceDataXmlImportStep extends ItemStep
{
    /** @var ReferenceDataXmlReaderInterface */
    protected $reader = null;

    /** @var \RegexIterator */
    protected $fileIterator;

    /** @var PathBuilder */
    private $pathBuilder;

    /**
     * {@inheritdoc}
     */
    public function doExecute(StepExecution $stepExecution): void
    {
        $selectedFilePath = $stepExecution->getJobParameters()->get('filePath');
        if (ReferenceDataXmlImportProvider::ALL_FILES !== $selectedFilePath) {
            parent::doExecute($stepExecution);

            return;
        }
        $dirPath = $stepExecution->getJobParameters()->get('dirPath') . ReferenceDataXmlImportProvider::WORK_DIR;
        foreach (new DirectoryIterator($dirPath) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir() || '.gitignore' === $fileInfo->getFilename()) {
                continue;
            }

            $currentFile = $fileInfo->getPath() . '/' . $fileInfo->getFilename();
            $this->pathBuilder->setPath($currentFile);
            $oldDirectory = $stepExecution->getJobParameters()->get('dirPath') . ReferenceDataXmlImportProvider::OLD_DIR;
            try {
                $this->reader->setFilePath($currentFile);
                parent::doExecute($stepExecution);
                rename(
                    $currentFile,
                    $oldDirectory . $this->pathBuilder->getFileNameWithTime($fileInfo->getFilename())
                );
            } catch (\Throwable $exception) {
                $invalidItem = new XmlInvalidItem($currentFile);
                $stepExecution->addWarning(
                    $exception->getMessage(),
                    [],
                    $invalidItem
                );
                $stepExecution->incrementSummaryInfo('failed');
            }
            $stepExecution->incrementSummaryInfo($this->pathBuilder->getFileName(false));
        }
    }

    public function setPathBuilder(PathBuilder $pathBuilder): void
    {
        $this->pathBuilder = $pathBuilder;
    }
}