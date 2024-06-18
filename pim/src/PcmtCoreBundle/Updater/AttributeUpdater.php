<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Updater;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater as BaseAttributeUpdater;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

/**
 * @override: Handle localizable attribute description when updating an attribute
 *
 * Class AttributeUpdater
 *
 * @author                 Benjamin Hil <benjamin.hil@dnd.fr>
 * @copyright              Copyright (c) 2018 Agence Dn'D
 * @license                http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see                   http://www.dnd.fr/
 */
class AttributeUpdater implements ObjectUpdaterInterface
{
    /** @var TranslatableUpdater */
    protected $translatableUpdater;

    /** @var ConcatenatedAttributeUpdater */
    protected $concatenatedAttributeUpdater;

    /** @var BaseAttributeUpdater */
    private $baseAttributeUpdater;

    public function __construct(
        BaseAttributeUpdater $baseAttributeUpdater,
        TranslatableUpdater $translatableUpdater,
        ConcatenatedAttributeUpdater $concatenatedAttributeUpdater
    ) {
        $this->concatenatedAttributeUpdater = $concatenatedAttributeUpdater;
        $this->translatableUpdater = $translatableUpdater;
        $this->baseAttributeUpdater = $baseAttributeUpdater;
    }

    /**
     * @param AttributeInterface|AttributeInterface $object
     *
     * @return $this|ObjectUpdaterInterface
     */
    public function update($object, array $data, array $options = [])
    {
        if (!$object instanceof AttributeInterface) {
            throw InvalidObjectException::objectExpected(
                get_class($object),
                AttributeInterface::class
            );
        }

        foreach ($data as $field => $value) {
            if ($this->supports($field)) {
                $this->validateDataType($field, $value);
                $this->setData($object, $field, $value);
            } else {
                $this->baseAttributeUpdater->update($object, [$field => $value], $options);
            }
        }

        return $this;
    }

    protected function supports(string $field): bool
    {
        return in_array($field, ['descriptions', 'concatenated']);
    }

    /**
     * @param array|string|int|bool $data
     */
    protected function validateDataType(string $field, $data): void
    {
        if (in_array($field, ['descriptions', 'concatenated'])) {
            if (!is_array($data)) {
                throw InvalidPropertyTypeException::arrayExpected($field, static::class, $data);
            }

            foreach ($data as $value) {
                if (null !== $value && !is_scalar($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf('one of the "%s" values is not a scalar', $field),
                        static::class,
                        $data
                    );
                }
            }
        }
    }

    /**
     * @param array|string|int|bool $data
     */
    protected function setData(AttributeInterface $attribute, string $field, $data): void
    {
        switch ($field) {
            case 'descriptions':
                // update localizable attribute description fields
                $this->translatableUpdater->updateDescription($attribute, $data);
                break;
            case 'concatenated':
                $this->concatenatedAttributeUpdater->update($attribute, $data);
                break;
        }
    }
}
