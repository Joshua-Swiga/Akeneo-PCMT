<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Service\ConcatenatedAttribute;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PcmtCoreBundle\Entity\ConcatenatedProperty;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ObjectUpdater implements ObjectUpdaterInterface
{
    private const IS_MISSING = ' [missing]';
    private const IS_EMPTY = ' [empty]';

    /** @var ObjectUpdaterInterface */
    private $entityWithValuesUpdater;

    /** @var NormalizerInterface */
    private $rawValuesStorageFormatNormalizer;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    public function __construct(
        ObjectUpdaterInterface $entityWithValuesUpdater,
        NormalizerInterface $rawValuesStorageFormatNormalizer,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->entityWithValuesUpdater = $entityWithValuesUpdater;
        $this->rawValuesStorageFormatNormalizer = $rawValuesStorageFormatNormalizer;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function update($object, array $data, array $options = []): void
    {
        $this->validateData($object, $data);

        /** @var AttributeInterface $concatenatedAttribute */
        $concatenatedAttribute = $data['concatenatedAttribute'];
        foreach ($data['memberAttributes'] as $memberAttribute) {
            /** @var AttributeInterface $memberAttribute */
            if (!$memberAttribute instanceof AttributeInterface) {
                throw new \InvalidArgumentException(
                    'Wrong type passed for update. ' . AttributeInterface::class . ' expected.'
                );
            }
        }

        if ($concatenatedAttribute->isScopable()) {
            $scopes = $this->channelRepository->getChannelCodes();
        }
        if ($concatenatedAttribute->isLocalizable()) {
            $locales = $this->localeRepository->getActivatedLocaleCodes();
        }

        $scopes = $scopes ?? [null];
        $locales = $locales ?? [null];

        foreach ($scopes as $scope) {
            foreach ($locales as $locale) {
                $this->updateWithSpecificScopeAndLocale($object, $concatenatedAttribute, $scope, $locale);
            }
        }

        $this->computeRawValues($object);
    }

    private function computeRawValues(object $object): void
    {
        $values = $this->getValues($object);
        $rawValues = $this->rawValuesStorageFormatNormalizer->normalize($values, 'storage');
        $object->setRawValues($rawValues);
    }

    private function getValues(EntityWithValuesInterface $entity): WriteValueCollection
    {
        if ($entity instanceof ProductModelInterface) {
            return $entity->getValuesForVariation();
        }

        if ($entity instanceof ProductInterface) {
            return $entity->getValuesForVariation();
        }

        return $entity->getValues();
    }

    private function validateData(object $object, array $data): void
    {
        if (!array_key_exists('concatenatedAttribute', $data) ||
            (!$data['concatenatedAttribute'] instanceof AttributeInterface ||
                PcmtAtributeTypes::BACKEND_TYPE_CONCATENATED === !$data['concatenatedAttribute']->getType())) {
            throw new \InvalidArgumentException('Missing concatenated attribute to update.');
        }

        if (!array_key_exists('memberAttributes', $data) || !is_array($data['memberAttributes'])) {
            throw new \InvalidArgumentException('Missing member attributes key.');
        }

        if (!($object instanceof ProductInterface || $object instanceof ProductModelInterface)) {
            throw new \InvalidArgumentException('Improper data passed for concatenated attribute update.');
        }
    }

    private function updateWithSpecificScopeAndLocale(
        EntityWithValuesInterface $objectToUpdate,
        AttributeInterface $concatenatedAttribute,
        ?string $scope = null,
        ?string $locale = null
    ): void {
        $property = new ConcatenatedProperty();
        $property->updateFromAttribute($concatenatedAttribute);

        $finalValue = '';

        $separators = $property->getSeparators();
        $attributeCodes = $property->getAttributeCodes();
        foreach ($attributeCodes as $key => $attributeCode) {
            $finalValue .= $this->getValue($objectToUpdate, $attributeCode, $locale, $scope);
            if (!empty($separators[$key])) {
                $finalValue .= $separators[$key];
            }
        }

        $valuesToUpdate[$concatenatedAttribute->getCode()]['data'] = [
            'data'   => [$finalValue],
            'scope'  => $scope,
            'locale' => $locale,
        ];
        $this->entityWithValuesUpdater->update($objectToUpdate, $valuesToUpdate);
    }

    private function getValue(EntityWithValuesInterface $object, string $attributeCode, ?string $locale, ?string $scope): string
    {
        if (!$attributeCode) {
            return '';
        }
        if (!$object->hasAttribute($attributeCode)) {
            return $attributeCode . ' ' . self::IS_MISSING;
        }
        $productValue = $object->getValue($attributeCode, $locale, $scope);
        $stringValue = $productValue->__toString();
        if ($stringValue) {
            return $stringValue;
        }

        return $attributeCode . ' ' . self::IS_EMPTY;
    }
}