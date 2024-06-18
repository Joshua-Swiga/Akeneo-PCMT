<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Entity;

use PcmtCoreBundle\Connector\Mapping\E2OpenMapping;

class E2OpenAttributeData
{
    /** @var string */
    private $name;

    /** @var string */
    private $code;

    /** @var bool|int|string|null */
    private $value;

    /** @var string[] */
    private $attributes = [];

    /**
     * E2OpenAttributeData constructor.
     *
     * @param bool|int|string|null $value
     */
    public function __construct(string $name, string $code, $value, array $attributes)
    {
        $value = E2OpenMapping::mapValue($value);
        $this->name = $name;
        $this->code = $code;
        $this->value = is_string($value) ? trim($value) : $value;
        $this->attributes = $attributes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return bool|int|string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }
}