<?php

declare(strict_types=1);

namespace FhirBundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi\ProductModelController;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQueryHandler;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ListProductModelsQueryValidator;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Security\PrimaryKeyEncrypter;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityRepository;
use Elasticsearch\Common\Exceptions\ServerErrorResponseException;
use FhirBundle\Normalizer\ExternalApi\ConnectorProductModelNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * Copyright (c) 2022, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
class FhirProductModelController extends ProductModelController
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbSearchAfterFactory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var PaginatorInterface */
    protected $offsetPaginator;

    /** @var PaginatorInterface */
    protected $searchAfterPaginator;

    /** @var PrimaryKeyEncrypter */
    protected $primaryKeyEncrypter;

    /** @var [] */
    protected $apiConfiguration = [];

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var SaverInterface */
    protected $saver;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var ValidatorInterface */
    protected $productModelValidator;

    /** @var AttributeFilterInterface */
    protected $productModelAttributeFilter;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productModelRepository;

    /** @var StreamResourceResponse */
    protected $partialUpdateStreamResource;

    /** @var QueryParametersCheckerInterface */
    protected $queryParametersChecker;

    /** @var ListProductModelsQueryValidator */
    private $listProductModelsQueryValidator;

    /** @var ListProductModelsQueryHandler */
    private $listProductModelsQueryHandler;

    /** @var ConnectorProductModelNormalizer */
    private $connectorProductModelNormalizer;

    /** @var GetConnectorProductModels */
    private $getConnectorProductModels;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EntityRepository */
    private $entityRepository;

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderFactoryInterface $pqbSearchAfterFactory,
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        PaginatorInterface $offsetPaginator,
        PaginatorInterface $searchAfterPaginator,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        ObjectUpdaterInterface $updater,
        SimpleFactoryInterface $factory,
        SaverInterface $saver,
        UrlGeneratorInterface $router,
        ValidatorInterface $productModelValidator,
        AttributeFilterInterface $productModelAttributeFilter,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        StreamResourceResponse $partialUpdateStreamResource,
        ListProductModelsQueryValidator $listProductModelsQueryValidator,
        ListProductModelsQueryHandler $listProductModelsQueryHandler,
        ConnectorProductModelNormalizer $connectorProductModelNormalizer,
        GetConnectorProductModels $getConnectorProductModels,
        TokenStorageInterface $tokenStorage,
        array $apiConfiguration,
        EntityRepository $entityRepository
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->pqbSearchAfterFactory = $pqbSearchAfterFactory;
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->offsetPaginator = $offsetPaginator;
        $this->searchAfterPaginator = $searchAfterPaginator;
        $this->primaryKeyEncrypter = $primaryKeyEncrypter;
        $this->updater = $updater;
        $this->factory = $factory;
        $this->saver = $saver;
        $this->router = $router;
        $this->productModelValidator = $productModelValidator;
        $this->productModelAttributeFilter = $productModelAttributeFilter;
        $this->productModelRepository = $productModelRepository;
        $this->partialUpdateStreamResource = $partialUpdateStreamResource;
        $this->listProductModelsQueryValidator = $listProductModelsQueryValidator;
        $this->listProductModelsQueryHandler = $listProductModelsQueryHandler;
        $this->connectorProductModelNormalizer = $connectorProductModelNormalizer;
        $this->getConnectorProductModels = $getConnectorProductModels;
        $this->tokenStorage = $tokenStorage;
        $this->apiConfiguration = $apiConfiguration;
        $this->entityRepository = $entityRepository;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getAction(string $code): JsonResponse
    {
        try {
            $user = $this->tokenStorage->getToken()->getUser();
            Assert::isInstanceOf($user, UserInterface::class);

            $productModel = $this->getConnectorProductModels->fromProductModelCode($code, $user->getId());
        } catch (ObjectNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Product model "%s" does not exist.', $code));
        }

        return new JsonResponse(
            $this->connectorProductModelNormalizer->normalizeConnectorProductModel($productModel)
        );
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws ServerErrorResponseException
     */
    public function listAction(Request $request): JsonResponse
    {
        $user = $this->tokenStorage->getToken()->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        $query = new ListProductModelsQuery();

        //Query Fhir mappings
        $fhir_mapping = $this->entityRepository->findAll();
        $attributes = [];
        foreach ($fhir_mapping as $mapping) {
            $attributes[] = $mapping->getCode();
        }
        //Query fhir attributes
        $query->attributeCodes = $attributes;
        if ($request->query->has('locales')) {
            $query->localeCodes = explode(',', $request->query->get('locales'));
        }
        if ($request->query->has('search')) {
            $query->search = json_decode($request->query->get('search'), true);
            if (!is_array($query->search)) {
                throw new UnprocessableEntityHttpException('Search query parameter should be valid JSON.');
            }
        }
        $query->channelCode = $request->query->get('scope', null);
        $query->limit = $request->query->get('limit', $this->apiConfiguration['pagination']['limit_by_default']);
        $query->paginationType = $request->query->get('pagination_type', PaginationTypes::OFFSET);
        $query->searchLocaleCode = $request->query->get('search_locale', null);
        $query->withCount = $request->query->get('with_count', 'false');
        $query->page = $request->query->get('page', 1);
        $query->searchChannelCode = $request->query->get('search_scope', null);
        $query->searchAfter = $request->query->get('search_after', null);
        $query->userId = $user->getId();

        try {
            $this->listProductModelsQueryValidator->validate($query);
            $productModels = $this->listProductModelsQueryHandler->handle($query); // in try block as PQB is doing validation also
        } catch (InvalidQueryException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (ServerErrorResponseException $e) {
            $message = json_decode($e->getMessage(), true);

            if (null !== $message && isset($message['error']['root_cause'][0]['type'])
                && 'query_phase_execution_exception' === $message['error']['root_cause'][0]['type']) {
                throw new DocumentedHttpException(
                    Documentation::URL_DOCUMENTATION . 'pagination.html#search-after-type',
                    'You have reached the maximum number of pages you can retrieve with the "page" pagination type. Please use the search after pagination type instead',
                    $e
                );
            }

            throw new ServerErrorResponseException($e->getMessage(), $e->getCode(), $e);
        }

        return new JsonResponse($this->normalizeProductModelsList($productModels, $query));
    }

    private function normalizeProductModelsList(ConnectorProductModelList $connectorProductModels, ListProductModelsQuery $query): array
    {
        $queryParameters = [
            'with_count'      => $query->withCount,
            'pagination_type' => $query->paginationType,
            'limit'           => $query->limit,
        ];

        if ([] !== $query->search) {
            $queryParameters['search'] = json_encode($query->search);
        }
        if (null !== $query->channelCode) {
            $queryParameters['scope'] = $query->channelCode;
        }
        if (null !== $query->searchChannelCode) {
            $queryParameters['search_scope'] = $query->searchChannelCode;
        }
        if (null !== $query->localeCodes) {
            $queryParameters['locales'] = implode(',', $query->localeCodes);
        }

        $bundle = [];

        if (PaginationTypes::OFFSET === $query->paginationType) {
            $queryParameters = ['page' => $query->page] + $queryParameters;

            $paginationParameters = [
                'query_parameters'    => $queryParameters,
                'list_route_name'     => 'pim_fhir_api_product_model_list',
                'item_route_name'     => 'pim_fhir_api_product_model_get',
                'item_identifier_key' => 'id',
            ];

            $count = $query->withCountAsBoolean() ? $connectorProductModels->totalNumberOfProductModels() : null;

            $paginated = $this->offsetPaginator->paginate(
                $this->connectorProductModelNormalizer->normalizeConnectorProductModelList($connectorProductModels),
                $paginationParameters,
                $count
            );
            //create fhir bundle output
            $bundle['resourceType'] = 'Bundle';
            if (array_key_exists('current_page', $paginated)) {
                $bundle['meta'] = [
                    'current_page' => $paginated['current_page'],
                ];
            }
            $bundle['type'] = 'searchset';
            $bundle['timestamp'] = date('c');
            $bundle['total'] = $connectorProductModels->totalNumberOfProductModels();
            foreach ($paginated['_links'] as $k => $l) {
                $bundle['link'][] = [
                    'relation' => $k,
                    'url'      => $l['href'],
                ];
            }
            foreach ($paginated['_embedded']['items'] as $v) {
                $entry = [];
                $entry['fullUrl'] = $v['_links']['self']['href'];
                unset($v['_links']);
                $entry['resource'] = $v;
                $entry['search'] = 'match';
                $bundle['entry'][] = $entry;
            }

            return $bundle;
        }
        $productModels = $connectorProductModels->connectorProductModels();
        $lastProductModel = end($productModels);

        $parameters = [
            'query_parameters'    => $queryParameters,
            'search_after'        => [
                'next' => false !== $lastProductModel ? $this->primaryKeyEncrypter->encrypt($lastProductModel->id()) : null,
                'self' => $query->searchAfter,
            ],
            'list_route_name'     => 'pim_fhir_api_product_model_list',
            'item_route_name'     => 'pim_fhir_api_product_model_get',
            'item_identifier_key' => 'id',
        ];

        $paginated = $this->searchAfterPaginator->paginate(
            $this->connectorProductModelNormalizer->normalizeConnectorProductModelList($connectorProductModels),
            $parameters,
            null
        );
        //create fhir bundle output
        $bundle['resourceType'] = 'Bundle';
        if (array_key_exists('current_page', $paginated)) {
            $bundle['meta'] = [
                'current_page' => $paginated['current_page'],
            ];
        }
        $bundle['type'] = 'searchset';
        $bundle['timestamp'] = date('c');
        $bundle['total'] = $connectorProductModels->totalNumberOfProductModels();
        foreach ($paginated['_links'] as $k => $l) {
            $bundle['link'][] = [
                'relation' => $k,
                'url'      => $l['href'],
            ];
        }
        foreach ($paginated['_embedded']['items'] as $v) {
            $entry = [];
            $entry['fullUrl'] = $v['_links']['self']['href'];
            unset($v['_links']);
            $entry['resource'] = $v;
            $entry['search'] = 'match';
            $bundle['entry'][] = $entry;
        }

        return $bundle;
    }
}
