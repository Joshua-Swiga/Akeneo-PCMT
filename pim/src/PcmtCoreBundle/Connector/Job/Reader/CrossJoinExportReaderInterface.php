<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Reader;

use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;

interface CrossJoinExportReaderInterface extends ItemReaderInterface
{
    /**
     * @return mixed|null
     *
     * @throws InvalidItemException if there is a problem reading the current record
     *                              (but the next one may still be valid)
     */
    public function readCross();

    public function setFamilyToCrossRead(string $familyToCrossRead): void;
}