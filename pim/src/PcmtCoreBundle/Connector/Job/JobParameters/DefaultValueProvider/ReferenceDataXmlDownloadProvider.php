<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PcmtSharedBundle\Connector\Job\JobParameters\SupportedJobsTrait;

class ReferenceDataXmlDownloadProvider implements DefaultValuesProviderInterface
{
    use SupportedJobsTrait;

    /** @var string */
    protected $fileDirectory;

    /** @var string */
    protected $refDirName;

    public const CONFIG_FILE_NAME = 'ReferenceDataXmlDownloadConfig.yml';

    public function __construct(
        array $supportedJobNames,
        string $refDirName,
        ?string $fileDirectory = null
    ) {
        $this->supportedJobNames = $supportedJobNames;
        $this->refDirName = $refDirName;
        $this->fileDirectory = $fileDirectory ?? 'reference_data/';
    }

    public function getDefaultValues(): array
    {
        return [
            'dirPath'  => 'src/PcmtCoreBundle/Resources/' . $this->fileDirectory . $this->refDirName . '/',
            'filePath' => 'any_path.xml',
        ];
    }
}