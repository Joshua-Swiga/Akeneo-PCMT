<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductQueryBuilderFactory;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtCoreBundle\Service\E2Open\E2OpenAttributesService;
use PcmtCoreBundle\Service\E2Open\PackagingHierarchyProcessor;
use PcmtCoreBundle\Service\E2Open\TradeItemDynamicMapping;
use PcmtCoreBundle\Service\E2Open\TradeItemProductUpdater;
use PcmtCoreBundle\Service\E2Open\TradeItemXmlProcessor;
use PcmtCoreBundle\Util\Adapter\FileGetContentsWrapper;
use Psr\Log\LoggerInterface;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class E2OpenFromXmlTasklet implements TaskletInterface
{
    private const CATEGORY_CODE_FOR_IMPORTED_ITEMS = 'GS1';

    /** @var StepExecution */
    protected $stepExecution;

    /** @var Service */
    private $xmlReader;

    /** @var mixed */
    private $item;

    /** @var SaverInterface */
    private $productSaver;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var TradeItemXmlProcessor */
    private $nodeProcessor;

    /** @var TradeItemProductUpdater */
    private $tradeItemProductUpdater;

    /** @var ProductQueryBuilderFactory */
    private $pqbFactory;

    /** @var LoggerInterface */
    private $logger;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var PackagingHierarchyProcessor */
    private $packagingHierarchyProcessor;

    /** @var TradeItemDynamicMapping */
    private $tradeItemDynamicMapping;

    /** @var ProductInterface[] */
    private $products = [];

    public function __construct(
        SaverInterface $productSaver,
        ProductBuilderInterface $productBuilder,
        TradeItemXmlProcessor $nodeProcessor,
        ProductQueryBuilderFactory $pqbFactory,
        LoggerInterface $logger,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        FamilyRepositoryInterface $familyRepository,
        PackagingHierarchyProcessor $packagingHierarchyProcessor,
        TradeItemProductUpdater $tradeItemProductUpdater,
        TradeItemDynamicMapping $tradeItemDynamicMapping
    ) {
        $this->xmlReader = new Service();
        $this->productSaver = $productSaver;
        $this->productBuilder = $productBuilder;
        $this->nodeProcessor = $nodeProcessor;
        $this->pqbFactory = $pqbFactory;
        $this->logger = $logger;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->familyRepository = $familyRepository;
        $this->packagingHierarchyProcessor = $packagingHierarchyProcessor;
        $this->tradeItemProductUpdater = $tradeItemProductUpdater;
        $this->tradeItemDynamicMapping = $tradeItemDynamicMapping;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $filePath = $this->stepExecution->getJobParameters()
            ->get('xmlFilePath');
        $this->products = [];

        $this->processFile($filePath);
        $this->packagingHierarchyProcessor->process($this->products);

        foreach ($this->products as $product) {
            $this->productSaver->save($product);
        }
    }

    private function processFile(string $filePath): void
    {
        $fileGetContentsWrapper = new FileGetContentsWrapper();
        $xmlInput = $fileGetContentsWrapper->fileGetContents($filePath);
        $xmlHeader = '{http://www.unece.org/cefact/namespaces/StandardBusinessDocumentHeader}StandardBusinessDocumentHeader';
        $xmlProductInformationRootNode = '{}tradeItem';
        $this->xmlReader->elementMap = [
            $xmlHeader                     => 'Sabre\Xml\Deserializer\enum',
            $xmlProductInformationRootNode => function (
                Reader $reader
            ): void {
                $subTree = $reader->parseInnerTree();
                foreach ($subTree as $element) {
                    if ('{}gtin' === $element['name']) {
                        $this->item = $this->instantiateProduct($element);
                        $this->nodeProcessor->setFoundAttributes([]);
                        break;
                    }
                }
                if (!$this->item) {
                    throw new \Exception('No item has been created.');
                }

                $this->stepExecution->incrementSummaryInfo('TradeItemsProcessed');

                array_walk(
                    $subTree,
                    function ($element): void {
                        $this->nodeProcessor->processNode($element);
                    }
                );

                $data = $this->tradeItemDynamicMapping->process($this->nodeProcessor->getFoundAttributes());

                $this->tradeItemProductUpdater->update($this->item, $data);

                $category = $this->categoryRepository->findOneByIdentifier(self::CATEGORY_CODE_FOR_IMPORTED_ITEMS);
                if ($category) {
                    $this->item->addCategory($category);
                }

                $this->productSaver->save($this->item);

                $this->products[$this->item->getId()] = $this->item;
            },
        ];

        $this->stepExecution->incrementSummaryInfo('TradeItemsProcessed', 0);

        $this->xmlReader->parse($xmlInput);

        $this->stepExecution->incrementSummaryInfo('UniqueProductsProcessed', count($this->products));
    }

    private function instantiateProduct(array $element): ProductInterface
    {
        $gtinValue = $element['value'];
        $this->logger->info('Instantiating for GTIN: '. $gtinValue);

        $product = $this->findProductForGTIN($gtinValue);
        if ($product) {
            $this->stepExecution->incrementSummaryInfo('ProductsFoundByGTIN');
            $this->logger->info('Product in GS1_GDSN family found, id: '. $product->getId());

            return $product;
        }

        $this->stepExecution->incrementSummaryInfo('NewProductsCreated');

        $this->logger->info('Product not found in GS1_GDSN family, creating new');

        $uniqueId = microtime() . '-'. $gtinValue;

        return $this->createNewProductInstance($uniqueId);
    }

    private function findProductForGTIN(string $gtinValue): ?ProductInterface
    {
        // first, look in ElasticSearch index
        $pqb = $this->pqbFactory->create([
            'default_locale' => null,
            'default_scope'  => null,
        ]);
        $pqb->addFilter('GTIN', '=', $gtinValue);
        $pqb->addFilter('family', Operators::IN_LIST, [E2OpenAttributesService::FAMILY_CODE]);

        $productsCursor = $pqb->execute();
        $product = $productsCursor->current();

        if ($product) {
            return $product;
        }

        // if not found, check latest products in the family
        $family = $this->familyRepository->findOneByIdentifier(E2OpenAttributesService::FAMILY_CODE);

        $products = $this->productRepository->findBy(
            [
                'family' => $family,
            ],
            ['id' => 'DESC'],
            10
        );

        foreach ($products as $product) {
            /** @var ProductInterface $product */
            $actualGTINvalue = $product->getValue('GTIN') ? $product->getValue('GTIN')->__toString() : null;
            if ($gtinValue === $actualGTINvalue) {
                return $product;
            }
        }

        return null;
    }

    private function createNewProductInstance(string $identifier): ProductInterface
    {
        return $this->productBuilder->createProduct(
            $identifier,
            E2OpenAttributesService::FAMILY_CODE
        );
    }
}
