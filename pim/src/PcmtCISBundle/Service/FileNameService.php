<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Service;

class FileNameService
{
    private const DESTINATION = 'GS1Engine';
    private const MESSAGE_TYPE = 'GDSNSubscription';
    private const VERSION = '1.0';

    /** @var FileUniqueIdentifierGenerator */
    private $uniqueIdentifierGenerator;

    /** @var string */
    private $source;

    public function __construct(
        FileUniqueIdentifierGenerator $uniqueIdentifierGenerator,
        string $source
    ) {
        $this->uniqueIdentifierGenerator = $uniqueIdentifierGenerator;
        $this->source = $source;
    }

    public function get(): string
    {
        $uniqueIdentifier = $this->uniqueIdentifierGenerator->generate();

        return sprintf(
            '%s_%s_%s_%s_%s.txt',
            $this->source,
            self::DESTINATION,
            self::MESSAGE_TYPE,
            self::VERSION,
            $uniqueIdentifier
        );
    }
}