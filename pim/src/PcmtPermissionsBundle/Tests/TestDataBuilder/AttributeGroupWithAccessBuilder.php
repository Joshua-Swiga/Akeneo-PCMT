<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use PcmtPermissionsBundle\Entity\AttributeGroupAccess;
use PcmtPermissionsBundle\Entity\AttributeGroupWithAccess;

class AttributeGroupWithAccessBuilder
{
    /** @var AttributeGroupWithAccess */
    private $attributeGroup;

    public function __construct()
    {
        $this->attributeGroup = new AttributeGroupWithAccess();
    }

    public function addAccess(AttributeGroupAccess $access): self
    {
        $this->attributeGroup->addAccess($access);

        return $this;
    }

    public function build(): AttributeGroupWithAccess
    {
        return $this->attributeGroup;
    }
}
