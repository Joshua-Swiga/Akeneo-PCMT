<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\AttributeOption;

class AttributeOptionBuilder
{
    public const DEFAULT_CODE = 'DEFAULT_CODE_OPTION_XX';

    /** @var AttributeOption */
    private $attributeOption;

    public function __construct()
    {
        $this->attributeOption = new AttributeOption();
        $this->attributeOption->setCode(self::DEFAULT_CODE);
    }

    public function withCode(string $code): self
    {
        $this->attributeOption->setCode($code);

        return $this;
    }

    public function build(): AttributeOption
    {
        return $this->attributeOption;
    }
}