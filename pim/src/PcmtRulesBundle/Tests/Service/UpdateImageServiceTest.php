<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PcmtRulesBundle\Service\UpdateImageService;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\ConstraintViolationBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\ConstraintViolationListBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateImageServiceTest extends TestCase
{
    /** @var ObjectUpdaterInterface|MockObject */
    private $updaterMock;

    /** @var SaverInterface|MockObject */
    private $saverMock;

    /** @var ValidatorInterface|MockObject */
    private $validatorMock;

    /** @var FileInfoInterface|MockObject */
    private $fileMock;

    protected function setUp(): void
    {
        $this->updaterMock = $this->createMock(ObjectUpdaterInterface::class);
        $this->saverMock = $this->createMock(SaverInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);

        $this->fileMock = $this->createMock(FileInfoInterface::class);
    }

    public function dataProcessEntity(): array
    {
        $entity = (new ProductBuilder())->build();

        return [
            [$entity],
        ];
    }

    /** @dataProvider dataProcessEntity */
    public function testProcessEntityWithException(EntityWithValuesInterface $entity): void
    {
        $violation = (new ConstraintViolationBuilder())->build();
        $violations = (new ConstraintViolationListBuilder())->withViolation($violation)->build();
        $this->validatorMock->method('validate')->willReturn($violations);

        $this->expectException(\Throwable::class);

        $service = $this->getServiceInstance();
        $service->processEntity($entity, $this->fileMock);
    }

    /** @dataProvider dataProcessEntity */
    public function testProcessEntity(EntityWithValuesInterface $entity): void
    {
        $violations = (new ConstraintViolationListBuilder())->build();
        $this->validatorMock->method('validate')->willReturn($violations);

        $this->saverMock->expects($this->once())->method('save');
        $this->updaterMock->expects($this->once())->method('update');

        $service = $this->getServiceInstance();
        $service->processEntity($entity, $this->fileMock);
    }

    private function getServiceInstance(): UpdateImageService
    {
        $service = new UpdateImageService($this->updaterMock, $this->saverMock, $this->validatorMock);
        $service->setDestinationAttributeCode('dest_attr_code');

        return $service;
    }
}