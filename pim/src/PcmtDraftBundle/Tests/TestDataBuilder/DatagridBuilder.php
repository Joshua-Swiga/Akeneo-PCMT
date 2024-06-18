<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;

class DatagridBuilder
{
    private const EXAMPLE_NAME = 'Example name';

    /** @var Datagrid */
    private $datagrid;

    public function __construct()
    {
        $this->datagrid = new Datagrid(self::EXAMPLE_NAME, (new AcceptorBuilder())->build());
    }

    public function build(): Datagrid
    {
        return $this->datagrid;
    }
}