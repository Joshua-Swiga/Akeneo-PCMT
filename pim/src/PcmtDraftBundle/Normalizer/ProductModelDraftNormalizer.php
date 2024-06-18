<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;
use PcmtDraftBundle\Service\AttributeChange\AttributeChangeService;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ProductModelDraftNormalizer implements NormalizerInterface
{
    /** @var GeneralDraftNormalizer */
    private $generalDraftNormalizer;

    /** @var GeneralObjectFromDraftCreator */
    private $productModelFromDraftCreator;

    /** @var AttributeChangeService */
    private $attributeChangeService;

    /** @var NormalizerInterface */
    private $productModelNormalizer;

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
        NormalizerInterface $productModelNormalizer,
        GeneralDraftNormalizer $generalDraftNormalizer,
        GeneralObjectFromDraftCreator $productModelFromDraftCreator,
        NormalizerInterface $valuesNormalizer,
        PermissionsHelper $permissionsHelper,
        UserContext $userContext
    ) {
        $this->attributeChangeService = $attributeChangeService;
        $this->attributeChangeNormalizer = $attributeChangeNormalizer;
        $this->formProvider = $formProvider;
        $this->productModelNormalizer = $productModelNormalizer;
        $this->generalDraftNormalizer = $generalDraftNormalizer;
        $this->productModelFromDraftCreator = $productModelFromDraftCreator;
        $this->valuesNormalizer = $valuesNormalizer;
        $this->permissionsHelper = $permissionsHelper;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($draft, $format = null, array $context = []): array
    {
        /** @var ProductModelDraftInterface $draft */
        $data = $this->generalDraftNormalizer->normalize($draft, $format, $context);

        $newProductModel = $this->productModelFromDraftCreator->getObjectToCompare($draft);

        $data['categoryPermissions'] = $this->permissionsHelper->normalizeCategoryPermissions($draft->getProductModel());

        if (!$newProductModel) {
            // that's a special case when a original product has been removed after creating a draft.
            return $data;
        }
        $data['label'] = $this->getLabel($draft, $newProductModel);

        $changes = $this->attributeChangeService->get($newProductModel, $draft->getProductModel());
        $serializer = new Serializer([$this->attributeChangeNormalizer]);
        $data['changes'] = $serializer->normalize($changes);

        $data['values'] = [
            'draftId'        => $draft->getId(),
            'code'           => $newProductModel->getCode(),
            'family'         => $newProductModel->getFamily()->getCode(),
            'family_variant' => $newProductModel->getFamilyVariant()->getCode(),
            'parentId'       => $newProductModel->getParent() ? $newProductModel->getParent()->getId() : null,
            'parent'         => $newProductModel->getParent() ? $newProductModel->getParent()->getCode() : null,
            'identifier'     => $newProductModel->getCode(),
        ];

        if ($context['include_product'] ?? false) {
            $data['product'] = $this->productModelNormalizer->normalize($newProductModel, 'internal_api');
            $data['product']['meta']['form'] = $this->formProvider->getForm($draft);
        } else {
            $values = [];

            $copiedProduct = clone $newProductModel;
            $copiedProduct->setParent(null);
            foreach ($copiedProduct->getValues() as $value) {
                /** @var ValueInterface $value */
                $values[$value->getAttributeCode()][] = $this->valuesNormalizer->normalize($value, 'standard');
            }

            $data['values']['values'] = $values;
        }

        return $data;
    }

    private function getLabel(ProductModelDraftInterface $draft, ProductModelInterface $newProductModel): string
    {
        if ($draft instanceof ExistingProductModelDraft) {
            return $draft->getProductModel()->getLabel($this->userContext->getUiLocaleCode());
        }

        return $newProductModel->getLabel($this->userContext->getUiLocaleCode()) ?? '-';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductModelDraftInterface;
    }
}