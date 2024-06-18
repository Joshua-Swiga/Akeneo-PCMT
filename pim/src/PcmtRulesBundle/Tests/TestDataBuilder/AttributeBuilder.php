<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;

class AttributeBuilder
{
    public const DEFAULT_CODE = 'DEFAULT_CODE';

    /** @var Attribute */
    private $attribute;

    public function __construct()
    {
        $this->attribute = new Attribute();
        $this->attribute->setCode(self::DEFAULT_CODE);
        $this->attribute->setType(AttributeTypes::TEXT);
    }

    public function withCode(string $code): self
    {
        $this->attribute->setCode($code);

        return $this;
    }

    public function withType(string $type): self
    {
        $this->attribute->setType($type);

        return $this;
    }

    public function withValidationRule(string $rule): self
    {
        $this->attribute->setValidationRule($rule);

        return $this;
    }

    public function withProperties(array $properties): self
    {
        $this->attribute->setProperties($properties);

        return $this;
    }

    public function withMetricFamily(string $family): self
    {
        $this->attribute->setMetricFamily($family);

        return $this;
    }

    public function withOption(AttributeOption $option): self
    {
        $this->attribute->addOption($option);

        return $this;
    }

    public function build(): Attribute
    {
        return $this->attribute;
    }

    public function asScopable(): self
    {
        $this->attribute->setScopable(true);

        return $this;
    }

    public function asUnique(): self
    {
        $this->attribute->setUnique(true);

        return $this;
    }
}