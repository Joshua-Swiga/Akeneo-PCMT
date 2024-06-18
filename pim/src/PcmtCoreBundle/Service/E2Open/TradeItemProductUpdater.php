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
use PcmtCoreBundle\Entity\E2OpenAttributeData;
use Psr\Log\LoggerInterface;

class TradeItemProductUpdater
{
    /** @var E2OpenAttributesService */
    private $attributesService;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(E2OpenAttributesService $attributesService, ObjectUpdaterInterface $productUpdater, LoggerInterface $logger)
    {
        $this->attributesService = $attributesService;
        $this->productUpdater = $productUpdater;
        $this->logger = $logger;
    }

    /**
     * @param E2OpenAttributeData[] $dataToUpdate
     */
    public function update(ProductInterface $product, array $dataToUpdate): void
    {
        $valuesToUpdate = [];

        foreach ($dataToUpdate as $e2openAttributeData) {
            try {
                $valuesToUpdate = $this->processProductAttributeValue($product, $e2openAttributeData, $valuesToUpdate);
            } catch (\Throwable $exception) {
                $format = 'Processing key %s failed. Its value will be ignored. Details: %s';
                $this->logger->error(sprintf($format, $e2openAttributeData->getName(), $exception->getMessage()));
            }
        }

        $this->productUpdater->update(
            $product,
            [
                'values' => $valuesToUpdate,
            ]
        );
    }

    private function processProductAttributeValue(ProductInterface $product, E2OpenAttributeData $data, array $valuesToUpdate): array
    {
        $pcmtAttribute = $this->attributesService->getForCode($data->getCode());

        if (!$pcmtAttribute) {
            throw new \Exception('Attribute not found for ' . $data->getCode());
        }

        $unit = null;
        if (E2OpenAttributesService::MEASURE_UNIT === $pcmtAttribute->getMetricFamily()) {
            foreach ($data->getAttributes() as $name => $v) {
                if (false !== mb_stripos($name, 'measurementUnitCode')) {
                    if ($u = $this->attributesService->getMeasureUnitForSymbol($v)) {
                        $unit = $u;
                    }
                }
            }
        }

        $value = $data->getValue();
        if ($unit) {
            // if measurement unit found, this is a special metric field and we need to send array instead of string/int
            $value = [
                'unit'   => $unit,
                'amount' => $value,
            ];
        }

        $valuesToUpdate[$data->getCode()][] = [
            'data'   => $value,
            'locale' => null,
            'scope'  => null,
        ];

        return $valuesToUpdate;
    }
}