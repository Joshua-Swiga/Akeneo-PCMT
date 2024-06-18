<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi\ProductModelController as AkeneoProductModelController;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ExternalAPIProductModelController extends AkeneoProductModelController
{
    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    /** {@inheritdoc} */
    public function partialUpdateAction(Request $request, $code): Response
    {
        $product = $this->productModelRepository->findOneByIdentifier($code);
        if ($product) {
            if (!$this->categoryPermissionsChecker->hasAccessToProduct(CategoryPermissionsCheckerInterface::OWN_LEVEL, $product)) {
                throw new AccessDeniedHttpException('No category access');
            }
        }

        return parent::partialUpdateAction($request, $code);
    }

    public function setCategoryPermissionsChecker(CategoryPermissionsCheckerInterface $categoryPermissionsChecker): void
    {
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
    }
}