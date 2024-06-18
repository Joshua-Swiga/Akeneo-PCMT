<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class ImageVerificationService
{
    public function verifyIfSame(FileInfo $imageValue, \SplFileInfo $downloadedFile): bool
    {
        /** @var FileInfo $data */
        if ($imageValue->getOriginalFilename() !== $downloadedFile->getBasename() || $imageValue->getSize() !== $downloadedFile->getSize()) {
            return false;
        }

        return true;
    }
}