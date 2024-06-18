<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Entity;

/**
 * Value Object for expressing difference between draft and 'original' product
 */
class AttributeChange
{
    /**
     * @var string
     */
    private $attributeName;

    /**
     * @var string|null
     */
    private $previousValue;

    /**
     * @var string|null
     */
    private $newValue;

    public function __construct(string $attributeName, ?string $previousValue, ?string $newValue)
    {
        $this->attributeName = $attributeName;
        $this->previousValue = $previousValue;
        $this->newValue = $newValue;
    }

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    public function getPreviousValue(): ?string
    {
        return $this->previousValue;
    }

    public function getNewValue(): ?string
    {
        return $this->newValue;
    }
}