<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use GuzzleHttp\Client;
use PcmtRulesBundle\Service\ImageVerificationService;
use PcmtRulesBundle\Service\PullImageService;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ValueBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\FileInfoBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\MediaValueBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PullImageServiceTest extends TestCase
{
    /** @var string */
    private $tmpStorageDirMock = '/tmp/storage/test';

    /** @var FileStorer|MockObject */
    private $fileStorerMock;

    /** @var string */
    private $sourceAttributeCode = 'source_code';

    /** @var string */
    private $destinationAttributeCode = 'destination_code';

    /** @var Client|MockObject */
    private $httpClientMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var ImageVerificationService|MockObject */
    private $imageVerificationServiceMock;

    public const DOWNLOADED_FILE_BASENAME = 'filebasename';

    protected function setUp(): void
    {
        $this->fileStorerMock = $this->createMock(FileStorer::class);
        $this->httpClientMock = $this->createMock(Client::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->imageVerificationServiceMock = $this->createMock(ImageVerificationService::class);
    }

    public function dataProcessEntity(): array
    {
        $valueUrl = (new ValueBuilder())
            ->withAttributeCode($this->sourceAttributeCode)
            ->withData(self::DOWNLOADED_FILE_BASENAME)
            ->build();
        $valueImage = (new MediaValueBuilder())
            ->withAttributeCode($this->destinationAttributeCode)
            ->withData((new FileInfoBuilder())->withOriginalFilename(self::DOWNLOADED_FILE_BASENAME)->build())
            ->build();

        $productAllValues = (new ProductBuilder())->addValue($valueUrl)->addValue($valueImage)->build();
        $productNoImageValue = (new ProductBuilder())->addValue($valueUrl)->build();
        $productNoUrlValue = (new ProductBuilder())->build();

        return [
            [$productNoUrlValue, false, 0],
            [$productNoImageValue, false, 1],
            [$productAllValues, false, 1],
            [$productAllValues, true, 0],
        ];
    }

    /** @dataProvider dataProcessEntity */
    public function testProcessEntity(EntityWithValuesInterface $entity, bool $ifSameImage, int $expectedStoreCalls): void
    {
        $this->imageVerificationServiceMock->method('verifyIfSame')->willReturn($ifSameImage);
        $this->fileStorerMock->expects($this->exactly($expectedStoreCalls))->method('store');
        $service = $this->getPullImageService();
        $service->processEntity($entity);
    }

    private function getPullImageService(): PullImageService
    {
        $service = new PullImageService($this->tmpStorageDirMock, $this->fileStorerMock, $this->imageVerificationServiceMock);
        $service->setSourceAttributeCode($this->sourceAttributeCode);
        $service->setDestinationAttributeCode($this->destinationAttributeCode);
        $service->setStepExecution($this->stepExecutionMock);
        $service->setHttpClient($this->httpClientMock);

        return $service;
    }
}