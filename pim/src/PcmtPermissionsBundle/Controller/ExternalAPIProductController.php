<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi\ProductController as AkeneoProductController;
use PcmtPermissionsBundle\Exception\NoCategoryAccessException;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ExternalAPIProductController extends AkeneoProductController
{
    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    /** {@inheritdoc} */
    public function deleteAction($code): Response
    {
        try {
            return parent::deleteAction($code);
        } catch (NoCategoryAccessException $e) {
            throw new AccessDeniedHttpException('No category permission');
        }
    }

    /** {@inheritdoc} */
    public function partialUpdateAction(Request $request, $code): Response
    {
        $product = $this->productRepository->findOneByIdentifier($code);
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