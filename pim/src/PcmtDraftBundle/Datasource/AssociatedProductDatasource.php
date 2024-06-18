<?php

declare(strict_types=1);

/**
 * New associated product datasource.
 * Overrides the original one so that we can show associated products for draft,
 * not for original product.
 *
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Repository\DraftRepository;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;

/**
 * Original class copyrights:
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssociatedProductDatasource extends OriginalAssociatedProductDatasource
{
    /** @var GeneralObjectFromDraftCreator */
    protected $creator;

    /** @var DraftRepository */
    protected $draftRepository;

    public function setCreator(GeneralObjectFromDraftCreator $creator): void
    {
        $this->creator = $creator;
    }

    public function setDraftRepository(DraftRepository $repository): void
    {
        $this->draftRepository = $repository;
    }

    /**
     * The original product from database is overridden by the product created from draft.
     * In that way, we can correctly get associations for draft product, not for the original one.
     *
     * {@inheritdoc}
     */
    public function getResults()
    {
        $sourceProduct = $this->getSourceProduct();
        $draft = $this->getDraft($sourceProduct);
        $sourceProduct = $this->creator->getObjectToSave($draft);

        if (!$sourceProduct) {
            return [
                'totalRecords' => 0,
                'data'         => [],
            ];
        }

        return $this->getResultsForProduct($sourceProduct);
    }

    private function getSourceProduct(): ProductInterface
    {
        $sourceProduct = $this->getConfiguration('current_product', false);

        if (!$sourceProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected($sourceProduct, ProductInterface::class);
        }

        return $sourceProduct;
    }

    private function getDraft(ProductInterface $sourceProduct): DraftInterface
    {
        $criteria = [
            'status'  => AbstractDraft::STATUS_NEW,
            'product' => $sourceProduct->getId(),
        ];

        $draft = $this->draftRepository->findOneBy($criteria);

        if (!$draft instanceof DraftInterface) {
            throw InvalidObjectException::objectExpected($draft, DraftInterface::class);
        }

        return $draft;
    }
}
