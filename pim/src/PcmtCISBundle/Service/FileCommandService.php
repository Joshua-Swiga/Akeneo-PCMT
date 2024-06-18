<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Service;

use PcmtCISBundle\Entity\Subscription;
use PcmtCISBundle\Exception\FileIsWaitingForUploadException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class FileCommandService
{
    /** @var Filesystem */
    private $filesystem;

    /** @var FileContentService */
    private $fileContentService;

    /** @var FileNameService */
    private $fileNameService;

    /** @var DirectoryService */
    private $directoryService;

    /** @var FileSearchService */
    private $fileSearchService;

    public function __construct(
        Filesystem $filesystem,
        FileContentService $fileContentService,
        FileNameService $fileNameService,
        DirectoryService $directoryService,
        FileSearchService $fileSearchService
    ) {
        $this->filesystem = $filesystem;
        $this->fileContentService = $fileContentService;
        $this->fileNameService = $fileNameService;
        $this->directoryService = $directoryService;
        $this->fileSearchService = $fileSearchService;
    }

    public function createFile(Subscription $subscription, string $documentCommandType): void
    {
        $this->directoryService->prepare();
        $content = $this->fileContentService->getSubscriptionContent($subscription, $documentCommandType);

        if ($this->fileSearchService->isFileWaitingForUploadByContent($content)) {
            throw new FileIsWaitingForUploadException('There is existing file which is waiting for upload.');
        }

        $filepath = $this->directoryService->getWorkDirectory() . $this->fileNameService->get();

        $this->filesystem->touch($filepath);

        if (!$this->filesystem->exists($filepath)) {
            throw new FileNotFoundException('File has not been created!');
        }

        $this->filesystem->appendToFile($filepath, $this->fileContentService->getHeader() . PHP_EOL);
        $this->filesystem->appendToFile(
            $filepath,
            $content
        );
    }
}