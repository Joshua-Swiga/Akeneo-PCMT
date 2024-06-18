<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Validator\Directory\Provider;

class ValidPathProvider
{
    public function getConfig(): array
    {
        return [
            'reference_data_files_path' => 'src/PcmtCoreBundle/Resources/config/reference_data',
        ];
    }
}
