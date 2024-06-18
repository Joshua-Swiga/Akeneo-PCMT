<?php
/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Malawi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PcmtRulesBundle\Service\RuleAttributeProvider;

class RuleProcessStep
{
    public const KEY_ATTRIBUTE_NAME_FIRST_AXIS = 'MANUFACTURER_NAME';

    public const KEY_ATTRIBUTE_NAME_SECOND_AXIS_SOURCE = 'GTIN';
    public const KEY_ATTRIBUTE_NAME_SECOND_AXIS_DESTINATION = 'KNOWN_GTIN';

    /** @var RuleAttributeProvider */
    private $attributeProvider;

    /** @var RuleProductProcessor */
    private $ruleProductProcessor;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    public const BATCH_SIZE = 20;

    public function setAttributeProvider(RuleAttributeProvider $attributeProvider): void
    {
        $this->attributeProvider = $attributeProvider;
    }

    public function setRuleProductProcessor(RuleProductProcessor $ruleProductProcessor): void
    {
        $this->ruleProductProcessor = $ruleProductProcessor;
    }

    public function setPqbFactory(ProductQueryBuilderFactoryInterface $pqbFactory): void
    {
        $this->pqbFactory = $pqbFactory;
    }

    public function execute(): void
    {
        $sourceFamily = $this->familyRepository->findOneBy(['code' => 'Malawi_Products_and_Trade_Items_Flat']);
        if (!$sourceFamily) {
            throw new \Exception('Malawi: source family not existing.');
        }
        $destinationFamily = $this->familyRepository->findOneBy(['code' => 'Malawi_Products_and_Trade_Items']);
        if (!$destinationFamily) {
            throw new \Exception('Malawi: destination family not existing.');
        }

        $attributes = $this->attributeProvider->getAllForFamilies($sourceFamily, $destinationFamily);
        $keyAttribute = null;
        foreach ($attributes as $attribute) {
            if (self::KEY_ATTRIBUTE_NAME_FIRST_AXIS === $attribute->getCode()) {
                $keyAttribute = $attribute;
            }
        }
        if (!$keyAttribute) {
            throw new \Exception('Malawi: key attribute not found.');
        }

        echo 'Attributes_found: ' . count($attributes) . "\n";

        $result = true;
        $offset = 0;
        while ($result) {
            $result = $this->processBatch(
                [
                    'sourceFamily'      => $sourceFamily,
                    'destinationFamily' => $destinationFamily,
                    'keyAttribute'      => $keyAttribute,
                ],
                $offset
            );
            $offset += self::BATCH_SIZE;
        }
    }

    private function processBatch(array $rule, int $offset): bool
    {
        $count = 0;
        // look in ElasticSearch index
        $pqb = $this->pqbFactory->create(
            [
                'default_locale' => null,
                'default_scope'  => null,
                'limit'          => self::BATCH_SIZE,
                'from'           => $offset,
            ]
        );
        $pqb->addFilter('family', Operators::IN_LIST, [$rule['sourceFamily']->getCode()]);

        $entityCursor = $pqb->execute();

        foreach ($entityCursor as $entity) {
            $count++;
            $result = $count + $offset;
            echo $result . ".\n";
            if ($entity instanceof ProductInterface) {
                echo 'Source product found: ' . $entity->getLabel() . "\n";
                $this->ruleProductProcessor->process($rule, $entity);
            } else {
                echo 'Found source entity that is not a product! ' . $entity->getLabel() . "\n";
            }
        }

        return $count ? true : false;
    }

    public function setFamilyRepository(FamilyRepositoryInterface $familyRepository): void
    {
        $this->familyRepository = $familyRepository;
    }
}
