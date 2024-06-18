<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Provider\Field;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;

class ConcatenatedAttributeFieldProvider implements FieldProviderInterface
{
    /** @var mixed[] */
    protected $fields = [
        PcmtAtributeTypes::CONCATENATED_FIELDS => 'pcmt_concatenated_attribute_text_field',
    ];

    /**
     * {@inheritdoc}
     */
    public function getField($attribute): string
    {
        return $this->fields[$attribute->getType()];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof AttributeInterface &&
            in_array($element->getType(), array_keys($this->fields));
    }
}