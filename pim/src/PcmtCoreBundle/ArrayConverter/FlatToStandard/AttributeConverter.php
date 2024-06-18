<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * @override: Handle localizable attribute description in flat array to standard array conversion
 */
class AttributeConverter implements ArrayConverterInterface
{
    /** @var FieldsRequirementChecker */
    protected $fieldChecker;

    /** @var ArrayConverterInterface */
    private $baseAttributeConverter;

    /** @var string[] */
    public $supportedFields = ['concatenated'];

    public function __construct(
        FieldsRequirementChecker $fieldChecker,
        ArrayConverterInterface $baseAttributeConverter
    ) {
        $this->fieldChecker = $fieldChecker;
        $this->baseAttributeConverter = $baseAttributeConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldChecker->checkFieldsPresence($item, ['code']);
        $this->fieldChecker->checkFieldsFilling($item, ['code']);

        $convertedItem = [
            'descriptions' => [],
        ];

        $tmpItem = [
            'code' => $item['code'],
        ];
        foreach ($item as $field => $data) {
            if ($this->supportField($field)) {
                $convertedItem = $this->convertFields($field, $data, $convertedItem);
            } else {
                $tmpItem[$field] = $data;
            }
        }

        return array_merge(
            $convertedItem,
            $this->baseAttributeConverter->convert($tmpItem, $options)
        );
    }

    /**
     * @param int|string|null $data
     */
    public function convertFields(string $field, $data, array $convertedItem): array
    {
        if ('concatenated' === $field) {
            return $convertedItem;
        }
        if (false !== mb_strpos($field, 'description-', 0)) {
            $descriptionTokens = explode('-', $field);
            $descriptionLocale = $descriptionTokens[1];
            $convertedItem['descriptions'][$descriptionLocale] = $data;
        }

        return $convertedItem;
    }

    public function supportField(string $field): bool
    {
        return in_array($field, $this->supportedFields) || false !== mb_strpos($field, 'description-', 0);
    }
}
