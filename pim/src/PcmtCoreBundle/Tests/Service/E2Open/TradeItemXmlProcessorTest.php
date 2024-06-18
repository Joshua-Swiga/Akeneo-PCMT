<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\E2Open;

use PcmtCoreBundle\Entity\E2OpenAttributeData;
use PcmtCoreBundle\Service\E2Open\TradeItemXmlProcessor;
use PHPUnit\Framework\TestCase;

class TradeItemXmlProcessorTest extends TestCase
{
    protected function setUp(): void
    {
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcess(array $node, array $expectedData): void
    {
        $tradeItemProcessor = $this->getTradeItemXmlProcessorInstance();
        $tradeItemProcessor->processNode($node);

        $data = $tradeItemProcessor->getFoundAttributes();

        $this->assertEquals($expectedData, $data);
    }

    public function dataProcess(): array
    {
        return [
            [
                [
                    'name'       => '{}packaging',
                    'value'      => [
                        0 => [
                            'name'       => '{}packagingTypeCode',
                            'value'      => 'BPG',
                            'attributes' => [],
                        ],
                    ],
                    'attributes' => [
                        '{}packagingTypeCode' => 'BPG',
                    ],
                ],
                [
                    'GS1_PACKAGINGTYPECODE' => new E2OpenAttributeData('{}packagingTypeCode', 'GS1_PACKAGINGTYPECODE', 'BPG', []),
                ],
            ],
            [
                [
                    'name'       => '{}tradeItemSynchronisationDates',
                    'value'      => [
                        0 => [
                            'name'       => '{}effectiveDateTime',
                            'value'      => '2019-11-26T00:00:00',
                            'attributes' => [],
                        ],
                    ],
                    'attributes' => [
                        '{}effectiveDateTime' => '2019-11-26T00:00:00',
                    ],
                ],
                [
                    'GS1_EFFECTIVEDATETIME' => new E2OpenAttributeData('{}effectiveDateTime', 'GS1_EFFECTIVEDATETIME', '2019-11-26T00:00:00', []),
                ],
            ],
        ];
    }

    private function getTradeItemXmlProcessorInstance(): TradeItemXmlProcessor
    {
        return new TradeItemXmlProcessor();
    }
}
