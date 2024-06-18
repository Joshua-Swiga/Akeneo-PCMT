<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Reader\File;

use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;

interface ReferenceDataXmlReaderInterface extends FileReaderInterface
{
    public function setFilePath(string $filePath): void;
}