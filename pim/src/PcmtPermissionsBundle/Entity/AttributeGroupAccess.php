<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Entity;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;

class AttributeGroupAccess
{
    public const VIEW_LEVEL = 'VIEW';
    public const EDIT_LEVEL = 'EDIT';
    public const OWN_LEVEL = 'OWN';

    /** @var int */
    private $id;

    /** @var AttributeGroupInterface */
    private $attributeGroup;

    /** @var GroupInterface */
    private $userGroup;

    /** @var string */
    private $level = '';

    /**
     * AttributeGroupAccess constructor.
     */
    public function __construct(
        AttributeGroupInterface $attributeGroup,
        GroupInterface $userGroup,
        string $level
    ) {
        $this->attributeGroup = $attributeGroup;
        $this->userGroup = $userGroup;
        $this->level = $level;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getAttributeGroup(): AttributeGroupInterface
    {
        return $this->attributeGroup;
    }

    public function getUserGroup(): GroupInterface
    {
        return $this->userGroup;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    public function setUserGroup(GroupInterface $userGroup): void
    {
        $this->userGroup = $userGroup;
    }
}
