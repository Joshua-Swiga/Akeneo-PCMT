<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Tests\TestDataBuilder;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;

class DatagridViewBuilder
{
    public const EXAMPLE_OWNER_USERNAME = 'Example Owner';
    public const EXAMPLE_LABEL = 'Custom label';
    public const EXAMPLE_TYPE = 'My type';
    public const EXAMPLE_ALIAS = 'Some alias';
    public const EXAMPLE_COLUMNS = 'one,two,three,four';
    public const EXAMPLE_FILTERS = 'some';

    /** @var DatagridView */
    private $datagridView;

    public function __construct()
    {
        $this->datagridView = new DatagridView();
        $this->datagridView->setOwner(
            (new UserBuilder())
                ->withUsername(self::EXAMPLE_OWNER_USERNAME)
                ->build()
        );
        $this->datagridView->setFilters(self::EXAMPLE_FILTERS);
        $this->datagridView->setLabel(self::EXAMPLE_LABEL);
        $this->datagridView->setType(self::EXAMPLE_TYPE);
        $this->datagridView->setDatagridAlias(self::EXAMPLE_ALIAS);
        $this->datagridView->setColumns(explode(',', self::EXAMPLE_COLUMNS));
    }

    public function build(): DatagridView
    {
        return $this->datagridView;
    }

    public function withOwner(?UserInterface $owner): self
    {
        $this->datagridView->setOwner($owner);

        return $this;
    }
}