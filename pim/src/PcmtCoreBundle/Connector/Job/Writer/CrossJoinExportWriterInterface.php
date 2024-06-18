<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Writer;

use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;

interface CrossJoinExportWriterInterface extends ItemWriterInterface
{
    /**
     * @throws InvalidItemException
     */
    public function writeCross(array $items, array $crossItems): void;
}