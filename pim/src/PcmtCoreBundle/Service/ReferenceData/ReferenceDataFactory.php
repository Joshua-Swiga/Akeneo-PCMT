<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Service\ReferenceData;

use PcmtCoreBundle\Entity\ReferenceData\AdditionalTradeItemClassificationCodeListCode;
use PcmtCoreBundle\Entity\ReferenceData\AdditionalTradeItemIdentificationTypeCode;
use PcmtCoreBundle\Entity\ReferenceData\ColourCodeListCode;
use PcmtCoreBundle\Entity\ReferenceData\CountryCode;
use PcmtCoreBundle\Entity\ReferenceData\DataCarrierTypeCode;
use PcmtCoreBundle\Entity\ReferenceData\GDSNMeasurementUnitCode;
use PcmtCoreBundle\Entity\ReferenceData\Gs1TradeItemIdentificationKeyCode;
use PcmtCoreBundle\Entity\ReferenceData\ImportClassificationTypeCode;
use PcmtCoreBundle\Entity\ReferenceData\LanguageCode;
use PcmtCoreBundle\Entity\ReferenceData\NonfoodIngredientOfConcernCode;
use PcmtCoreBundle\Entity\ReferenceData\PackageTypeCode;
use PcmtCoreBundle\Entity\ReferenceData\PlatformTypeCode;
use PcmtCoreBundle\Entity\ReferenceData\ReferencedFileTypeCode;
use PcmtCoreBundle\Entity\ReferenceData\RegulationTypeCode;
use PcmtCoreBundle\Entity\ReferenceData\ShippingContainerTypeCode;
use PcmtCoreBundle\Entity\ReferenceData\SizeCodeListCode;
use PcmtCoreBundle\Entity\ReferenceData\TemperatureQualifierCode;
use PcmtCoreBundle\Entity\ReferenceData\TradeItemUnitDescriptorCode;

class ReferenceDataFactory
{
    public function getReferenceDataClass(string $className): ?string
    {
        switch ($className) {
            case 'AdditionalTradeItemClassificationCodeListCode':
                return AdditionalTradeItemClassificationCodeListCode::class;
            case 'AdditionalTradeItemIdentificationTypeCode':
                return AdditionalTradeItemIdentificationTypeCode::class;
            case 'ColourCodeListCode':
                return ColourCodeListCode::class;
            case 'CountryCode':
                return CountryCode::class;
            case 'DataCarrierTypeCode':
                return DataCarrierTypeCode::class;
            case 'GS1TradeItemIdentificationKeyTypeCode':
                return Gs1TradeItemIdentificationKeyCode::class;
            case 'ImportClassificationTypeCode':
                return ImportClassificationTypeCode::class;
            case 'LanguageCode':
                return LanguageCode::class;
            case 'MeasurementUnitCode_GDSN':
                return GDSNMeasurementUnitCode::class;
            case 'NonfoodIngredientOfConcernCode':
                return NonfoodIngredientOfConcernCode::class;
            case 'PackageTypeCode':
                return PackageTypeCode::class;
            case 'PlatformTypeCode':
                return PlatformTypeCode::class;
            case 'ReferencedFileTypeCode':
                return ReferencedFileTypeCode::class;
            case 'ShippingContainerTypeCode':
                return ShippingContainerTypeCode::class;
            case 'SizeCodeListCode':
                return SizeCodeListCode::class;
            case 'TemperatureQualifierCode':
                return TemperatureQualifierCode::class;
            case 'TradeItemUnitDescriptorCode':
                return TradeItemUnitDescriptorCode::class;
            case 'RegulationTypeCode':
                return RegulationTypeCode::class;
            default:
                return null;
        }
    }
}