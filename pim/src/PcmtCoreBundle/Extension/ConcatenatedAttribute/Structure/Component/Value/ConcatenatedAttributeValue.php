<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

final class ConcatenatedAttributeValue extends AbstractValue implements ConcatenatedAttributeValueInterface
{
    public function isEqual(ValueInterface $value): bool
    {
        return $this->getData() === $value->getData();
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function __toString(): string
    {
        return '';
    }
}