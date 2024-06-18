<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PcmtCoreBundle\Entity\ReferenceData\CountryCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReferenceDataController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var NormalizerInterface */
    private $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        NormalizerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function getCountryCodes(): Response
    {
        $repository = $this->entityManager->getRepository(CountryCode::class);

        $referenceData = $repository->findAll();

        return new JsonResponse($this->serializer->normalize($referenceData));
    }
}