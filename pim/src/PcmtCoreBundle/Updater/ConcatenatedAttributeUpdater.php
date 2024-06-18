<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Updater;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtCoreBundle\Entity\ConcatenatedProperty;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use Psr\Log\LoggerInterface;

class ConcatenatedAttributeUpdater
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ConcatenatedProperty */
    private $property;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->property = new ConcatenatedProperty();
    }

    public function update(AttributeInterface $attribute, array $concatenatedPropertyData): AttributeInterface
    {
        try {
            $this->validateAttribute($attribute);
            foreach ($concatenatedPropertyData as $field => $value) {
                $this->property->updatePropertyValue($field, $value);
            }
        } catch (\InvalidArgumentException $argumentException) {
            $this->logger->error($argumentException->getMessage());
        }
        $this->property->setAttributeProperties($attribute);

        return $attribute;
    }

    private function validateAttribute(AttributeInterface $attribute): void
    {
        if (PcmtAtributeTypes::CONCATENATED_FIELDS !== $attribute->getType()) {
            $message = sprintf(
                'Attribute is of a wrong type. Attribute of type %s passed, and attribute %s expected.',
                $attribute->getType(),
                PcmtAtributeTypes::CONCATENATED_FIELDS
            );

            throw new \InvalidArgumentException($message);
        }
    }
}