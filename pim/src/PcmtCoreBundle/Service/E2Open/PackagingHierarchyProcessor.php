<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Service\E2Open;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Psr\Log\LoggerInterface;

class PackagingHierarchyProcessor
{
    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ObjectUpdaterInterface $productUpdater,
        LoggerInterface $logger
    ) {
        $this->productUpdater = $productUpdater;
        $this->logger = $logger;
    }

    /**
     * @param ProductInterface[] $products
     */
    public function process(array $products): void
    {
        $this->logger->info('Processing Packaging Hierarchy Table');

        foreach ($products as $product) {
            $data = [];

            $this->getChildTradeItemValues(
                $product,
                $products,
                $data
            );

            $this->logger->info('Updating packaging hierarchy for: ' . $product->getId());

            $newDataEncoded = json_encode($data);

            $valuesToUpdate['GS1_PACKAGING_HIERARCHY']['data'] = [
                'data'   => $newDataEncoded,
                'locale' => null,
                'scope'  => null,
            ];

            $this->productUpdater->update(
                $product,
                [
                    'values' => $valuesToUpdate,
                ]
            );
        }
    }

    private function getChildTradeItemValues(
        ProductInterface $product,
        array $products,
        array &$data
    ): void {
        $row = [];
        $row['tradeItemUnitDescriptorCode'] = $product->getValue('GS1_TRADEITEMUNITDESCRIPTORCODE') ?
            $product->getValue('GS1_TRADEITEMUNITDESCRIPTORCODE')
                ->getData() : '';
        $row['tradeItemDescription'] = $product->getValue('GS1_TRADEITEMDESCRIPTION') ?
            $product->getValue('GS1_TRADEITEMDESCRIPTION')
                ->getData() : '';
        $row['GTIN'] = $product->getValue('GTIN') ?
            $product->getValue('GTIN')
                ->getData() : '';
        $row['quantityOfNextLowerLevelTradeItem'] = $product->getValue('GS1_QUANTITYOFNEXTLOWERLEVELTRADEITEM') ?
            (int) $product->getValue('GS1_QUANTITYOFNEXTLOWERLEVELTRADEITEM')
                ->getData() : '';
        $row['childTradeItem_gtin'] = $product->getValue('GS1_GTIN_CHILD_NEXTLOWERLEVELTRADEITEMINFORMATION') ?
            $product->getValue('GS1_GTIN_CHILD_NEXTLOWERLEVELTRADEITEMINFORMATION')
                ->getData() : '';
        $data[] = $row;

        if ($product->getValue('GS1_GTIN_CHILD_NEXTLOWERLEVELTRADEITEMINFORMATION')) {
            $childProduct = $this->findChildTradeItem(
                $products,
                $product->getValue(
                    'GS1_GTIN_CHILD_NEXTLOWERLEVELTRADEITEMINFORMATION'
                )
                    ->getData()
            );

            $this->getChildTradeItemValues(
                $childProduct,
                $products,
                $data
            );
        }
    }

    private function findChildTradeItem(
        array $products,
        string $expectedGtin
    ): ?ProductInterface {
        $result = array_values(
            array_filter(
                $products,
                function (ProductInterface $product) use (
                    $expectedGtin
                ) {
                    return $product->getValue('GTIN')
                        ->getData() === $expectedGtin;
                }
            )
        );

        return $result[0] ?? null;
    }
}