<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Connector\Job\InvalidItem;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;

class CategoryAwareInvalidItem implements InvalidItemInterface
{
    /** @var CategoryAwareInterface */
    private $entity;

    public function __construct(CategoryAwareInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidData()
    {
        $type = $this->entity instanceof ProductModelInterface ? 'product model' : 'product';

        return [
            'identifier' => $this->entity instanceof ProductModelInterface ? $this->entity->getCode() : $this->entity->getIdentifier(),
            'label'      => $this->entity->getLabel(),
            'type'       => $type,
        ];
    }
}
