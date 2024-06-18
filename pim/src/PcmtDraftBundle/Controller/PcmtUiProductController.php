<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\Ui\ProductController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PcmtUiProductController extends ProductController
{
    /**
     * Toggling product status is disabled
     *
     * {@inheritdoc}
     */
    public function toggleStatusAction($id): Response
    {
        return new JsonResponse(
            [
                'successful' => false,
            ],
            400
        );
    }
}