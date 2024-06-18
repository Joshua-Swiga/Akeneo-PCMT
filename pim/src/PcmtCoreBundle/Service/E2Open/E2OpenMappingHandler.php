<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Service\E2Open;

use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtCoreBundle\Connector\Mapping\E2OpenMapping;
use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Entity\AttributeMapping;
use PcmtCoreBundle\Exception\Mapping\AttributeNotInFamilyException;
use PcmtCoreBundle\Repository\AttributeMappingRepository;
use PcmtCoreBundle\Service\Handler\AttributeMappingHandlerInterface;

class E2OpenMappingHandler implements AttributeMappingHandlerInterface
{
    public const MAPPING_TYPE = 'E2Open';

    public const FAMILY_CODE = 'GS1_GDSN';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeMappingRepository */
    private $attributeMappingRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        FamilyRepositoryInterface $familyRepository,
        AttributeMappingRepository $attributeMappingRepository
    ) {
        $this->entityManager = $entityManager;
        $this->familyRepository = $familyRepository;
        $this->attributeMappingRepository = $attributeMappingRepository;
        $this->attributeList = E2OpenMapping::getE2OpenAttributeNames();
    }

    public function createMapping(Attribute $mappingAttribute, Attribute $mappedAttribute): void
    {
        if (!$this->validateAttributeIsE2Open($mappingAttribute)) {
            throw new AttributeNotInFamilyException(
                'Attribute ' . $mappingAttribute->getCode() . ' does not belong to family.'
            );
        }
        $mapping = $this->findMapping(
            $mappingAttribute,
            $mappedAttribute
        ) ??
            new AttributeMapping(
                self::MAPPING_TYPE,
                $mappingAttribute,
                $mappedAttribute
            );
        $this->entityManager->persist($mapping);
        $this->entityManager->flush();
    }

    private function findMapping(Attribute $externalAttribute, Attribute $pcmtAttribute): ?AttributeMapping
    {
        return $this->attributeMappingRepository->findOneBy(
            [
                'externalAttribute' => $externalAttribute,
                'pcmtAttribute'     => $pcmtAttribute,
                'mappingType'       => self::MAPPING_TYPE,
            ]
        );
    }

    private function validateAttributeIsE2Open(Attribute $attribute): bool
    {
        $family = $this->familyRepository->findOneBy(
            [
                'code' => self::FAMILY_CODE,
            ]
        );

        return $family->hasAttribute($attribute);
    }
}