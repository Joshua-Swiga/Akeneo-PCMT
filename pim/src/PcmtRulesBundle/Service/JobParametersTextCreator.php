<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Tool\Component\Batch\Job\JobParameters;

class JobParametersTextCreator
{
    public function create(JobParameters $jobParameters): string
    {
        return $this->getTextFromArray($jobParameters->all());
    }

    private function getTextFromArray(array $array): string
    {
        $text = [];
        foreach ($array as $key => $value) {
            $text[] = sprintf(
                '%s : %s',
                $key,
                is_array($value) ? ' { '. $this->getTextFromArray($value) .' } ' : $value
            );
        }

        return implode(', ', $text);
    }
}