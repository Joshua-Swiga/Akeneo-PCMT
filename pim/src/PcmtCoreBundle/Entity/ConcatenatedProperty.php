<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Entity;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class ConcatenatedProperty
{
    public const DELIMITER = ',';

    public const SPECIAL_CODE_FOR_DELIMITER = 'X^%$()';

    /** @var string[] */
    private $attributeCodes = [];

    /** @var string[] */
    private $separators = [];

    public function getAttributeCodes(): array
    {
        return $this->attributeCodes;
    }

    public function getAttributeCodesCount(): int
    {
        return count($this->attributeCodes);
    }

    public function getSeparators(): array
    {
        return $this->separators;
    }

    public function getSeparatorsCount(): int
    {
        return count($this->separators);
    }

    private function decodeDelimiter(): void
    {
        foreach ($this->attributeCodes as $key => $code) {
            $this->attributeCodes[$key] = strtr($code, [self::SPECIAL_CODE_FOR_DELIMITER => self::DELIMITER]);
        }
        foreach ($this->separators as $key => $code) {
            $this->separators[$key] = strtr($code, [self::SPECIAL_CODE_FOR_DELIMITER => self::DELIMITER]);
        }
    }

    private function encodeDelimiter(): void
    {
        foreach ($this->attributeCodes as $key => $code) {
            $this->attributeCodes[$key] = strtr($code, [self::DELIMITER => self::SPECIAL_CODE_FOR_DELIMITER]);
        }
        foreach ($this->separators as $key => $code) {
            $this->separators[$key] = strtr($code, [self::DELIMITER => self::SPECIAL_CODE_FOR_DELIMITER]);
        }
    }

    public function updateFromAttribute(AttributeInterface $attribute): void
    {
        $this->attributeCodes = explode(self::DELIMITER, (string) $attribute->getProperty('attributes'));
        $this->removeEmptyAttributes();
        $this->separators = explode(self::DELIMITER, (string) $attribute->getProperty('separators'));
        $this->removeUnnecessarySeparators();
        $this->decodeDelimiter();
    }

    private function removeEmptyAttributes(): void
    {
        while (count($this->attributeCodes) > 2) {
            $lastAttributeCode = end($this->attributeCodes);
            if (!$lastAttributeCode) {
                array_pop($this->attributeCodes);
            } else {
                return;
            }
        }
    }

    private function removeUnnecessarySeparators(): void
    {
        while ($this->getSeparatorsCount() > $this->getAttributeCodesCount() - 1) {
            array_pop($this->separators);
        }
    }

    private function addAttributeCode(string $code): void
    {
        $this->attributeCodes[] = $code;
    }

    private function addSeparator(string $separator): void
    {
        $this->separators[] = $separator;
    }

    public function updatePropertyValue(string $field, string $value): void
    {
        switch (true) {
            case 0 === mb_strpos($field, 'separator'):
                $this->addSeparator($value);

                return;
            case 0 === mb_strpos($field, 'attribute'):
                $this->addAttributeCode($value);

                return;
        }
    }

    public function setAttributeProperties(AttributeInterface $attribute): void
    {
        $this->encodeDelimiter();
        $attribute->setProperty('separators', implode(self::DELIMITER, $this->separators));
        $attribute->setProperty('attributes', implode(self::DELIMITER, $this->attributeCodes));
        $this->decodeDelimiter();
    }
}