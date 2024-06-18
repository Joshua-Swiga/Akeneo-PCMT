<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Validator;

use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Entity\ConcatenatedProperty;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AttributeValidator
{
    /**
     * @param mixed|object $payload
     */
    public static function validateConcatenated(Attribute $object, ExecutionContextInterface $context, $payload): void
    {
        if (PcmtAtributeTypes::CONCATENATED_FIELDS !== $object->getType()) {
            return;
        }

        $concatenatedProperty = new ConcatenatedProperty();
        $concatenatedProperty->updateFromAttribute($object);

        if (2 > $concatenatedProperty->getAttributeCodesCount()) {
            self::addTypeSpecificFieldsViolation($context);

            return;
        }
        foreach ($concatenatedProperty->getAttributeCodes() as $code) {
            if (empty($code)) {
                self::addTypeSpecificFieldsViolation($context);

                return;
            }
        }

        if ($concatenatedProperty->getSeparatorsCount() !== $concatenatedProperty->getAttributeCodesCount() - 1) {
            self::addTypeSpecificFieldsViolation($context);

            return;
        }

        foreach ($concatenatedProperty->getSeparators() as $separator) {
            if (empty($separator)) {
                self::addTypeSpecificFieldsViolation($context);

                return;
            }
        }
    }

    private static function addTypeSpecificFieldsViolation(ExecutionContextInterface $context): void
    {
        $context->buildViolation('pcmt.attribute.concatenated.type_specific_fields.error')
            ->addViolation();
    }
}