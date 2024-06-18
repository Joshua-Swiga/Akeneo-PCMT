<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\InvalidItems;

use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;

class XmlInvalidItem implements InvalidItemInterface
{
    /** @var string[] */
    protected $invalidData = [];

    public function __construct(string $fileName)
    {
        $this->invalidData['file'] = $fileName;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidData()
    {
        return $this->invalidData;
    }
}