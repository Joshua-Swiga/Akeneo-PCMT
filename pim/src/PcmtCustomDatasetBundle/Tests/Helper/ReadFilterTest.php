<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Tests\Helper;

use PcmtCustomDatasetBundle\Helper\ReadFilter;
use PHPUnit\Framework\TestCase;

class ReadFilterTest extends TestCase
{
    /** @var ReadFilter */
    private $readFilter;

    /** @var string */
    private $columnToFilter;

    /** @var string */
    private $columnNotToFilter;

    protected function setUp(): void
    {
        $this->columnToFilter = 'ColumnToFilter';
        $this->columnNotToFilter = 'ColumnNotToFilter';
        $this->readFilter = new ReadFilter($this->columnToFilter);
    }

    /**
     * @dataProvider dataReadCell
     */
    public function testReadCell(string $column, int $row, bool $expectedResult): void
    {
        $result = $this->readFilter->readCell($column, $row, 'Some Text');
        $this->assertSame($result, $expectedResult);
    }

    public function dataReadCell(): array
    {
        $this->setUp();

        return [
            'first row from column to filter'     => [$this->columnToFilter, 1, true],
            'next row from column to filter'      => [$this->columnToFilter, 2, false],
            'first row from column not to filter' => [$this->columnNotToFilter, 1, true],
            'next row from column not to filter'  => [$this->columnNotToFilter, 2, true],
        ];
    }
}