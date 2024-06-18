<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductModelController;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\EntityWithValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtDraftBundle\Normalizer\PcmtProductModelNormalizer;
use PcmtDraftBundle\Service\Builder\ResponseBuilder;
use PcmtDraftBundle\Service\Draft\DraftCreatorInterface;
use PcmtDraftBundle\Service\Helper\SpecialCategoryUpdater;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Code copied from Product Model Controller:
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PcmtProductModelController extends ProductModelController
{
    /** @var UserContext */
    private $userContext;

    /** @var ObjectFilterInterface */
    private $objectFilter;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var SaverInterface */
    protected $draftSaver;

    /** @var ResponseBuilder */
    private $responseBuilder;

    /** @var NormalizerInterface */
    private $violationNormalizer;

    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    /** @var DraftCreatorInterface */
    private $draftCreator;

    /** @var SpecialCategoryUpdater */
    private $specialCategoryUpdater;

    /** @var SimpleFactoryInterface */
    private $productModelFactory;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        NormalizerInterface $normalizer,
        UserContext $userContext,
        ObjectFilterInterface $objectFilter,
        AttributeConverterInterface $localizedConverter,
        EntityWithValuesFilter $emptyValuesFilter,
        ConverterInterface $productValueConverter,
        ObjectUpdaterInterface $productModelUpdater,
        RemoverInterface $productModelRemover,
        ValidatorInterface $validator,
        SaverInterface $productModelSaver,
        NormalizerInterface $constraintViolationNormalizer,
        NormalizerInterface $entityWithFamilyVariantNormalizer,
        SimpleFactoryInterface $productModelFactory,
        NormalizerInterface $violationNormalizer,
        FamilyVariantRepositoryInterface $familyVariantRepository,
        AttributeFilterInterface $productModelAttributeFilter,
        ?Client $productModelClient = null,
        ?Client $productAndProductModelClient = null
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->userContext = $userContext;
        $this->objectFilter = $objectFilter;
        $this->violationNormalizer = $violationNormalizer;
        $this->productModelFactory = $productModelFactory;
        $this->productModelUpdater = $productModelUpdater;
        $this->validator = $validator;
        $this->productModelSaver = $productModelSaver;
        $this->normalizer = $normalizer;

        parent::__construct(
            $productModelRepository,
            $normalizer,
            $userContext,
            $objectFilter,
            $localizedConverter,
            $emptyValuesFilter,
            $productValueConverter,
            $productModelUpdater,
            $productModelRemover,
            $validator,
            $productModelSaver,
            $constraintViolationNormalizer,
            $entityWithFamilyVariantNormalizer,
            $productModelFactory,
            $violationNormalizer,
            $familyVariantRepository,
            $productModelAttributeFilter,
            $productModelClient,
            $productAndProductModelClient
        );
    }

    public function setResponseBuilder(ResponseBuilder $responseBuilder): void
    {
        $this->responseBuilder = $responseBuilder;
    }

    public function setDraftSaver(SaverInterface $draftSaver): void
    {
        $this->draftSaver = $draftSaver;
    }

    public function setDraftCreator(DraftCreatorInterface $draftCreator): void
    {
        $this->draftCreator = $draftCreator;
    }

    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productModel = $this->productModelFactory->create();
        $content = json_decode($request->getContent(), true);

        $this->productModelUpdater->update($productModel, $content);

        $this->specialCategoryUpdater->addSpecialCategory($productModel);

        $violations = $this->validator->validate($productModel);

        if (count($violations) > 0) {
            $normalizedViolations = [];
            foreach ($violations as $violation) {
                $normalizedViolations[] = $this->violationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['product_model' => $productModel]
                );
            }

            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        $this->productModelSaver->save($productModel);
        $normalizedProductModel = $this->normalizeProductModel($productModel);

        return new JsonResponse($normalizedProductModel);
    }

    private function normalizeProductModel(ProductModelInterface $productModel): array
    {
        $normalizationContext = $this->userContext->toArray() + [
            'filter_types' => [],
        ];

        return $this->normalizer->normalize(
            $productModel,
            'internal_api',
            $normalizationContext
        );
    }

    /**
     * {@inheritdoc}
     */
    public function postAction(Request $request, $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        /** @var ProductModel $productModel */
        $productModel = $this->productModelRepository->find($id);

        $this->hasAccessOr403($productModel, CategoryPermissionsCheckerInterface::EDIT_LEVEL);

        $productModel = $this->objectFilter->filterObject($productModel, 'pim.internal_api.product.view') ?
            null :
            $productModel;

        if (null === $productModel) {
            throw new NotFoundHttpException(
                sprintf('ProductModel with id %s could not be found.', $id)
            );
        }

        $data = json_decode($request->getContent(), true);

        $fields = [
            'created',
            'updated',
            PcmtProductModelNormalizer::PERMISSION_TO_EDIT,
        ];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }

        $draft = $this->draftCreator->create(
            $productModel,
            $data,
            $this->userContext->getUser()
        );

        $this->draftSaver->save($draft);

        return $this->responseBuilder->setData($draft)
            ->setFormat('internal_api')
            ->setContext($this->getNormalizationContext())
            ->build();
    }

    public function getAction(int $id): JsonResponse
    {
        $productModel = $this->findProductModelOr404($id);

        $this->hasAccessOr403($productModel, CategoryPermissionsCheckerInterface::VIEW_LEVEL);

        return $this->responseBuilder->setData($productModel)
            ->setFormat('internal_api')
            ->setContext($this->getNormalizationContext() + [
                'include_draft_id'                                       => true,
                PcmtProductModelNormalizer::INCLUDE_CATEGORY_PERMISSIONS => true,
            ])
            ->build();
    }

    private function getNormalizationContext(): array
    {
        return $this->userContext->toArray() + [
            'filter_types' => [],
        ];
    }

    protected function hasAccessOr403(CategoryAwareInterface $entity, string $level): void
    {
        if (!$this->categoryPermissionsChecker->hasAccessToProduct($level, $entity)) {
            throw new AccessDeniedHttpException('Access denied basing on categories');
        }
    }

    public function setCategoryPermissionsChecker(CategoryPermissionsCheckerInterface $categoryPermissionsChecker): void
    {
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
    }

    public function setSpecialCategoryUpdater(SpecialCategoryUpdater $specialCategoryUpdater): void
    {
        $this->specialCategoryUpdater = $specialCategoryUpdater;
    }
}