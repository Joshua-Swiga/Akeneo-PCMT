<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtSharedBundle\Connector\Job\InvalidItems;

use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;

class SimpleInvalidItem implements InvalidItemInterface
{
    /** @var string[] */
    protected $invalidData = [];

    public function __construct(array $data = [])
    {
        $this->invalidData = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidData()
    {
        return $this->invalidData;
    }
}