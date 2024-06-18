<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtDraftBundle\Entity\DraftInterface;

class ProductFromDraftCreator implements ObjectFromDraftCreatorInterface
{
    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var AttributeFilterInterface */
    private $productAttributeFilter;

    /** @var ConverterInterface */
    private $productValueConverter;

    /** @var AttributeConverterInterface */
    private $localizedConverter;

    /** @var UserContext */
    private $userContext;

    /** @var FilterInterface */
    private $emptyValuesFilter;

    /** @var ObjectUpdaterInterface */
    private $objectUpdater;

    /** @var DraftValuesWithMissingAttributeFilter */
    private $draftValuesWithMissingAttributeFilter;

    public function __construct(
        ProductBuilderInterface $productBuilder,
        ConverterInterface $productValueConverter,
        AttributeConverterInterface $localizedConverter,
        UserContext $userContext,
        FilterInterface $emptyValuesFilter,
        ObjectUpdaterInterface $objectUpdater,
        AttributeFilterInterface $productAttributeFilter,
        DraftValuesWithMissingAttributeFilter $draftValuesWithMissingAttributeFilter
    ) {
        $this->productBuilder = $productBuilder;
        $this->productValueConverter = $productValueConverter;
        $this->localizedConverter = $localizedConverter;
        $this->userContext = $userContext;
        $this->emptyValuesFilter = $emptyValuesFilter;
        $this->objectUpdater = $objectUpdater;
        $this->productAttributeFilter = $productAttributeFilter;
        $this->draftValuesWithMissingAttributeFilter = $draftValuesWithMissingAttributeFilter;
    }

    public function createForSaveForDraftForExistingObject(
        DraftInterface $draft
    ): ?EntityWithAssociationsInterface {
        $product = $draft->getProduct();
        if (!$product) {
            return null;
        }
        $data = $draft->getProductData();
        if (isset($data['values'])) {
            $this->updateObject($product, $data);
        }

        return $product;
    }

    public function createNewObject(DraftInterface $draft): EntityWithAssociationsInterface
    {
        $data = $draft->getProductData();

        if (isset($data['parent'])) {
            $product = $this->productBuilder->createProduct(
                $data['identifier'] ?? null,
                $data['family'] ?? null
            );

            if (isset($data['values'])) {
                $this->updateObject($product, $data);
            }
        } else {
            $product = $this->productBuilder->createProduct(
                $data['identifier'] ?? null,
                $data['family'] ?? null
            );
        }

        return $product;
    }

    /**
     * Updates product with the provided data
     * Copied from ProductController
     */
    public function updateObject(EntityWithAssociationsInterface $entity, array $data): void
    {
        $values = $this->productValueConverter->convert($data['values']);

        $values = $this->localizedConverter->convertToDefaultFormats(
            $values,
            [
                'locale' => $this->userContext->getUiLocaleCode(),
            ]
        );

        $values = $this->draftValuesWithMissingAttributeFilter->filter($entity, $values);
        $dataFiltered = $this->emptyValuesFilter->filter($entity, ['values' => $values]);

        if (!empty($dataFiltered)) {
            $data = array_replace($data, $dataFiltered);
        } else {
            $data['values'] = [];
        }

        // don't filter during creation, because identifier is needed
        // but not sent by the frontend during creation (it sends the sku in the values)
        if (null !== $entity->getId() && $entity->isVariant()) {
            $data = $this->productAttributeFilter->filter($data);
        }

        $this->objectUpdater->update($entity, $data);
    }
}