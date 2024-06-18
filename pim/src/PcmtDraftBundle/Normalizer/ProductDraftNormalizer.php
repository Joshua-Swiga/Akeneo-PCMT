<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\ProductDraftInterface;
use PcmtDraftBundle\Service\AttributeChange\AttributeChangeService;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ProductDraftNormalizer implements NormalizerInterface
{
    /** @var GeneralDraftNormalizer */
    private $generalDraftNormalizer;

    /** @var GeneralObjectFromDraftCreator */
    private $productFromDraftCreator;

    /** @var AttributeChangeService */
    private $attributeChangeService;

    /** @var NormalizerInterface */
    private $productNormalizer;

    /** @var AttributeChangeNormalizer */
    private $attributeChangeNormalizer;

    /** @var FormProviderInterface */
    private $formProvider;

    /** @var NormalizerInterface */
    private $valuesNormalizer;

    /** @var PermissionsHelper */
    private $permissionsHelper;

    /** @var UserContext */
    private $userContext;

    public function __construct(
        AttributeChangeService $attributeChangeService,
        AttributeChangeNormalizer $attributeChangeNormalizer,
        FormProviderInterface $formProvider,
        NormalizerInterface $productNormalizer,
        GeneralDraftNormalizer $generalDraftNormalizer,
        GeneralObjectFromDraftCreator $productFromDraftCreator,
        NormalizerInterface $valuesNormalizer,
        PermissionsHelper $permissionsHelper,
        UserContext $userContext
    ) {
        $this->attributeChangeService = $attributeChangeService;
        $this->attributeChangeNormalizer = $attributeChangeNormalizer;
        $this->formProvider = $formProvider;
        $this->productNormalizer = $productNormalizer;
        $this->generalDraftNormalizer = $generalDraftNormalizer;
        $this->productFromDraftCreator = $productFromDraftCreator;
        $this->valuesNormalizer = $valuesNormalizer;
        $this->permissionsHelper = $permissionsHelper;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($draft, $format = null, array $context = []): array
    {
        /** @var ProductDraftInterface $draft */
        $data = $this->generalDraftNormalizer->normalize($draft, $format, $context);

        $data['categoryPermissions'] = $this->permissionsHelper->normalizeCategoryPermissions($draft->getProduct());

        $newProduct = $this->productFromDraftCreator->getObjectToCompare($draft);
        if (!$newProduct) {
            // that's a special case when a original product has been removed after creating a draft.
            return $data;
        }
        $data['label'] = $this->getLabel($draft, $newProduct);

        $changes = $this->attributeChangeService->get($newProduct, $draft->getProduct());
        $serializer = new Serializer([$this->attributeChangeNormalizer]);
        $data['changes'] = $serializer->normalize($changes);

        $data['values'] = [
            'draftId'    => $draft->getId(),
            'identifier' => $newProduct->getIdentifier(),
            'family'     => $newProduct->getFamily() ? $newProduct->getFamily()->getCode() : '-',
            'parentId'   => $newProduct->getParent() ? $newProduct->getParent()->getId() : null,
            'parent'     => $newProduct->getParent() ? $newProduct->getParent()->getCode() : null,
        ];

        if ($context['include_product'] ?? false) {
            $data['product'] = $this->productNormalizer->normalize($newProduct, 'internal_api');
            $data['product']['meta']['form'] = $this->formProvider->getForm($draft);
        } else {
            $values = [];

            $copiedProduct = clone $newProduct;
            $copiedProduct->setParent(null);
            foreach ($copiedProduct->getValues() as $value) {
                /** @var ValueInterface $value */
                $values[$value->getAttributeCode()][] = $this->valuesNormalizer->normalize($value, 'standard');
            }

            $data['values']['values'] = $values;
        }

        return $data;
    }

    private function getLabel(ProductDraftInterface $draft, ProductInterface $newProduct): string
    {
        if ($draft instanceof ExistingProductDraft) {
            return $draft->getProduct()->getLabel($this->userContext->getUiLocaleCode()) ?? '-';
        }

        return $newProduct->getLabel($this->userContext->getUiLocaleCode()) ?? '-';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductDraftInterface;
    }
}