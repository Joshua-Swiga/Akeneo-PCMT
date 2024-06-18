<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Provider;

use Akeneo\Platform\VersionProviderInterface;

class PcmtVersionProvider implements VersionProviderInterface
{
    private const KEY_MAJOR = 'major';
    private const KEY_MINOR = 'minor';
    private const KEY_PATCH = 'patch';
    private const KEY_VERSION = 'version';
    private const KEY_SHA = 'sha';

    /** @var string */
    private $major;

    /** @var string */
    private $minor;

    /** @var string */
    private $patch;

    /** @var string */
    private $version;

    /** @var string */
    private $sha;

    public function __construct(string $version)
    {
        $matches = [];

        preg_match(
            sprintf(
                '/^(?P<%s>0|[1-9]\d*)\.(?P<%s>0|[1-9]\d*)\.(?P<%s>0|[1-9]\d*)\-?(?P<%s>[a-zA-Z]+)?\-?(?P<%s>sha[a-zA-Z0-9]+)?$/',
                self::KEY_MAJOR,
                self::KEY_MINOR,
                self::KEY_PATCH,
                self::KEY_VERSION,
                self::KEY_SHA
            ),
            $version,
            $matches
        );

        $this->major = $matches[self::KEY_MAJOR] ?? '';
        $this->minor = $matches[self::KEY_MINOR] ?? '';
        $this->patch = $matches[self::KEY_PATCH] ?? '';
        $this->version = $matches[self::KEY_VERSION] ?? 'stable';
        $this->sha = $matches[self::KEY_SHA] ?? '';
    }

    public function getEdition(): string
    {
        return 'PCMT';
    }

    public function getMajor(): string
    {
        return $this->major;
    }

    public function getMinor(): string
    {
        return $this->minor;
    }

    public function getPatch(): string
    {
        return $this->patch;
    }

    public function getStability(): string
    {
        return $this->version;
    }

    public function getSha(): string
    {
        return $this->sha;
    }

    public function getFullVersion(): string
    {
        return sprintf(
            '%s %s.%s.%s %s%s',
            $this->getEdition(),
            $this->getMajor(),
            $this->getMinor(),
            $this->getPatch(),
            ucfirst($this->getStability()),
            '' !== $this->getSha() ? " ({$this->getSha()})" : ''
        );
    }
}