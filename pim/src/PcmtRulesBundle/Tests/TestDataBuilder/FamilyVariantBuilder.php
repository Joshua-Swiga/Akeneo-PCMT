<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;

class FamilyVariantBuilder
{
    public const EXAMPLE_CODE = 'example_code';

    /** @var FamilyVariant */
    private $familyVariant;

    public function __construct()
    {
        $this->familyVariant = new FamilyVariant();
        $this->familyVariant->setFamily(
            (new FamilyBuilder())->build()
        );
        $this->familyVariant->setCode(self::EXAMPLE_CODE);
    }

    public function withVariantAttributeSet(VariantAttributeSet $variantAttributeSet): self
    {
        $this->familyVariant->addVariantAttributeSet($variantAttributeSet);

        return $this;
    }

    public function withFamily(FamilyInterface $family): self
    {
        $this->familyVariant->setFamily($family);

        return $this;
    }

    public function withCode(string $code): self
    {
        $this->familyVariant->setCode($code);

        return $this;
    }

    public function build(): FamilyVariant
    {
        return $this->familyVariant;
    }
}