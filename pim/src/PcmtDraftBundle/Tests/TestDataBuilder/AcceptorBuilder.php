<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Oro\Bundle\DataGridBundle\Extension\Acceptor;

class AcceptorBuilder
{
    /** @var Acceptor */
    private $acceptor;

    public function __construct()
    {
        $this->acceptor = new Acceptor((new DatagridConfigurationBuilder())->build());
    }

    public function build(): Acceptor
    {
        return $this->acceptor;
    }
}