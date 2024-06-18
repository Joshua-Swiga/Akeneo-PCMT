<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\AttributeChange;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use PcmtDraftBundle\Entity\AttributeChange;
use Symfony\Component\Serializer\SerializerInterface;

class AttributeChangeService
{
    /** @var AttributeChange[] */
    protected $changes = [];

    /** @var SerializerInterface */
    protected $versioningSerializer;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    public function __construct(SerializerInterface $versioningSerializer, AttributeRepositoryInterface $attributeRepository)
    {
        $this->versioningSerializer = $versioningSerializer;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param array|object|string|int|null $value
     * @param array|object|string|int|null $previousValue
     */
    protected function createChange(string $attributeCode, $value, $previousValue): void
    {
        $value = '' === $value ? null : $value;
        $previousValue = '' === $previousValue ? null : $previousValue;

        if ($value === $previousValue) {
            return;
        }
        $attributeInstance = $this->attributeRepository->findOneByIdentifier($attributeCode);
        $attributeName = $attributeInstance ? $attributeInstance->getLabel() : $attributeCode;
        $this->changes[] = new AttributeChange($attributeName, (string) $previousValue, (string) $value);
    }

    public function get(?VersionableInterface $newObject, ?VersionableInterface $previousObject): array
    {
        $this->changes = [];

        if (!$newObject) {
            return $this->changes;
        }

        $newValues = $this->versioningSerializer->normalize($newObject, 'flat');
        $previousValues = $previousObject ?
            $this->versioningSerializer->normalize($previousObject, 'flat') :
            [];

        $attributeCodes = array_unique(array_merge(array_keys($newValues), array_keys($previousValues)));
        foreach ($attributeCodes as $attributeCode) {
            $newValue = $newValues[$attributeCode] ?? null;
            $previousValue = $previousValues[$attributeCode] ?? null;
            $attributeCode = (string) $attributeCode;
            $this->createChange($attributeCode, $newValue, $previousValue);
        }

        return $this->changes;
    }
}