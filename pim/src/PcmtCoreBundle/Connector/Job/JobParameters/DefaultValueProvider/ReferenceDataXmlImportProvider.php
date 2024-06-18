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
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ReferenceDataXmlImportProvider implements DefaultValuesProviderInterface
{
    use SupportedJobsTrait;

    /** @var string */
    protected $fileDirectory = 'Xml';

    public const ALL_FILES = 'ALL';

    public const DIR_PATH = 'src/PcmtCoreBundle/Resources/reference_data/gs1Codes/';

    public const WORK_DIR = 'tmp/';

    public const OLD_DIR = 'old/';

    public const FAILED_DIR = 'failed/';

    public function __construct(
        array $supportedJobNames
    ) {
        $this->supportedJobNames = $supportedJobNames;
    }

    public function getDefaultValues(): array
    {
        return [
            'filePath'      => self::ALL_FILES,
            'dirPath'       => self::DIR_PATH,
            'uploadAllowed' => [
                new Type('bool'),
                new IsTrue(['groups' => 'UploadExecution']),
            ],
            'decimalSeparator' => new NotBlank(),
            'xmlMapping'       => null,
        ];
    }
}