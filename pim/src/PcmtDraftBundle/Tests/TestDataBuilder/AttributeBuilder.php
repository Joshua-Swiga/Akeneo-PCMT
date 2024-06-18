<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\Attribute;

class AttributeBuilder
{
    /** @var Attribute */
    private $attribute;

    public function __construct()
    {
        $this->attribute = new Attribute();
    }

    public function withCode(string $code): self
    {
        $this->attribute->setCode($code);

        return $this;
    }

    public function build(): Attribute
    {
        return $this->attribute;
    }
}