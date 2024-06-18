<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductController;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtDraftBundle\Normalizer\PcmtProductNormalizer;
use PcmtDraftBundle\Service\Builder\ResponseBuilder;
use PcmtDraftBundle\Service\Draft\DraftCreatorInterface;
use PcmtDraftBundle\Service\Helper\SpecialCategoryUpdater;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Code copied from Product Controller:
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PcmtProductController extends ProductController
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var SaverInterface */
    protected $draftSaver;

    /** @var ResponseBuilder */
    private $responseBuilder;

    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    /** @var DraftCreatorInterface */
    private $draftCreator;

    /** @var SpecialCategoryUpdater */
    private $specialCategoryUpdater;

    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['parent'])) {
            $product = $this->variantProductBuilder->createProduct(
                $data['identifier'] ?? null,
                $data['family'] ?? null
            );

            if (isset($data['values'])) {
                $this->updateProduct($product, $data);
            }
        } else {
            $product = $this->productBuilder->createProduct(
                $data['identifier'] ?? null,
                $data['family'] ?? null
            );
        }

        // add a special category
        $this->specialCategoryUpdater->addSpecialCategory($product);

        $violations = $this->validator->validate($product);

        if (0 === $violations->count()) {
            $this->productSaver->save($product);

            return new JsonResponse($this->normalizer->normalize(
                $product,
                'internal_api',
                $this->getNormalizationContext()
            ));
        }

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['product' => $product]
            );
        }

        return new JsonResponse(['values' => $normalizedViolations], 400);
    }

    /**
     * {@inheritdoc}
     */
    public function getAction($id)
    {
        $product = $this->findProductOr404($id);

        $this->hasAccessOr403($product, CategoryPermissionsCheckerInterface::VIEW_LEVEL);

        return $this->responseBuilder->setData($product)
            ->setContext($this->getNormalizationContext() + [
                'include_draft_id'                                  => true,
                PcmtProductNormalizer::INCLUDE_CATEGORY_PERMISSIONS => true,
            ])
            ->setFormat('internal_api')
            ->build();
    }

    /**
     * {@inheritdoc}
     */
    public function postAction(Request $request, $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($id);

        $this->hasAccessOr403($product, CategoryPermissionsCheckerInterface::EDIT_LEVEL);

        if ($this->objectFilter->filterObject($product, 'pim.internal_api.product.edit')) {
            throw new AccessDeniedHttpException();
        }
        $data = json_decode($request->getContent(), true);

        try {
            $data = $this->productEditDataFilter->filterCollection($data, null, ['product' => $product]);
        } catch (ObjectNotFoundException $e) {
            throw new BadRequestHttpException();
        }

        $fields = [
            'created',
            'updated',
            PcmtProductNormalizer::PERMISSION_TO_EDIT,
        ];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }

        $draft = $this->draftCreator->create(
            $product,
            $data,
            $this->userContext->getUser()
        );

        $this->draftSaver->save($draft);

        return $this->responseBuilder->setData($draft)->setFormat('internal_api')->setContext(
            $this->getNormalizationContext()
        )->build();
    }

    public function setDraftSaver(SaverInterface $draftSaver): void
    {
        $this->draftSaver = $draftSaver;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setResponseBuilder(ResponseBuilder $responseBuilder): void
    {
        $this->responseBuilder = $responseBuilder;
    }

    public function setCategoryPermissionsChecker(CategoryPermissionsCheckerInterface $categoryPermissionsChecker): void
    {
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
    }

    public function setDraftCreator(DraftCreatorInterface $draftCreator): void
    {
        $this->draftCreator = $draftCreator;
    }

    protected function hasAccessOr403(CategoryAwareInterface $entity, string $level): void
    {
        if (!$this->categoryPermissionsChecker->hasAccessToProduct($level, $entity)) {
            throw new AccessDeniedHttpException('Access denied basing on categories');
        }
    }

    public function setSpecialCategoryUpdater(SpecialCategoryUpdater $specialCategoryUpdater): void
    {
        $this->specialCategoryUpdater = $specialCategoryUpdater;
    }
}