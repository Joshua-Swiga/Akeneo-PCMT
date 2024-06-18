<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Connector\Job\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PropertiesNormalizer;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PcmtDraftBundle\Connector\Job\Writer\Database\DraftWriter;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\Service\Draft\BaseEntityCreatorInterface;
use PcmtDraftBundle\Service\Draft\DraftCreatorInterface;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class DraftWriterTest extends TestCase
{
    /** @var DraftWriter */
    private $draftWriter;

    /** @var VersionManager|MockObject */
    private $versionManagerMock;

    /** @var SaverInterface|MockObject */
    private $entitySaverMock;

    /** @var NormalizerInterface|MockObject */
    private $standardNormalizerMock;

    /** @var SaverInterface|MockObject */
    private $draftSaverMock;

    /** @var BaseEntityCreatorInterface |MockObject */
    private $baseEntityCreatorMock;

    /** @var DraftCreatorInterface|MockObject */
    private $draftCreatorMock;

    /** @var UserInterface|MockObject */
    private $userMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var JobParameters|MockObject */
    private $jobParametersMock;

    /** @var CategoryPermissionsCheckerInterface|MockObject */
    private $accessCheckerMock;

    /** @var ConverterInterface|MockObject */
    private $valueConverterMock;

    protected function setUp(): void
    {
        $this->versionManagerMock = $this->createMock(VersionManager::class);
        $this->entitySaverMock = $this->createMock(SaverInterface::class);
        $this->standardNormalizerMock = $this->createMock(NormalizerInterface::class);
        $this->draftSaverMock = $this->createMock(SaverInterface::class);
        $this->baseEntityCreatorMock = $this->createMock(BaseEntityCreatorInterface::class);
        $this->draftCreatorMock = $this->createMock(DraftCreatorInterface::class);
        $this->accessCheckerMock = $this->createMock(CategoryPermissionsCheckerInterface::class);

        $this->userMock = $this->createMock(UserInterface::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);

        $this->jobParametersMock = $this->createMock(JobParameters::class);

        $this->stepExecutionMock
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->valueConverterMock = $this->createMock(ConverterInterface::class);
        $this->valueConverterMock->method('convert')->willReturnArgument(0);

        $this->draftWriter = new DraftWriter(
            $this->versionManagerMock,
            $this->entitySaverMock,
            $this->standardNormalizerMock,
            $this->draftSaverMock,
            $this->baseEntityCreatorMock,
            $this->draftCreatorMock,
            $this->accessCheckerMock,
            $this->valueConverterMock
        );

        $this->draftWriter->setStepExecution($this->stepExecutionMock);
        $this->draftWriter->setUser($this->userMock);

        $this->standardNormalizerMock
            ->method('normalize')
            ->willReturn(
                [
                    PropertiesNormalizer::FIELD_IDENTIFIER => 'IDENTIFIER1',
                    PropertiesNormalizer::FIELD_FAMILY     => 'FAMILY',
                    PropertiesNormalizer::FIELD_PARENT     => null,
                    PropertiesNormalizer::FIELD_GROUPS     => [],
                    PropertiesNormalizer::FIELD_CATEGORIES => [
                        'GHSC_PSM',
                    ],
                    PropertiesNormalizer::FIELD_ENABLED    => true,
                    PropertiesNormalizer::FIELD_VALUES     => [
                        'LABELING'          => [
                            [
                                'locale' => null,
                                'scope'  => null,
                                'data'   => 'LABEL',
                            ],
                        ],
                        'COUNTRY_OF_ORIGIN' => [
                            [
                                'locale' => null,
                                'scope'  => null,
                                'data'   => 'PL',
                            ],
                        ],
                    ],
                    PropertiesNormalizer::FIELD_CREATED    => '2020-01-31T09:48:25+00:00',
                    PropertiesNormalizer::FIELD_UPDATED    => '2020-02-03T08:33:41+00:00',
                ]
            );
    }

    public function dataWrite(): array
    {
        return [
            'with_product_items'                             => [
                'items' => [
                    (new ProductBuilder())->build(),
                    (new ProductBuilder())->build(),
                    (new ProductBuilder())->build(),
                ],
            ],
            'with_product_items_when_all_are_variant'        => [
                'items' => [
                    (new ProductBuilder())->withParent((new ProductModelBuilder())->build())->build(),
                    (new ProductBuilder())->withParent((new ProductModelBuilder())->build())->build(),
                    (new ProductBuilder())->withParent((new ProductModelBuilder())->build())->build(),
                ],
            ],
            'with_product_items_when_all_has_id'             => [
                'items' => [
                    (new ProductBuilder())->withId(1)->build(),
                    (new ProductBuilder())->withId(2)->build(),
                    (new ProductBuilder())->withId(3)->build(),
                ],
            ],
            'with_product_model_items'                       => [
                'items' => [
                    (new ProductModelBuilder())->build(),
                    (new ProductModelBuilder())->build(),
                    (new ProductModelBuilder())->build(),
                ],
            ],
            'with_product_model_items_when_all_are_not_root' => [
                'items' => [
                    (new ProductModelBuilder())->withParent((new ProductModelBuilder())->build())->build(),
                    (new ProductModelBuilder())->withParent((new ProductModelBuilder())->build())->build(),
                    (new ProductModelBuilder())->withParent((new ProductModelBuilder())->build())->build(),
                ],
            ],
            'with_product_model_items_when_all_has_id'       => [
                'items' => [
                    (new ProductModelBuilder())->withId(1)->build(),
                    (new ProductModelBuilder())->withId(2)->build(),
                    (new ProductModelBuilder())->withId(3)->build(),
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataWrite
     */
    public function testWrite(array $items): void
    {
        $this->accessCheckerMock->method('hasAccessToProduct')->willReturn(true);

        $this->baseEntityCreatorMock
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$items);

        $this->draftSaverMock
            ->expects($this->exactly(count($items)))
            ->method('save');

        $this->draftWriter->write($items);
    }

    /**
     * @dataProvider dataWrite
     */
    public function testWriteThrowsExceptionWhenNoAccess(array $items): void
    {
        $this->accessCheckerMock->method('hasAccessToProduct')->willReturn(false);

        $this->stepExecutionMock
            ->expects($this->exactly(count($items)))
            ->method('addWarning');

        $this->draftWriter->write($items);
    }

    public function testInitialize(): void
    {
        $this->versionManagerMock
            ->expects($this->once())
            ->method('setRealTimeVersioning');

        $this->draftWriter->initialize();
    }

    public function testWriteWhenDraftSaverThrowsInvalidArgumentException(): void
    {
        $this->accessCheckerMock->method('hasAccessToProduct')->willReturn(true);

        $items = [
            (new ProductModelBuilder())->build(),
            (new ProductModelBuilder())->build(),
            (new ProductModelBuilder())->build(),
        ];

        $exception = $this->createMock(\InvalidArgumentException::class);

        $this->baseEntityCreatorMock
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$items);

        $this->draftSaverMock
            ->method('save')
            ->willThrowException($exception);

        $this->stepExecutionMock
            ->expects($this->exactly(count($items)))
            ->method('addWarning');

        $this->draftWriter->write($items);
    }

    public function testWriteWhenDraftSaverThrowsDraftViolationException(): void
    {
        $this->accessCheckerMock->method('hasAccessToProduct')->willReturn(true);

        $items = [
            (new ProductModelBuilder())->build(),
            (new ProductModelBuilder())->build(),
            (new ProductModelBuilder())->build(),
        ];

        $constraintViolationMock = $this->createMock(ConstraintViolation::class);
        $constraintViolationMock->expects($this->atLeastOnce())->method('getMessage');

        $constraintViolationList = new ConstraintViolationList([$constraintViolationMock]);

        $exceptionMock = $this->createMock(DraftViolationException::class);
        $exceptionMock->method('getViolations')->willReturn($constraintViolationList);

        $this->baseEntityCreatorMock
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$items);

        $this->draftSaverMock
            ->method('save')
            ->willThrowException($exceptionMock);

        $this->stepExecutionMock
            ->expects($this->exactly(count($items)))
            ->method('addWarning');

        $this->draftWriter->write($items);
    }
}