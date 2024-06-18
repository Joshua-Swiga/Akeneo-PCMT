<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Validator\Directory;

use PcmtCoreBundle\Validator\Directory\Provider\ValidPathProvider;

class DirectoryPathValidator
{
    /** @var mixed[] */
    protected $configProviders = [];

    public function __construct()
    {
        $this->configProviders = [new ValidPathProvider()];
    }

    /**
     * @param array|string|null $value
     */
    public function validate(string $key, $value): bool
    {
        $configuration = [];
        foreach ($this->configProviders as $configProvider) {
            $configuration = array_merge($configuration, $configProvider->getConfig());
        }

        if (!array_key_exists($key, $configuration)) {
            throw new \InvalidArgumentException(sprintf('Key %s either not valid or not registered', $key));
        }

        if (!is_array($value)) {
            return mb_strpos($configuration[$key], $value);
        }

        if (is_array($value)) {
            foreach ($configuration[$key] as $configValue) {
                if (mb_strpos($configValue, $value)) {
                    return true;
                }
            }
        }

        return false;
    }
}