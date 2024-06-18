<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;

class ProductModelFromDraftCreator implements ObjectFromDraftCreatorInterface
{
    /** @var AttributeFilterInterface */
    private $productModelAttributeFilter;

    /** @var SimpleFactoryInterface */
    private $productModelFactory;

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
        SimpleFactoryInterface $productModelFactory,
        ConverterInterface $productValueConverter,
        AttributeConverterInterface $localizedConverter,
        UserContext $userContext,
        FilterInterface $emptyValuesFilter,
        ObjectUpdaterInterface $objectUpdater,
        AttributeFilterInterface $productModelAttributeFilter,
        DraftValuesWithMissingAttributeFilter $draftValuesWithMissingAttributeFilter
    ) {
        $this->productValueConverter = $productValueConverter;
        $this->localizedConverter = $localizedConverter;
        $this->userContext = $userContext;
        $this->emptyValuesFilter = $emptyValuesFilter;
        $this->productModelAttributeFilter = $productModelAttributeFilter;
        $this->objectUpdater = $objectUpdater;
        $this->productModelFactory = $productModelFactory;
        $this->draftValuesWithMissingAttributeFilter = $draftValuesWithMissingAttributeFilter;
    }

    public function createForSaveForDraftForExistingObject(
        DraftInterface $draft
    ): ?EntityWithAssociationsInterface {
        /** @var ExistingProductModelDraft $draft */
        $productModel = $draft->getProductModel();
        if (!$productModel) {
            return null;
        }
        $data = $draft->getProductData();
        if (isset($data['values'])) {
            $this->updateObject($productModel, $data);
        }

        return $productModel;
    }

    public function createNewObject(DraftInterface $draft): EntityWithAssociationsInterface
    {
        $data = $draft->getProductData();
        /** @var ProductModelInterface $productModel */
        $productModel = $this->productModelFactory->create();
        $this->objectUpdater->update($productModel, $data);

        return $productModel;
    }

    public function updateObject(EntityWithAssociationsInterface $entity, array $data): void
    {
        unset($data['parent']);
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

        if (!$entity->isRoot()) {
            $data = $this->productModelAttributeFilter->filter($data);
        }

        $this->objectUpdater->update($entity, $data);
    }
}