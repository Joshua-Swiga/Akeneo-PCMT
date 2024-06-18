<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use PcmtRulesBundle\Service\SelectOptionsRemover;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeOptionBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SelectOptionsRemoverTest extends TestCase
{
    /** @var RemoverInterface|MockObject */
    private $optionRemoverMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    protected function setUp(): void
    {
        $this->optionRemoverMock = $this->createMock(RemoverInterface::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
    }

    public function testRemove(): void
    {
        $attribute = (new AttributeBuilder())
            ->withType('pim_catalog_simpleselect')
            ->withOption((new AttributeOptionBuilder())->build())
            ->withOption((new AttributeOptionBuilder())->build())
            ->build();

        $this->stepExecutionMock->expects($this->exactly(2))->method('incrementSummaryInfo');
        $this->optionRemoverMock->expects($this->exactly(2))->method('remove');

        $remover = $this->getSelectOptionsRemoverInstance();
        $remover->remove($this->stepExecutionMock, $attribute);

        $this->assertEquals(0, $attribute->getOptions()->count());
    }

    private function getSelectOptionsRemoverInstance(): SelectOptionsRemover
    {
        return new SelectOptionsRemover(
            $this->optionRemoverMock
        );
    }
}