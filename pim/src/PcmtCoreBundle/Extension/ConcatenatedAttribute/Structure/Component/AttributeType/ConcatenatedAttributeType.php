<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeTypeInterface;

class ConcatenatedAttributeType implements AttributeTypeInterface
{
    /** @var string */
    protected $backendType = PcmtAtributeTypes::BACKEND_TYPE_CONCATENATED;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return PcmtAtributeTypes::CONCATENATED_FIELDS;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackendType()
    {
        return $this->backendType;
    }

    /**
     * {@inheritdoc}
     */
    public function isUnique()
    {
        return false;
    }
}