<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\ArrayConverter\StandardToFlat;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

class AttributeConverter implements ArrayConverterInterface
{
    /** @var ArrayConverterInterface */
    private $baseAttributeConverter;

    /** @var string[] */
    private $supportedFields = ['concatenated', 'descriptions'];

    public function __construct(ArrayConverterInterface $baseAttributeConverter)
    {
        $this->baseAttributeConverter = $baseAttributeConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = [];

        $tmpItem = [];

        foreach ($item as $property => $data) {
            if ($this->supportField($property)) {
                $convertedItem = $this->convertProperty($property, $data, $convertedItem);
            } else {
                $tmpItem[$property] = $data;
            }
        }

        return array_merge(
            $convertedItem,
            $this->baseAttributeConverter->convert($tmpItem, $options)
        );
    }

    /**
     * @param array|int|string|null $data
     */
    private function convertProperty(string $property, $data, array $convertedItem): array
    {
        switch ($property) {
            case 'descriptions':
                foreach ($data as $localeCode => $description) {
                    $descriptionKey = sprintf('description-%s', $localeCode);
                    $convertedItem[$descriptionKey] = $description;
                }
                break;
            case 'concatenated':
                break;
        }

        return $convertedItem;
    }

    private function supportField(string $field): bool
    {
        return in_array($field, $this->supportedFields);
    }
}
