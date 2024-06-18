<?php

declare(strict_types=1);

namespace FhirBundle\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Copyright (c) 2022, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
final class ConnectorProductNormalizer
{
    /** @var ValuesNormalizer */
    private $valuesNormalizer;

    /** @var EntityRepository */
    private $entityRepository;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var string */
    private $identifier;

    /** @var string */
    private $description;

    /** @var string */
    private $marketingAuthorization;

    /** @var string */
    private $other;

    /** @var ObjectRepository */
    private $categoryRepository;

    public function __construct(ValuesNormalizer $valuesNormalizer, EntityRepository $entityRepository, UrlGeneratorInterface $router, string $identifier, string $description, string $marketingAuthorization, string $other, ObjectRepository $categoryRepository)
    {
        $this->valuesNormalizer = $valuesNormalizer;
        $this->entityRepository = $entityRepository;
        $this->router = $router;
        $this->identifier = $identifier;
        $this->description = $description;
        $this->marketingAuthorization = $marketingAuthorization;
        $this->other = $other;
        $this->categoryRepository = $categoryRepository;
    }

    public function normalizeConnectorProductList(ConnectorProductList $connectorProducts): array
    {
        $normalizedProducts = [];
        foreach ($connectorProducts->connectorProducts() as $connectorProduct) {
            $normalizedProducts[] = $this->normalizeConnectorProduct($connectorProduct);
        }

        return $normalizedProducts;
    }

    public function normalizeConnectorProduct(ConnectorProduct $connectorProduct): array
    {
        $values = $this->valuesNormalizer->normalize($connectorProduct->values(), 'standard');
        $attr_values = $connectorProduct->attributeCodesInValues();
        $repository = $this->entityRepository;
        $identifier = [];
        $description = [];
        $marketingAuthorization = [];
        $other = [];
        $product_route = $this->router->generate(
            'pim_api_product_get',
            ['code' => $connectorProduct->identifier()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        foreach ($attr_values as $code) {
            $mapping = $repository->findOneByCode($code);
            if ($mapping) {
                $attribute_route = $this->router->generate(
                    'pim_api_attribute_get',
                    ['code' => $code],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                switch ($mapping->getMapping()) {
                    case $this->identifier:
                        $identifier[] = $this->mapIdentifier($attribute_route, $code, $product_route, $values);
                        break;
                    case $this->description:
                        $description['language'] = $values[$code][0]['locale'];
                        $description['description'] = $values[$code][0]['data'];
                        break;
                    case $this->marketingAuthorization:
                        $marketingAuthorization[] = $this->mapMarketingAuthorization($attribute_route, $code, $product_route, $values);
                        break;
                    case $this->other:
                        $other[] = $this->mapOther($attribute_route, $code, $values);
                        break;
                }
            }
        }

        $association = [];

        if ($connectorProduct->parentProductModelCode()) {
            $product_model_route = $this->router->generate(
                'pim_api_product_model_get',
                ['code' => $connectorProduct->parentProductModelCode()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $product_model_fhir_route = $this->router->generate(
                'pim_fhir_api_product_model_get',
                ['code' => $connectorProduct->parentProductModelCode()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $association = [
                'associationType' => [
                    'system'  => $product_model_route,
                    'code'    => $connectorProduct->parentProductModelCode(),
                    'display' => $connectorProduct->parentProductModelCode(),
                ],
                'associatedProduct' => [
                    'Product' => [
                        'reference' => $product_model_fhir_route,
                        'display'   => $connectorProduct->parentProductModelCode(),
                        'type'      => 'Product',
                    ],
                ],
                'quantity' => [
                    'numerator'   => 1,
                    'denominator' => 1,
                ],
            ];
        }

        $categories = [];

        foreach ($this->categoryRepository->findByCode($connectorProduct->categoryCodes()) as $category) {
            $category_route = $this->router->generate(
                'pim_api_category_get',
                ['code' => $category->getCode()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $categories[] = [
                'coding' => [
                    'system'  => $category_route,
                    'code'    => $category->getCode(),
                    'display' => $category->getLabel(),
                ],
                'text' => $category->getLabel(),
            ];
        }

        return [
            'resourceType'           => 'Item',
            'id'                     => $connectorProduct->identifier(),
            'identifier'             => $identifier,
            'description'            => $description,
            'marketingAuthorization' => $marketingAuthorization,
            'association'            => $association,
            'attributes'             => $other,
            'classification'         => $categories,
        ];
    }

    private function mapIdentifier(string $attribute_route, string $code, string $product_route, array $values): array
    {
        return [
            'type' => [
                'coding' => [
                    [
                        'system'  => $attribute_route,
                        'code'    => $code,
                        'display' => $code,
                    ],
                ],
                'text' => $code,
            ],
            'system' => $product_route,
            'value'  => $values[$code][0]['data'],
        ];
    }

    private function mapMarketingAuthorization(string $attribute_route, string $code, string $product_route, array $values): array
    {
        return [
            'holder' => [
                'reference' => $attribute_route,
                'display'   => $code,
            ],
            'number' => [
                'type' => [
                    'coding' => [
                        [
                            'system'  => $attribute_route,
                            'code'    => $code,
                            'display' => $code,
                        ],
                    ],
                    'text' => $code,
                ],
                'system' => $product_route,
                'value'  => $values[$code][0]['data'],
            ],
        ];
    }

    private function mapOther(string $attribute_route, string $code, array $values): array
    {
        return [
            'attributeType' => [
                'coding' => [
                    'system'  => $attribute_route,
                    'code'    => $code,
                    'display' => $code,
                ],
                'text' => $code,
            ],
            'value' => $values[$code][0]['data'],
        ];
    }
}
