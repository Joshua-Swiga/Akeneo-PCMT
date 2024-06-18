<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Entity;

use Akeneo\Pim\Structure\Component\Model\AttributeTranslation as BaseAttributeTranslation;

class AttributeTranslation extends BaseAttributeTranslation
{
    /** @var string */
    protected $description;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}