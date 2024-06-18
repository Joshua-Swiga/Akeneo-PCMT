<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\MassActions;

use Akeneo\Pim\Enrichment\Bundle\MassEditAction\Operation\BatchableOperationInterface;

class DraftsBulkActionOperation implements BatchableOperationInterface
{
    public const KEY_EXCLUDED = 'excluded';
    public const KEY_SELECTED = 'selected';
    public const KEY_ALL_SELECTED = 'allSelected';
    public const KEY_USER_TO_NOTIFY = 'user_to_notify';
    public const KEY_IS_USER_AUTHENTICATED = 'is_user_authenticated';

    /** @var string The background job code to launch */
    protected $jobInstanceCode;

    /** @var bool */
    protected $allSelected;

    /** @var int[] */
    protected $selected = [];

    /** @var int[] */
    protected $excluded = [];

    public function __construct(string $jobInstanceCode, bool $allSelected, array $selected, array $excluded)
    {
        $this->jobInstanceCode = $jobInstanceCode;
        $this->allSelected = $allSelected;
        $this->selected = $selected;
        $this->excluded = $excluded;
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchConfig(): array
    {
        return [
            self::KEY_ALL_SELECTED => $this->allSelected,
            self::KEY_SELECTED     => $this->selected,
            self::KEY_EXCLUDED     => $this->excluded,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getJobInstanceCode(): string
    {
        return $this->jobInstanceCode;
    }
}