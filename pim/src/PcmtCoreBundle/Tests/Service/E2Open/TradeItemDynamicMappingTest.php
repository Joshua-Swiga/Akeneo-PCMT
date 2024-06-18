<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\E2Open;

use PcmtCoreBundle\Entity\E2OpenAttributeData;
use PcmtCoreBundle\Service\E2Open\TradeItemDynamicMapping;
use PHPUnit\Framework\TestCase;

class TradeItemDynamicMappingTest extends TestCase
{
    protected function setUp(): void
    {
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcess(array $inputData, array $expectedData): void
    {
        $tradeItemDynamicMapping = $this->getTradeItemDynamicMappingInstance();
        $data = $tradeItemDynamicMapping->process($inputData);

        $this->assertEquals($expectedData, $data);
    }

    public function dataProcess(): array
    {
        $cases = [];

        $data1 = [
            'GS1_PACKAGINGTYPECODE' => new E2OpenAttributeData('{}packagingTypeCode', 'GS1_PACKAGINGTYPECODE', 'BPG', []),
        ];
        $cases[] = [
            $data1,
            $data1,
        ];

        $data2 = [
            'GS1_PACKAGINGTYPECODE'         => new E2OpenAttributeData('{}packagingTypeCode', 'GS1_PACKAGINGTYPECODE', 'BPG', []),
            'GS1_REFERENCEDFILETYPECODE'    => new E2OpenAttributeData('{}referencedFileTypeCode', 'GS1_REFERENCEDFILETYPECODE', 'PRODUCT_IMAGE', []),
            'GS1_UNIFORMRESOURCEIDENTIFIER' => new E2OpenAttributeData('{}uniformResourceIdentifier', 'GS1_UNIFORMRESOURCEIDENTIFIER', 'yyy', []),
        ];
        $expectedData2 = [
            'GS1_PACKAGINGTYPECODE'                       => new E2OpenAttributeData('{}packagingTypeCode', 'GS1_PACKAGINGTYPECODE', 'BPG', []),
            'GS1_REFERENCEDFILETYPECODE'                  => new E2OpenAttributeData('{}referencedFileTypeCode', 'GS1_REFERENCEDFILETYPECODE', 'PRODUCT_IMAGE', []),
            'GS1_UNIFORMRESOURCEIDENTIFIER_PRODUCT_IMAGE' => new E2OpenAttributeData('{}uniformResourceIdentifier', 'GS1_UNIFORMRESOURCEIDENTIFIER_PRODUCT_IMAGE', 'yyy', []),
        ];
        $cases[] = [
            $data2,
            $expectedData2,
        ];

        $data3 = [
            'GS1_PACKAGINGTYPECODE'                           => new E2OpenAttributeData('{}packagingTypeCode', 'GS1_PACKAGINGTYPECODE', 'BPG', []),
            'GS1_ADDITIONALTRADEITEMCLASSIFICATIONSYSTEMCODE' => new E2OpenAttributeData('{}additionalTradeItemClassificationSystemCode', 'GS1_ADDITIONALTRADEITEMCLASSIFICATIONSYSTEMCODE', '69', []),
            'GS1_ADDITIONALTRADEITEMCLASSIFCATIONCODEVALUE'   => new E2OpenAttributeData('{}additionalTradeItemClassificationCodeValue', 'GS1_ADDITIONALTRADEITEMCLASSIFCATIONCODEVALUE', 'yyy', []),
        ];
        $expectedData3 = [
            'GS1_PACKAGINGTYPECODE'                                  => new E2OpenAttributeData('{}packagingTypeCode', 'GS1_PACKAGINGTYPECODE', 'BPG', []),
            'GS1_ADDITIONALTRADEITEMCLASSIFICATIONSYSTEMCODE'        => new E2OpenAttributeData('{}additionalTradeItemClassificationSystemCode', 'GS1_ADDITIONALTRADEITEMCLASSIFICATIONSYSTEMCODE', '69', []),
            'GS1_ADDITIONALTRADEITEMCLASSIFCATIONCODEVALUE_INN_CODE' => new E2OpenAttributeData('{}additionalTradeItemClassificationCodeValue', 'GS1_ADDITIONALTRADEITEMCLASSIFCATIONCODEVALUE_INN_CODE', 'yyy', []),
        ];
        $cases[] = [
            $data3,
            $expectedData3,
        ];

        return $cases;
    }

    private function getTradeItemDynamicMappingInstance(): TradeItemDynamicMapping
    {
        return new TradeItemDynamicMapping();
    }
}
