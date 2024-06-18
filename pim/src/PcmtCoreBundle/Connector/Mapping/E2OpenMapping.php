<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Mapping;

class E2OpenMapping
{
    public static function findMappingForKey(string $key): ?string
    {
        if (!array_key_exists($key, self::mapping())) {
            return null;
        }

        return self::mapping()[$key];
    }

    public static function getE2OpenAttributeNames(): array
    {
        return array_map(
            function ($name) {
                if (null !== $name && '' !== $name) {
                    return $name;
                }
            },
            array_values(self::mapping())
        );
    }

    /**
     * @return bool|string|null
     */
    public static function mapValue(?string $value)
    {
        $mapping = [
            'true'  => 1,
            'false' => 0,
        ];

        return $mapping[$value] ?? $value;
    }

    private static function mapping(): array
    {
        return [
            '{}additionalTradeItemIdentification'                      => 'GS1_ADDITIONALTRADEITEMIDENTIFICATION',
            '{}isTradeItemABaseUnit'                                   => 'GS1_ISTRADEITEMABASEUNIT',
            '{}isTradeItemAConsumerUnit'                               => 'GS1_ISTRADEITEMACONSUMERUNIT',
            '{}gtin'                                                   => 'GTIN',
            '{}isTradeItemADespatchUnit'                               => 'GS1_ISTRADEITEMADESPATCHUNIT',
            '{}isTradeItemAnInvoiceUnit'                               => 'GS1_ISTRADEITEMANINVOICEUNIT',
            '{}isTradeItemAnOrderableUnit'                             => 'GS1_ISTRADEITEMANORDERABLEUNIT',
            '{}isTradeItemAService'                                    => '',
            '{}isTradeItemNonphysical'                                 => '',
            '{}tradeItemUnitDescriptorCode'                            => 'GS1_TRADEITEMUNITDESCRIPTORCODE',
            '{}brandOwner{}gln'                                        => 'GS1_BRANDOWNERGLN',
            '{}manufacturerOfTradeItem{}gln'                           => 'GS1_MANUFACTUREROFTRADEITEMGLN',
            '{}informationProviderOfTradeItem{}gln'                    => 'GS1_INFORMATIONPROVIDEROFTRADEITEMGLN',
            '{}brandOwner{}partyName'                                  => 'GS1_BRANDOWNERPARTYNAME',
            '{}informationProviderOfTradeItem{}partyName'              => 'GS1_INFORMATIONPROVIDEROFTRADEITEMPARTYNAME',
            '{}manufacturerOfTradeItem{}partyName'                     => 'GS1_MANUFACTUREROFTRADEITEMPARTYNAME',
            '{}manufacturerOfTradeItem{}partyAddress'                  => 'GS1_MANUFACTUREROFTRADEITEMPARTYADDRESS',
            '{}gpcCategoryCode'                                        => 'GS1_GPCCATEGORYCODE',
            '{}gpcCategoryDefinition'                                  => '',
            '{}gpcCategoryName'                                        => '',
            '{}additionalTradeItemClassificationSystemCode'            => 'GS1_ADDITIONALTRADEITEMCLASSIFICATIONSYSTEMCODE',
            '{}additionalTradeItemClassificationCodeValue'             => 'GS1_ADDITIONALTRADEITEMCLASSIFICATIONCODEVALUE',
            '{}additionalTradeItemClassificationVersion'               => '',
            '{}targetMarketCountryCode'                                => 'GS1_TARGETMARKETCOUNTRYCODE',
            '{}doesItemContainAControlledSubstance'                    => 'GS1_DOESITEMCONTAINACONTROLLEDSUBSTANCE',
            '{}controlledSubstanceScheduleCodeReference'               => 'GS1_CONTROLLEDSUBSTANCESCHEDULECODEREFERENCE',
            '{}controlledSubstanceAmount'                              => 'GS1_CONTROLLEDSUBSTANCEAMOUNT',
            '{}controlledSubstanceCode'                                => 'GS1_CONTOLLEDSUBSTANCECODE',
            '{}controlledSubstanceName'                                => 'GS1_CONTROLLEDSUBSTANCENAME',
            '{}isDangerousSubstance'                                   => 'GS1_ISDANGEROUSSUBSTANCE',
            '{}startAvailabilityDateTime'                              => 'GS1_STARTAVAILABILITYDATETIME',
            '{}externalAgencyName'                                     => 'GS1_EXTERNALAGENCYNAME',
            '{}externalCodeListName'                                   => 'GS1_EXTERNALCODELISTNAME',
            '{}enumerationValue'                                       => 'GS1_ENUMERATIONVALUE',
            '{}enumerationValueDescription'                            => 'GS1_ENUMERATIONVALUEDESCRIPTION',
            '{}tradeItemFeatureBenefit'                                => '',
            '{}tradeItemMarketingMessage'                              => '',
            '{}packagingShapeCode'                                     => '',
            '{}packagingTypeCode'                                      => 'GS1_PACKAGINGTYPECODE',
            '{}dosageFormTypeCodeReference'                            => 'GS1_DOSAGEFORMTYPECODEREFERENCE',
            '{}countryCode'                                            => 'GS1_PLACEOFPRODUCTACTIVITYCOUNTRYOFORIGINCOUNTRYCODE',
            '{}referencedFileTypeCode'                                 => 'GS1_REFERENCEDFILETYPECODE',
            '{}fileEffectiveStartDateTime'                             => '',
            '{}uniformResourceIdentifier'                              => 'GS1_UNIFORMRESOURCEIDENTIFIER',
            '{}regulationTypeCode'                                     => 'GS1_REGULATORYINFORMATIONREGULATIONTYPECODE',
            '{}regulatoryAct'                                          => '',
            '{}regulatoryAgency'                                       => '',
            '{}regulationRestrictionsAndDescriptors'                   => 'GS1_REGULATIONRESTRICTIONSANDDESCRIPTORS',
            '{}permitEndDateTime'                                      => 'GS1_PERMITENDDATETIME',
            '{}permitStartDateTime'                                    => 'GS1_PERMITSTARTDATETIME',
            '{}regulatoryPermitIdentification'                         => 'GS1_REGULATORYPERMITIDENTIFICATION',
            '{}isRegulatedForTransportation'                           => 'GS1_SAFETYDATASHEETINFORMATIONISREGULATEDFORTRANSPORTATION',
            '{}gs1TradeItemIdentificationKeyCode'                      => 'GS1_GS1TRADEITEMIDENTIFICATIONKEYCODE',
            '{}gs1TradeItemIdentificationKeyValue'                     => 'GS1_GS1TRADEITEMIDENTIFICATIONKEYVALUE',
            '{}dataCarrierTypeCode'                                    => 'GS1_DATACARRIERTYPECODE',
            '{}descriptionShort'                                       => 'GS1_DESCRIPTIONSHORT',
            '{}functionalName'                                         => 'GS1_FUNCTIONALNAME',
            '{}tradeItemDescription'                                   => 'GS1_TRADEITEMDESCRIPTION',
            '{}brandName'                                              => 'GS1_BRANDNAME',
            '{}handlingInstructionsCodeReference'                      => 'GS1_HANDLINGINSTRUCTIONCODEREFERENCE',
            '{}minimumTradeItemLifespanFromTimeOfProduction'           => 'GS1_MINIMUMTRADEITEMLIFESPANFROMTIMEOFPRODUCTION',
            '{}depth'                                                  => 'GS1_DEPTH',
            '{}depthmeasurementUnitCode'                               => '',
            '{}height'                                                 => 'GS1_HEIGHT',
            '{}heightmeasurementUnitCode'                              => '',
            '{}inBoxCubeDimension'                                     => 'GS1_INBOXCUBEDIMENSION',
            '{}netContent'                                             => 'GS1_NETCONTENT',
            '{}width'                                                  => 'GS1_WIDTH',
            '{}widthmeasurementUnitCode'                               => '',
            '{}grossWeight'                                            => 'GS1_GROSSWEIGHT',
            '{}grossWeightmeasurementUnitCode'                         => '',
            '{}netWeight'                                              => 'GS1_NETWEIGHT',
            '{}maximumTemperature'                                     => 'GS1_MAXIMUMTEMPERATURE',
            '{}tradeItemTemperatureInformation{}maximumTemperature'    => 'GS1_MAXIMUMTEMPERATURE',
            '{}maximumTemperaturetemperatureMeasurementUnitCode'       => '',
            '{}minimumTemperature'                                     => 'GS1_MINIMUMTEMPERATURE',
            '{}tradeItemTemperatureInformation{}minimumTemperature'    => 'GS1_MINIMUMTEMPERATURE',
            '{}minimumTemperaturetemperatureMeasurementUnitCode'       => '',
            '{}temperatureQualifierCode'                               => '',
            '{}isTradeItemAVariableUnit'                               => 'GS1_IISTRADEITEMAVARIABLEUNIT',
            '{}value'                                                  => '',
            '{}attr'                                                   => '',
            '{}lastChangeDateTime'                                     => '',
            '{}effectiveDateTime'                                      => 'GS1_EFFECTIVEDATETIME',
            '{}publicationDateTime'                                    => '',
            '{}childTradeItem{}quantityOfChildren'                     => 'GS1_QUANTITYOFCHILDREN',
            '{}childTradeItem{}gtin'                                   => 'GS1_GTIN_CHILD_NEXTLOWERLEVELTRADEITEMINFORMATION',
            '{}childTradeItem{}quantityOfNextLowerLevelTradeItem'      => 'GS1_QUANTITYOFNEXTLOWERLEVELTRADEITEM',
            '{}childTradeItem{}totalQuantityOfNextLowerLevelTradeItem' => 'GS1_TOTALQUANTITYOFNEXTLOWERLEVELTRADEITEM',
            '{}diameter'                                               => 'GS1_DIAMETER',
        ];
    }
}