<?php
/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCISBundle\Formatter;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\DateTimeProperty as BaseDateTimeProperty;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class overridden to use user's timezone when displaying datetime in datagrid
 */
class DateTimeProperty extends BaseDateTimeProperty
{
    /** @var UserContext */
    private $userContext;

    public function __construct(
        TranslatorInterface $translator,
        PresenterInterface $presenter,
        UserContext $userContext
    ) {
        parent::__construct($translator, $presenter);

        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $result = !$value instanceof \DateTime ? new \DateTime($value) : $value;
        $timezone = $this->userContext ? $this->userContext->getUserTimezone() : null;

        return $this->presenter->present($result, [
            'locale'   => $this->translator->getLocale(),
            'timezone' => $timezone,
        ]);
    }
}
