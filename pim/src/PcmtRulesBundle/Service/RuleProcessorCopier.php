<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\ProductAttributeFilter;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\ProductModelAttributeFilter;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyCopierInterface;
use LogicException;
use PcmtRulesBundle\Event\ProductChangedEvent;
use PcmtRulesBundle\Event\ProductModelChangedEvent;
use PcmtRulesBundle\Tests\TestDataBuilder\ValueBuilder;
use PcmtRulesBundle\Value\AttributeMapping;
use PcmtRulesBundle\Value\AttributeMappingCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use TypeError;

class RuleProcessorCopier
{
    /** @var PropertyCopierInterface */
    private $propertyCopier;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var ProductAttributeFilter */
    private $productAttributeFilter;

    /** @var ProductModelAttributeFilter */
    private $productModelAttributeFilter;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var LoggerInterface */
    private $logger;

    public const TABLE_NULL_WARNING = <<<'EOD'
        Suspected table attribute extension with null value problem detected, 
        filling in more appropriate value
EOD;

    public function __construct(
        PropertyCopierInterface $propertyCopier,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        ProductAttributeFilter $productAttributeFilter,
        ProductModelAttributeFilter $productModelAttributeFilter,
        NormalizerInterface $normalizer,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->propertyCopier = $propertyCopier;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->productAttributeFilter = $productAttributeFilter;
        $this->productModelAttributeFilter = $productModelAttributeFilter;
        $this->normalizer = $normalizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    public function copy(
        EntityWithValuesInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct,
        AttributeMappingCollection $attributeMappingCollection
    ): bool {
        $productData = $this->normalizer->normalize($destinationProduct, 'standard');
        $attributeCodes = array_map(function (AttributeMapping $attributeMapping) {
            return $attributeMapping->getDestinationAttribute()->getCode();
        }, $attributeMappingCollection->toArray());

        $productData['values'] = [];
        foreach ($attributeCodes as $code) {
            $productData['values'][$code] = 'value';
        }

        if ($destinationProduct instanceof ProductInterface) {
            $productData = $this->productAttributeFilter->filter($productData);
        } else {
            $productData = $this->productModelAttributeFilter->filter($productData);
        }

        if (0 === count($productData['values'])) {
            return false;
        }
        foreach ($attributeMappingCollection as $mapping) {
            if (isset($productData['values'][$mapping->getDestinationAttribute()->getCode()])) {
                $this->copyOneAttribute($sourceProduct, $destinationProduct, $mapping->getSourceAttribute(), $mapping->getDestinationAttribute());
            }
        }

        return true;
    }

    private function copyOneAttribute(
        EntityWithValuesInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct,
        AttributeInterface $sourceAttribute,
        AttributeInterface $destinationAttribute
    ): void {
        $scopes = $sourceAttribute->isScopable() ? $this->channelRepository->getChannelCodes() : null;
        $locales = $sourceAttribute->isLocalizable() ? $this->localeRepository->getActivatedLocaleCodes() : null;

        $scopes = $scopes ?? [null];
        $locales = $locales ?? [null];

        foreach ($locales as $localeCode) {
            foreach ($scopes as $scopeCode) {
                $options = [
                    'from_locale' => $localeCode,
                    'to_locale'   => $localeCode,
                    'from_scope'  => $scopeCode,
                    'to_scope'    => $scopeCode,
                ];

                try {
                    $this->copyOneAttributeHelper(
                        $sourceProduct,
                        $destinationProduct,
                        $sourceAttribute,
                        $destinationAttribute,
                        $options
                    );
                } catch (LogicException $le) {
                    $this->logger->warning(sprintf('Skipping copying attribute: %s, because %s', $sourceAttribute->getCode(), $le->getmessage()));
                }
            }
        }
    }

    private function copyOneAttributeHelper(
        EntityWithValuesInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct,
        AttributeInterface $sourceAttribute,
        AttributeInterface $destinationAttribute,
        array $options
    ): void {
        $previousValue = $destinationProduct->getValue($destinationAttribute->getCode(), $options['to_locale'], $options['to_scope']);
        $newValue = $sourceProduct->getValue($sourceAttribute->getCode(), $options['from_locale'], $options['from_scope']);

        // check for type errors that seem to arise from the TableAttribute
        // extension when the value is null, and if that condition gives way
        // to this exception, then give that attribute an appropriate
        // empty value
        try {
            $previousValue && !$previousValue->isEqual($newValue);
        } catch (TypeError $te) {
            $newValue = (new ValueBuilder())
                ->withAttributeCode($destinationAttribute->getCode())
                ->build();
            $sourceProduct->setValues(new WriteValueCollection([$newValue]));
            $warningMsg = sprintf(self::TABLE_NULL_WARNING . ': %s', print_r($newValue, true));
            $this->logger->warning($warningMsg);
        }

        // want to escape if both values are null, or both values are the same
        if (false === isset($previousValue) && false === isset($newValue)) {
            return;
        }
        if (isset($previousValue, $newValue) && $previousValue->isEqual($newValue)) {
            return;
        }

        // copy the attribute
        $this->propertyCopier->copyData(
            $sourceProduct,
            $destinationProduct,
            $sourceAttribute->getCode(),
            $destinationAttribute->getCode(),
            $options
        );

        // issue a change event for the changed value
        if ($destinationProduct instanceof ProductModelInterface) {
            $event = new ProductModelChangedEvent(
                $destinationProduct,
                $destinationAttribute,
                $options['from_locale'],
                $options['from_scope'],
                $previousValue,
                $newValue
            );
            $this->eventDispatcher->dispatch(ProductModelChangedEvent::NAME, $event);
        } else {
            $event = new ProductChangedEvent(
                $destinationProduct,
                $destinationAttribute,
                $options['from_locale'],
                $options['from_scope'],
                $previousValue,
                $newValue
            );
            $this->eventDispatcher->dispatch(ProductChangedEvent::NAME, $event);
        }
    }
}
