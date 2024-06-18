<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Service\E2Open;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

class E2OpenAttributesService
{
    public const MEASURE_UNIT = 'GDSN_Unit_Of_Measure';

    public const FAMILY_CODE = 'GS1_GDSN';

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeInterface[] */
    private $attributes = [];

    /** @var mixed[] */
    private $measureConfig = [];

    public function __construct(AttributeRepositoryInterface $attributeRepository, FamilyRepositoryInterface $familyRepository, array $measureConfig)
    {
        $this->attributeRepository = $attributeRepository;
        $this->familyRepository = $familyRepository;
        $this->measureConfig = $measureConfig['measures_config'][self::MEASURE_UNIT]['units'];
    }

    public function getMeasureUnitForSymbol(string $symbol): ?string
    {
        foreach ($this->measureConfig as $key => $values) {
            if ($values['symbol'] === $symbol) {
                return $key;
            }
        }

        return null;
    }

    private function load(): void
    {
        $family = $this->familyRepository->findOneBy(['code' => self::FAMILY_CODE]);
        if (!$family) {
            throw new \Exception('No family '. self::FAMILY_CODE);
        }
        $this->attributes = $this->attributeRepository->findAttributesByFamily($family);
    }

    public function getForCode(string $code): ?AttributeInterface
    {
        if (!$this->attributes) {
            $this->load();
        }
        foreach ($this->attributes as $attribute) {
            if ($attribute->getCode() === $code) {
                return $attribute;
            }
        }

        return null;
    }
}