<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Updater;

use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater as BaseTranslatableUpdater;

/**
 * @override: Handle localizable attribute description when updating a translation
 *
 * Class TranslatableUpdater
 *
 * @author                 Benjamin Hil <benjamin.hil@dnd.fr>
 * @copyright              Copyright (c) 2018 Agence Dn'D
 * @license                http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see                   http://www.dnd.fr/
 */
class TranslatableUpdater extends BaseTranslatableUpdater
{
    public function update(TranslatableInterface $object, array $data): void
    {
        foreach ($data as $localeCode => $label) {
            $object->setLocale($localeCode);
            $translation = $object->getTranslation();
            // Add @DND
            if (null === $label || '' === $label) {
                $translation->setLabel(null); // force label value to null instead of deleting translation
            } else {
                $translation->setLabel($label);
            }
            if (method_exists($translation, 'getDescription')) {
                $this->checkTranslationValues($object);
            }
            // / Add @DND
        }
    }

    public function updateDescription(TranslatableInterface $object, array $data): void
    {
        // Add @DND
        foreach ($data as $localeCode => $description) { // update localizable attribute description fields
            $object->setLocale($localeCode);
            $translation = $object->getTranslation();

            if (null === $description || '' === $description) {
                $translation->setDescription(null);
            } else {
                $translation->setDescription($description);
            }

            $this->checkTranslationValues($object);
        }
        // / Add @DND
    }

    /**
     * Check the database row, then remove it if both label and description fields are null
     */
    private function checkTranslationValues(TranslatableInterface $object): void
    {
        /** @var AbstractTranslation $translation */
        $translation = $object->getTranslation();
        /** @var string $description */
        $description = $translation->getDescription();
        /** @var string $label */
        $label = $translation->getLabel();

        if (null === $description && null === $label) {
            $object->removeTranslation($translation);
        }
    }
}
