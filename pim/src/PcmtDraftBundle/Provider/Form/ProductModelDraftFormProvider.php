<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Provider\Form;

use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use PcmtDraftBundle\Entity\DraftInterface;

class ProductModelDraftFormProvider implements FormProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getForm($element): string
    {
        return 'pcmt-product-model-drafts-edit';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof DraftInterface;
    }
}