<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AbstractAttributeCopier;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DifferentTypesAttributeCopier extends AbstractAttributeCopier
{
    /** @var NormalizerInterface */
    protected $normalizer;

    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        NormalizerInterface $normalizer,
        array $supportedFromTypes,
        array $supportedToTypes
    ) {
        parent::__construct($entityWithValuesBuilder, $attrValidatorHelper);

        $this->normalizer = $normalizer;
        $this->supportedFromTypes = $supportedFromTypes;
        $this->supportedToTypes = $supportedToTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function copyAttributeData(
        EntityWithValuesInterface $fromEntityWithValues,
        EntityWithValuesInterface $toEntityWithValues,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        array $options = []
    ): void {
        $options = $this->resolver->resolve($options);
        $fromLocale = $options['from_locale'];
        $toLocale = $options['to_locale'];
        $fromScope = $options['from_scope'];
        $toScope = $options['to_scope'];

        $this->checkLocaleAndScope($fromAttribute, $fromLocale, $fromScope);
        $this->checkLocaleAndScope($toAttribute, $toLocale, $toScope);

        $this->copySingleValue(
            $fromEntityWithValues,
            $toEntityWithValues,
            $fromAttribute,
            $toAttribute,
            $fromLocale,
            $toLocale,
            $fromScope,
            $toScope
        );
    }

    /**
     * Copies single value.
     */
    protected function copySingleValue(
        EntityWithValuesInterface $fromEntityWithValues,
        EntityWithValuesInterface $toEntityWithValues,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ?string $fromLocale,
        ?string $toLocale,
        ?string $fromScope,
        ?string $toScope
    ): void {
        $fromValue = $fromEntityWithValues->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        if (null !== $fromValue) {
            $standardData = $this->normalizer->normalize($fromValue, 'standard');
            if ('pim_catalog_simpleselect' === $toAttribute->getType()) {
                if (!$this->checkIfOptionExists($standardData['data'], $toAttribute)) {
                    throw new \Exception(sprintf(
                        'Attribute %s does not have option: %s.',
                        $toAttribute->getCode(),
                        $standardData['data']
                    ));
                }
            }

            $this->entityWithValuesBuilder->addOrReplaceValue(
                $toEntityWithValues,
                $toAttribute,
                $toLocale,
                $toScope,
                $standardData['data']
            );
        }
    }

    private function checkIfOptionExists(string $code, AttributeInterface $attribute): bool
    {
        $attribute->getOptions();
        foreach ($attribute->getOptions() as $option) {
            if ($code === $option->getCode()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttributes(AttributeInterface $fromAttribute, AttributeInterface $toAttribute)
    {
        $supportsFrom = in_array($fromAttribute->getType(), $this->supportedFromTypes);
        $supportsTo = in_array($toAttribute->getType(), $this->supportedToTypes);

        return $supportsFrom && $supportsTo;
    }
}