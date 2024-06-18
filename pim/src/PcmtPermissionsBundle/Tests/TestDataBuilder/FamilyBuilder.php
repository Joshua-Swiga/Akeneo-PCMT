<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\Family;

class FamilyBuilder
{
    public const EXAMPLE_CODE = 'example_family_code';

    /** @var Family */
    private $family;

    public function __construct()
    {
        $this->family = new Family();
        $this->family->setCode(self::EXAMPLE_CODE);
    }

    public function withCode(string $code): self
    {
        $this->family->setCode($code);

        return $this;
    }

    public function build(): Family
    {
        return $this->family;
    }
}
