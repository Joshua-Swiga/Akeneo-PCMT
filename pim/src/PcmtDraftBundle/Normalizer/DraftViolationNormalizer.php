<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use PcmtDraftBundle\Exception\DraftViolationException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DraftViolationNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    private $constraintViolationNormalizer;

    public function __construct(NormalizerInterface $constraintViolationNormalizer)
    {
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedViolations = [];
        $context = $object->getContextForNormalizer();
        foreach ($object->getViolations() as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                $context
            );
        }

        return $normalizedViolations;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof DraftViolationException;
    }
}