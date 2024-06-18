<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\Family;
use Doctrine\Common\Collections\Collection;

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

    public function withId(int $id): self
    {
        $reflection = new \ReflectionClass(get_class($this->family));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->family, $id);

        return $this;
    }

    public function withFamilyVariants(Collection $collection): self
    {
        $this->family->setFamilyVariants($collection);

        return $this;
    }

    public function build(): Family
    {
        return $this->family;
    }
}