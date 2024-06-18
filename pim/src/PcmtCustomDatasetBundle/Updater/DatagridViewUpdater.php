<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Updater;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PcmtCustomDatasetBundle\Exception\UserMissingException;

/**
 * Update the datagrid view properties
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridViewUpdater implements ObjectUpdaterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $userRepository;

    /** @var ObjectUpdaterInterface */
    private $baseDatagridViewUpdater;

    public function __construct(
        IdentifiableObjectRepositoryInterface $userRepository,
        ObjectUpdaterInterface $baseDatagridViewUpdater
    ) {
        $this->userRepository = $userRepository;
        $this->baseDatagridViewUpdater = $baseDatagridViewUpdater;
    }

    /**
     * @param object|DatagridView $datagridView
     */
    public function update($datagridView, array $data, array $options = []): ObjectUpdaterInterface
    {
        $user = $this->userRepository->findOneByIdentifier($data['owner']);
        if (null === $user) {
            throw new UserMissingException($data['owner']);
        }
        $this->baseDatagridViewUpdater->update($datagridView, $data, $options);

        return $this;
    }
}
