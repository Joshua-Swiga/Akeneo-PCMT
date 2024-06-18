<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Writer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Xlsx\ProductWriter;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use PcmtCoreBundle\Connector\Job\Writer\File\E2OpenFlatItemBufferFlusher;

class E2OpenWriter extends ProductWriter implements CrossJoinExportWriterInterface
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'identifier' => $item['identifier'],
                'values'     => $item['values'],
            ];
        }

        parent::write($result);
    }

    public function setFlatItemBufferFlusher(E2OpenFlatItemBufferFlusher $flatItemBufferFlusher): void
    {
        $this->flusher = $flatItemBufferFlusher;
    }

    /**
     * {@inheritdoc}
     */
    public function writeCross(array $items, array $crossItems): void
    {
        $result = [];
        foreach ($items as $item) {
            foreach ($crossItems as $crossItem) {
                unset($crossItem['values']['sku']);
                $result[] = [
                    'identifier' => $item['identifier'],
                    'values'     => array_merge_recursive($item['values'], $crossItem['values']),
                ];
            }
        }
        try {
            $this->write($result);
        } catch (\Throwable $exception) {
            throw new InvalidItemException(
                $exception->getMessage(),
                new DataInvalidItem($result)
            );
        }
    }
}
