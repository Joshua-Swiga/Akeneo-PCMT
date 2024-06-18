<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Service\E2Open;

use PcmtCoreBundle\Entity\E2OpenAttributeData;

class TradeItemDynamicMapping
{
    public const MAPPING_DEFINITION = [
        'GS1_REFERENCEDFILETYPECODE' => [
            'targetAttribute' => 'GS1_UNIFORMRESOURCEIDENTIFIER',
            'mapping'         => [
                'PRODUCT_IMAGE'        => 'GS1_UNIFORMRESOURCEIDENTIFIER_PRODUCT_IMAGE',
                'CERTIFICATION'        => 'GS1_UNIFORMRESOURCEIDENTIFIER_CERTIFICATION',
                'PRODUCT_LABEL_IMAGE'  => 'GS1_UNIFORMRESOURCEIDENTIFIER_PRODUCT_LABEL_IMAGE',
                'PRODUCT_WEBSITE'      => 'GS1_UNIFORMRESOURCEIDENTIFIER_PRODUCT_WEBSITE',
                'SAFETY_DATA_SHEET'    => 'GS1_UNIFORMRESOURCEIDENTIFIER_SAFETY_DATA_SHEET',
                'QUALITY_CONTROL_PLAN' => 'GS1_UNIFORMRESOURCEIDENTIFIER_QUALTIY_CONTROL',
                'VIDEO'                => 'GS1_UNIFORMRESOURCEIDENTIFIER_VIDEO',
            ],
        ],
        'GS1_ADDITIONALTRADEITEMCLASSIFICATIONSYSTEMCODE' => [
            'targetAttribute' => 'GS1_ADDITIONALTRADEITEMCLASSIFCATIONCODEVALUE',
            'mapping'         => [
                'UNSPSC' => 'GS1_ADDITIONALTRADEITEMCLASSIFCATIONCODEVALUE_UNSPSC',
                '69'     => 'GS1_ADDITIONALTRADEITEMCLASSIFCATIONCODEVALUE_INN_CODE',
                'INN'    => 'GS1_ADDITIONALTRADEITEMCLASSIFCATIONCODEVALUE_INN_CODE',
                'ATC'    => 'GS1_ADDITIONALTRADEITEMCLASSIFCATIONCODEVALUE_ATC_CODE',
            ],
        ],
    ];

    /**
     * @param E2OpenAttributeData[] $data
     */
    public function process(array $data): array
    {
        foreach (self::MAPPING_DEFINITION as $attributeCode => $mappingDefinition) {
            if (!empty($data[$attributeCode]) && !empty($data[$mappingDefinition['targetAttribute']])) {
                $mainElement = $data[$attributeCode];
                $targetElement = $data[$mappingDefinition['targetAttribute']];

                if (!empty($mappingDefinition['mapping'][$mainElement->getValue()])) {
                    $newCode = $mappingDefinition['mapping'][$mainElement->getValue()];
                    $targetElement->setCode($newCode);
                    unset($data[$mappingDefinition['targetAttribute']]);
                    $data[$newCode] = $targetElement;
                }
            }
        }

        return $data;
    }
}