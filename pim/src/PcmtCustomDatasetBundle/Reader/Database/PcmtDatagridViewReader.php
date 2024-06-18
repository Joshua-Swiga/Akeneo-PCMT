<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Reader\Database;

use Akeneo\Tool\Component\Connector\Reader\Database\AbstractReader;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepository;

/**
 * Category reader that reads categories ordered by tree and order inside the tree
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PcmtDatagridViewReader extends AbstractReader
{
    /** @var DatagridViewRepository */
    protected $datagridViewRepository;

    public function __construct(
        DatagridViewRepository $datagridViewRepository
    ) {
        $this->datagridViewRepository = $datagridViewRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResults(): \ArrayIterator
    {
        return new \ArrayIterator($this->datagridViewRepository->findAll());
    }
}
