<?php
/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtSharedBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class AssociationCollectionBuilder
{
    /** @var Collection */
    private $collection;

    public function __construct()
    {
        $this->collection = new ArrayCollection();
    }

    public function withAssociation(AssociationInterface $association): self
    {
        $this->collection->add($association);

        return $this;
    }

    public function build(): Collection
    {
        return $this->collection;
    }
}