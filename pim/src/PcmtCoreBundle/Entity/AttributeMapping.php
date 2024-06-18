<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Entity;

class AttributeMapping
{
    public const MAPPING_TYPES = [
        'E2Open',
    ];
    /** @var int */
    private $id;

    /** @var string */
    private $mappingType;

    /** @var Attribute */
    private $externalAttribute;

    /** @var Attribute */
    private $pcmtAttribute;

    public function __construct(
        string $type,
        Attribute $externalAttribute,
        Attribute $pcmtAttribute
    ) {
        if (!in_array($type, self::MAPPING_TYPES)) {
            throw new \InvalidArgumentException('Wrong mapping type.');
        }
        $this->mappingType = $type;
        $this->addMapping($externalAttribute, $pcmtAttribute);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExternalAttribute(): Attribute
    {
        return $this->externalAttribute;
    }

    public function getPcmtAttribute(): Attribute
    {
        return $this->pcmtAttribute;
    }

    public function getMappingType(): string
    {
        return $this->mappingType;
    }

    public function addMapping(Attribute $externalAttribute, Attribute $pcmtAttribute): void
    {
        $this->externalAttribute = $externalAttribute;
        $this->pcmtAttribute = $pcmtAttribute;
    }
}
