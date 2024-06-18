<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\Connector\Job\Tasklet;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PcmtPermissionsBundle\Connector\Job\Tasklet\CheckUserAccessToTheCategoryTasklet;
use PcmtPermissionsBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckUserAccessToTheCategoryTaskletTest extends TestCase
{
    /** @var CheckUserAccessToTheCategoryTasklet */
    private $tasklet;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var JobParameters|MockObject */
    private $jobParametersMock;

    /** @var ProductRepositoryInterface|MockObject */
    private $productRepositoryMock;

    /** @var ProductModelRepositoryInterface|MockObject */
    private $productModelRepositoryMock;

    /** @var CategoryPermissionsCheckerInterface|MockObject */
    private $categoryPermissionsCheckerMock;

    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->productModelRepositoryMock = $this->createMock(
            ProductModelRepositoryInterface::class
        );
        $this->categoryPermissionsCheckerMock = $this->createMock(
            CategoryPermissionsCheckerInterface::class
        );

        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);

        $this->tasklet = new CheckUserAccessToTheCategoryTasklet(
            $this->productRepositoryMock,
            $this->productModelRepositoryMock,
            $this->categoryPermissionsCheckerMock
        );

        $this->stepExecutionMock
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);
    }

    public function testExecuteWhenStepExecutionWasNotSet(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->tasklet->execute();
    }

    public function testExecute(): void
    {
        $filters = [
            [
                'field'    => 'id',
                'value'    => [
                    'product_8155',
                    'product_8156',
                    'product_8157',
                    'product_model_100',
                    'product_model_101',
                ],
                'context'  => [
                    'scope'  => 'LMIS',
                    'locale' => 'en_US',
                ],
                'operator' => 'IN',
            ],
        ];

        $productWithId8155 = (new ProductBuilder())
            ->withId(8155)
            ->build();

        $productWithId8156 = (new ProductBuilder())
            ->withId(8156)
            ->build();

        $productWithId8157 = (new ProductBuilder())
            ->withId(8157)
            ->build();

        $productModelWithId100 = (new ProductModelBuilder())
            ->withId(100)
            ->build();

        $productModelWithId101 = (new ProductModelBuilder())
            ->withId(101)
            ->build();

        $this->productRepositoryMock
            ->method('findBy')
            ->willReturn(
                [
                    $productWithId8155,
                    $productWithId8156,
                    $productWithId8157,
                ]
            );

        $this->productModelRepositoryMock
            ->method('findBy')
            ->willReturn(
                [
                    $productModelWithId100,
                    $productModelWithId101,
                ]
            );

        $this->categoryPermissionsCheckerMock
            ->method('hasAccessToProduct')
            ->willReturnOnConsecutiveCalls(true, false, false, true, false);

        $this->jobParametersMock
            ->method('get')
            ->with('filters')
            ->willReturn($filters);

        $this->tasklet->setStepExecution($this->stepExecutionMock);

        $this->jobParametersMock
            ->expects($this->once())
            ->method('set')
            ->with(
                'filters',
                [
                    [
                        'field'    => 'id',
                        'value'    => [
                            'product_8155',
                            'product_model_100',
                        ],
                        'context'  => [
                            'scope'  => 'LMIS',
                            'locale' => 'en_US',
                        ],
                        'operator' => 'IN',
                    ],
                ]
            );

        $this->tasklet->execute();
    }
}
