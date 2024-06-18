<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class PullImageService
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var string */
    private $sourceAttributeCode;

    /** @var string */
    private $destinationAttributeCode;

    /** @var string */
    private $tmpStorageDir;

    /** @var FileStorer */
    private $fileStorer;

    /** @var Client */
    private $httpClient;

    /** @var ImageVerificationService */
    private $imageVerificationService;

    public function __construct(
        string $tmpStorageDir,
        FileStorer $fileStorer,
        ImageVerificationService $imageVerificationService
    ) {
        $this->tmpStorageDir = $tmpStorageDir;
        $this->fileStorer = $fileStorer;
        $this->httpClient = new Client();
        $this->imageVerificationService = $imageVerificationService;
    }

    public function setSourceAttributeCode(string $sourceAttributeCode): void
    {
        $this->sourceAttributeCode = $sourceAttributeCode;
    }

    public function processEntity(EntityWithValuesInterface $entity): ?FileInfoInterface
    {
        $sourceValue = $entity->getValue($this->sourceAttributeCode);
        if (!$sourceValue || !$sourceValue->getData()) {
            return null;
        }

        $this->stepExecution->incrementSummaryInfo('source_urls_found', 1);

        $url = $sourceValue->getData();
        $path = parse_url($url, PHP_URL_PATH);
        $pathParts = pathinfo($path);
        $filePath = $this->tmpStorageDir . '/' . $pathParts['basename'];

        $this->httpClient->get($url, [
            RequestOptions::SINK            => $filePath,
            RequestOptions::TIMEOUT         => 15,
            RequestOptions::CONNECT_TIMEOUT => 5,
        ]);

        $downloadedFile = new \SplFileInfo($filePath);

        if (!$this->checkIfSameImageAlreadyExists($entity, $downloadedFile)) {
            return $this->fileStorer->store($downloadedFile, FileStorage::CATALOG_STORAGE_ALIAS, true);
        }

        return null;
    }

    private function checkIfSameImageAlreadyExists(EntityWithValuesInterface $entity, \SplFileInfo $downloadedFile): bool
    {
        $destinationValue = $entity->getValue($this->destinationAttributeCode);
        if (!$destinationValue || !$destinationValue->getData()) {
            return false;
        }

        return $this->imageVerificationService->verifyIfSame($destinationValue->getData(), $downloadedFile);
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function setHttpClient(Client $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    public function setDestinationAttributeCode(string $destinationAttributeCode): void
    {
        $this->destinationAttributeCode = $destinationAttributeCode;
    }
}