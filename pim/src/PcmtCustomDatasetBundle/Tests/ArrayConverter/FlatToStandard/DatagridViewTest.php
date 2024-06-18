<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Tests\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use PcmtCustomDatasetBundle\ArrayConverter\FlatToStandard\DatagridView;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatagridViewTest extends TestCase
{
    /** @var DatagridView */
    private $datagridViewArrayConverter;

    /** @var FieldsRequirementChecker|MockObject */
    private $fieldChecker;

    protected function setUp(): void
    {
        $this->fieldChecker = $this->createMock(FieldsRequirementChecker::class);
        $this->datagridViewArrayConverter = new DatagridView($this->fieldChecker);
    }

    public function testConvert(): void
    {
        $item = [
            'label'          => 1,
            'owner'          => 2,
            'datagrid_alias' => 3,
            'columns'        => 4,
        ];
        $requiredArray = [
            'label',
            'owner',
            'datagrid_alias',
            'columns',
        ];
        $this->fieldChecker->expects($this->once())->method('checkFieldsPresence')
            ->with($item, $requiredArray);
        $this->fieldChecker->expects($this->once())->method('checkFieldsFilling')
            ->with($item, $requiredArray);
        $this->datagridViewArrayConverter->convert($item);
    }
}