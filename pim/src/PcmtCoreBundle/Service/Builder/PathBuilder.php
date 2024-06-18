<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Service\Builder;

class PathBuilder
{
    /** @var string */
    private $path;

    public const PATH_DELIMITER = '/';

    public const FILE_DELIMITER = '.';

    public function __construct(string $path = '')
    {
        $this->path = $path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFileName(bool $withExtension = true): string
    {
        $fileNameArray = explode(self::PATH_DELIMITER, $this->path);
        $fileName = end($fileNameArray);
        if (false === $withExtension) {
            $fileName = $this->removeLastItemFromString(self::FILE_DELIMITER, $fileName);
        }

        return $fileName;
    }

    public function removeLastItemFromString(string $delimiter, string $path): string
    {
        $path = explode($delimiter, $path);
        array_pop($path);

        return implode($delimiter, $path);
    }

    public function getFileNameWithTime(string $fileName): string
    {
        $dt = new \DateTime();

        return $dt->format('Y-m-d H:i:s:u') . ' ' . $fileName;
    }
}