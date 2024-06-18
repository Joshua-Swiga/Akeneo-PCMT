<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;

class SelectOptionsRemover
{
    /** @var RemoverInterface */
    private $optionRemover;

    public function __construct(RemoverInterface $optionRemover)
    {
        $this->optionRemover = $optionRemover;
    }

    public function remove(StepExecution $stepExecution, AttributeInterface $attribute): void
    {
        // cleaning current options...
        foreach ($attribute->getOptions() as $option) {
            $stepExecution->incrementSummaryInfo('options_found_and_removed', 1);
            $attribute->removeOption($option);
            $this->optionRemover->remove($option);
        }
    }
}