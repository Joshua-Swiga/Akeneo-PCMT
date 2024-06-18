<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtCoreBundle\Entity\AttributeTranslation;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DescriptionNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        /** @var AttributeInterface $object */
        $context = array_merge(
            [
                'locales'  => [],
            ],
            $context
        );

        $translations = array_fill_keys($context['locales'], null);

        foreach ($object->getTranslations() as $translation) {
            /** @var AttributeTranslation $translation */
            if (!$translation instanceof AttributeTranslation) {
                throw new \LogicException(
                    'Description normalizer works only for instances of AttributeTranslation class.'
                );
            }

            if (empty($context['locales']) || in_array($translation->getLocale(), $context['locales'])) {
                $translations[$translation->getLocale()] = '' === $translation->getDescription() ? null : $translation->getDescription();
            }
        }

        return $translations;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AttributeInterface;
    }
}
