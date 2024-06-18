<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Controller;

use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeController
{
    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var NormalizerInterface */
    private $lightAttributeNormalizer;

    /** @var UserContext */
    private $userContext;

    /** @var RuleAttributeProvider */
    private $ruleAttributeProvider;

    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        NormalizerInterface $lightAttributeNormalizer,
        UserContext $userContext,
        RuleAttributeProvider $ruleAttributeProvider
    ) {
        $this->familyRepository = $familyRepository;
        $this->lightAttributeNormalizer = $lightAttributeNormalizer;
        $this->userContext = $userContext;
        $this->ruleAttributeProvider = $ruleAttributeProvider;
    }

    public function getForFamiliesAction(Request $request): Response
    {
        $sourceFamilyCode = $request->query->get('sourceFamily');
        $destinationFamilyCode = $request->query->get('destinationFamily');
        $sourceFamily = $sourceFamilyCode ? $this->familyRepository->findOneByIdentifier($sourceFamilyCode) : null;
        $destinationFamily = $destinationFamilyCode ? $this->familyRepository->findOneByIdentifier($destinationFamilyCode) : null;
        if (!$sourceFamily || !$destinationFamily) {
            return new JsonResponse([]);
        }

        ['sourceKeyAttributes' => $sourceKeyAttributes, 'destinationKeyAttributes' => $destinationKeyAttributes] = $this->ruleAttributeProvider->getPossibleForKeyAttribute($sourceFamily, $destinationFamily);

        $normalizedSourceAttributes = array_map(function ($attribute) {
            return $this->lightAttributeNormalizer->normalize(
                $attribute,
                'internal_api',
                ['locale' => $this->userContext->getUiLocale()->getCode()]
            );
        }, $sourceKeyAttributes);

        $normalizedDestinationAttributes = array_map(function ($attribute) {
            return $this->lightAttributeNormalizer->normalize(
                $attribute,
                'internal_api',
                ['locale' => $this->userContext->getUiLocale()->getCode()]
            );
        }, $destinationKeyAttributes);

        return new JsonResponse([
            'sourceKeyAttributes'      => $normalizedSourceAttributes,
            'destinationKeyAttributes' => $normalizedDestinationAttributes,
        ]);
    }

    public function getForOptionsAction(Request $request): Response
    {
        $familyCode = $request->query->get('family');
        $family = $familyCode ? $this->familyRepository->findOneByIdentifier($familyCode) : null;
        if (!$family) {
            return new JsonResponse([]);
        }

        $types = (array) $request->query->get('types');
        $validationRule = $request->query->get('validationRule');

        $attributes = $this->ruleAttributeProvider->getForOptions($family, $types, $validationRule);

        $normalizedAttributes = array_map(function ($attribute) {
            return $this->lightAttributeNormalizer->normalize(
                $attribute,
                'internal_api',
                ['locale' => $this->userContext->getUiLocale()->getCode()]
            );
        }, $attributes);

        return new JsonResponse($normalizedAttributes);
    }

    public function getForF2FMappingAction(Request $request): Response
    {
        $sourceFamilyCode = $request->query->get('sourceFamily');
        $destinationFamilyCode = $request->query->get('destinationFamily');

        [$sourceAttributeList, $destinationAttributeList] = $this->ruleAttributeProvider->getForF2FAttributeMapping(
            $sourceFamilyCode ? $this->familyRepository->findOneBy(['code' => $sourceFamilyCode]) : null,
            $destinationFamilyCode ? $this->familyRepository->findOneBy(['code' => $destinationFamilyCode]) : null
        );

        $normalizedSourceAttributes = array_map(function ($attribute) {
            return $this->lightAttributeNormalizer->normalize(
                $attribute,
                'internal_api',
                ['locale' => $this->userContext->getUiLocale()->getCode()]
            );
        }, $sourceAttributeList);
        $normalizedDestinationAttributes = array_map(function ($attribute) {
            return $this->lightAttributeNormalizer->normalize(
                $attribute,
                'internal_api',
                ['locale' => $this->userContext->getUiLocale()->getCode()]
            );
        }, $destinationAttributeList);

        return new JsonResponse([
            'sourceAttributeList'      => $normalizedSourceAttributes,
            'destinationAttributeList' => $normalizedDestinationAttributes,
        ]);
    }
}