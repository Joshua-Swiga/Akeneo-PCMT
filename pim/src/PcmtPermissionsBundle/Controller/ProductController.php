<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Controller;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use PcmtPermissionsBundle\Exception\NoCategoryAccessException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var RemoverInterface */
    protected $productRemover;

    /** @var Client */
    private $productClient;

    /** @var Client */
    private $productAndProductModelClient;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        RemoverInterface $productRemover,
        Client $productClient,
        Client $productAndProductModelClient
    ) {
        $this->productRepository = $productRepository;
        $this->productRemover = $productRemover;
        $this->productClient = $productClient;
        $this->productAndProductModelClient = $productAndProductModelClient;
    }

    public function removeAction(Request $request, int $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($id);

        try {
            $this->productRemover->remove($product);
        } catch (NoCategoryAccessException $exception) {
            return new JsonResponse(['message' => 'pcmt_permissions.product_datagrid.delete.error'], Response::HTTP_FORBIDDEN);
        }

        if (null !== $this->productClient && null !== $this->productAndProductModelClient) {
            $this->productClient->refreshIndex();
            $this->productAndProductModelClient->refreshIndex();
        }

        return new JsonResponse();
    }

    protected function findProductOr404(int $id): ProductInterface
    {
        $product = $this->productRepository->find($id);

        if (null === $product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %d could not be found.', $id)
            );
        }

        return $product;
    }
}
