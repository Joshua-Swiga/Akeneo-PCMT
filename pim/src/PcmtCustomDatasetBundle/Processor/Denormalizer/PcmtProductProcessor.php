<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\FindProductToImport;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductProcessor as OriginalProcessor;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/*******************************************************************************
 * Copyright (c) 2013, Akeneo SAS
 * Copyright (c) 2019, VillageReach
 * Licensed under the Open Software License version 3.0 AND Non-Profit Open
 * Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0 AND OSL-3.0
 *******************************************************************************/
class PcmtProductProcessor extends OriginalProcessor
{
    /** @var FindProductToImport */
    private $findProductToImport;

    /** @var AddParent */
    private $addParent;

    /** @var AttributeFilterInterface */
    private $productAttributeFilter;

    /** @var MediaStorer */
    private $mediaStorer;

    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        FindProductToImport $findProductToImport,
        AddParent $addParent,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        FilterInterface $productFilter,
        AttributeFilterInterface $productAttributeFilter,
        MediaStorer $mediaStorer
    ) {
        parent::__construct(
            $repository,
            $findProductToImport,
            $addParent,
            $updater,
            $validator,
            $detacher,
            $productFilter,
            $productAttributeFilter,
            $mediaStorer
        );

        $this->findProductToImport = $findProductToImport;
        $this->addParent = $addParent;
        $this->productAttributeFilter = $productAttributeFilter;
        $this->mediaStorer = $mediaStorer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item): ?ProductInterface
    {
        $itemHasStatus = isset($item['enabled']);
        if (!isset($item['enabled'])) {
            $item['enabled'] = $jobParameters = $this->stepExecution->getJobParameters()->get('enabled');
        }

        $identifier = $this->getIdentifier($item);

        if (null === $identifier || '' === $identifier) {
            $this->skipItemWithMessage($item, 'The identifier must be filled');
        }

        $parentProductModelCode = $item['parent'] ?? '';

        try {
            $familyCode = $this->getFamilyCode($item);
            $item['family'] = $familyCode;

            $item = $this->productAttributeFilter->filter($item);
            $filteredItem = $this->filterItemData($item);

            $product = $this->findProductToImport->fromFlatData($identifier, $familyCode);
        } catch (AccessDeniedException $e) {
            throw $this->skipItemAndReturnException($item, $e->getMessage(), $e);
        }

        if (false === $itemHasStatus && null !== $product->getId()) {
            unset($filteredItem['enabled']);
        }

        $jobParameters = $this->stepExecution->getJobParameters();
        $enabledComparison = $jobParameters->get('enabledComparison');
        if ($enabledComparison) {
            $filteredItem = $this->filterIdenticalData($product, $filteredItem);

            if (empty($filteredItem) && null !== $product->getId()) {
                $this->detachProduct($product);
                $this->stepExecution->incrementSummaryInfo('product_skipped_no_diff');

                return null;
            }
        }

        if ('' !== $parentProductModelCode && !$product->isVariant()) {
            try {
                $product = $this->addParent->to($product, $parentProductModelCode);
            } catch (\InvalidArgumentException $e) {
                $this->detachProduct($product);
                $this->skipItemWithMessage($item, $e->getMessage(), $e);
            }
        }

        if (isset($filteredItem['values'])) {
            try {
                $filteredItem['values'] = $this->mediaStorer->store($filteredItem['values']);
            } catch (InvalidPropertyException $e) {
                $this->detachProduct($product);
                $this->skipItemWithMessage($item, $e->getMessage(), $e);
            }
        }

        try {
            $this->updateProduct($product, $filteredItem);
        } catch (PropertyException $exception) {
            $this->detachProduct($product);
            $message = sprintf('%s: %s', $exception->getPropertyName(), $exception->getMessage());
            $this->skipItemWithMessage($item, $message, $exception);
        } catch (InvalidArgumentException | AccessDeniedException $exception) {
            $this->detachProduct($product);
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        return $product;
    }

    private function skipItemAndReturnException(array $item, string $message, ?\Throwable $previousException = null): InvalidItemException
    {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }
        $itemPosition = null !== $this->stepExecution ? $this->stepExecution->getSummaryInfo('item_position') : 0;
        $invalidItem = new FileInvalidItem($item, $itemPosition);

        return new InvalidItemException($message, $invalidItem, [], 0, $previousException);
    }
}
