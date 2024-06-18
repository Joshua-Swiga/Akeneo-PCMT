<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Value;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\Common\Collections\ArrayCollection;

class AttributeMappingCollection extends ArrayCollection
{
    public function getSourceAttributeForDestinationOne(AttributeInterface $destinationAttribute): ?AttributeInterface
    {
        $array = $this->toArray();
        $filtered = array_filter($array, function (AttributeMapping $mapping) use ($destinationAttribute) {
            return $mapping->getDestinationAttribute()->getCode() === $destinationAttribute->getCode();
        });
        if (!$filtered) {
            return null;
        }
        /** @var AttributeMapping $first */
        $first = reset($filtered);

        return $first->getSourceAttribute();
    }
}