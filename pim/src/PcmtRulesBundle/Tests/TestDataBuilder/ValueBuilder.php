<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;

class ValueBuilder
{
    /** @var string */
    private $attributeCode;

    /** @var mixed */
    private $dataValue;

    /** @var ?string */
    private $localeCode;

    /**
     * ValueBuilder constructor.
     */
    public function __construct()
    {
        $this->attributeCode = 'attr_code';
    }

    public function withAttributeCode(string $code): self
    {
        $this->attributeCode = $code;

        return $this;
    }

    public function withData(?string $data): self
    {
        $this->dataValue = $data;

        return $this;
    }

    public function withLocale(string $localeCode): self
    {
        $this->localeCode = $localeCode;

        return $this;
    }

    public function build(): ValueInterface
    {
        if (!$this->localeCode) {
            return ScalarValue::value($this->attributeCode, $this->dataValue);
        }

        return ScalarValue::localizableValue($this->attributeCode, $this->dataValue, $this->localeCode);
    }
}