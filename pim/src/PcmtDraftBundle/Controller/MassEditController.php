<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtDraftBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\MassEditController as OriginalMassEditController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MassEditController extends OriginalMassEditController
{
    /**
     * {@inheritdoc}
     */
    public function launchAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['operation']) && 'change_family' === $data['operation']) {
            return new JsonResponse(null, 403);
        }

        return parent::launchAction($request);
    }
}
