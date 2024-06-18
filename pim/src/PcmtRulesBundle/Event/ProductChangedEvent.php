<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Event;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\EventDispatcher\Event;

class ProductChangedEvent extends Event
{
    public const NAME = 'PRODUCT_VALUE_UPDATED';

    /** @var ProductInterface */
    private $product;

    /** @var AttributeInterface */
    private $attribute;

    /** @var ?string */
    private $localeCode;

    /** @var ?string */
    private $scopeCode;

    /** @var ?ValueInterface */
    private $previousValue;

    /** @var ValueInterface */
    private $newValue;

    public function __construct(ProductInterface $product, AttributeInterface $attribute, ?string $localeCode, ?string $scopeCode, ?ValueInterface $previousValue, ValueInterface $newValue)
    {
        $this->product = $product;
        $this->attribute = $attribute;
        $this->localeCode = $localeCode;
        $this->scopeCode = $scopeCode;
        $this->previousValue = $previousValue;
        $this->newValue = $newValue;
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    public function getAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    public function getLocaleCode(): ?string
    {
        return $this->localeCode;
    }

    public function getScopeCode(): ?string
    {
        return $this->scopeCode;
    }

    public function getPreviousValue(): ?ValueInterface
    {
        return $this->previousValue;
    }

    public function getNewValue(): ValueInterface
    {
        return $this->newValue;
    }
}