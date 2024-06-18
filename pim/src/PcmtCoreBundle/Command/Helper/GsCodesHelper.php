<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Command\Helper;

final class GsCodesHelper
{
    public static function getGsCodes(): array
    {
        return [
            'AdditionalTradeItemClassificationCodeListCode',
            'AdditionalTradeItemIdentificationTypeCode',
            'ColourCodeListCode',
            'CountryCode',
            'DataCarrierTypeCode',
            'GDSNMeasurementUnitCode',
            'Gs1TradeItemIdentificationKeyCode',
            'ImportClassificationTypeCode',
            'LanguageCode',
            'NonfoodIngredientOfConcernCode',
            'PackageTypeCode',
            'PlatformTypeCode',
            'ReferencedFileTypeCode',
            'RegulationTypeCode',
            'RouteAdministration',
            'ShippingContainerTypeCode',
            'SizeCodeListCode',
            'TemperatureQualifierCode',
            'TradeItemUnitDescriptorCode',
        ];
    }
}