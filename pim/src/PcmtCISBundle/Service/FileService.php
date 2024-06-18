<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Service;

use PcmtCISBundle\Entity\Subscription;

class FileService
{
    public const DOCUMENT_COMMAND_TYPE_ADD = 'ADD';
    public const DOCUMENT_COMMAND_TYPE_DELETE = 'DELETE';

    /** @var FileCommandService */
    private $fileCommandService;

    /**
     * FileService constructor.
     */
    public function __construct(FileCommandService $fileCommandService)
    {
        $this->fileCommandService = $fileCommandService;
    }

    public function createFileCommandAdd(Subscription $subscription): void
    {
        $this->fileCommandService->createFile($subscription, self::DOCUMENT_COMMAND_TYPE_ADD);
    }

    public function createFileCommandDelete(Subscription $subscription): void
    {
        $this->fileCommandService->createFile($subscription, self::DOCUMENT_COMMAND_TYPE_DELETE);
    }
}