<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use Akeneo\UserManagement\Component\Model\Group;
use PcmtPermissionsBundle\Entity\AttributeGroupAccess;
use PcmtPermissionsBundle\Entity\AttributeGroupWithAccess;

class AttributeGroupAccessBuilder
{
    /** @var AttributeGroupAccess */
    private $attributeGroupAccess;

    public function __construct()
    {
        $attributeGroup = new AttributeGroupWithAccess();
        $userGroup = new Group();
        $this->attributeGroupAccess = new AttributeGroupAccess(
            $attributeGroup,
            $userGroup,
            AttributeGroupAccess::VIEW_LEVEL
        );
    }

    public function withId(int $id): self
    {
        $this->attributeGroupAccess->setId($id);

        return $this;
    }

    public function withUserGroup(Group $userGroup): self
    {
        $this->attributeGroupAccess->setUserGroup($userGroup);

        return $this;
    }

    public function withLevel(string $level): self
    {
        $this->attributeGroupAccess->setLevel($level);

        return $this;
    }

    public function build(): AttributeGroupAccess
    {
        return $this->attributeGroupAccess;
    }
}
