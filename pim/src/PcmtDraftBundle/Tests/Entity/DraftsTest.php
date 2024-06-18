<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Entity;

use Carbon\Carbon;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Exception\DraftApproveFailedException;
use PcmtDraftBundle\Exception\DraftRejectFailedException;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductModelDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductModelDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\UserBuilder;
use PHPUnit\Framework\TestCase;

class DraftsTest extends TestCase
{
    public function dataDrafts(): array
    {
        return [
            'existing_product_draft'       => [(new ExistingProductDraftBuilder())->build()],
            'existing_product_model_draft' => [(new ExistingProductModelDraftBuilder())->build()],
            'new_product_draft'            => [(new NewProductDraftBuilder())->build()],
            'new_product_model_draft'      => [(new NewProductModelDraftBuilder())->build()],
        ];
    }

    /**
     * @dataProvider dataDrafts
     */
    public function testApproveWhenDraftHasStatusNew(DraftInterface $draft): void
    {
        $currentDate = Carbon::create(2020, 3, 18, 11);
        Carbon::setTestNow($currentDate);

        $approver = (new UserBuilder())->withId(100)->build();

        $draft->approve($approver);

        $this->assertEquals(
            AbstractDraft::STATUS_APPROVED,
            $draft->getStatus()
        );
        $this->assertEquals($currentDate, $draft->getApproved());
        $this->assertEquals(100, $draft->getApprovedBy()->getId());
    }

    public function dataDraftsWithStatusNotNew(): array
    {
        return [
            'already_approved_existing_product_draft'       => [
                (new ExistingProductDraftBuilder())->withStatus(
                    AbstractDraft::STATUS_APPROVED
                )->build(),
            ],
            'rejected_existing_product_draft'               => [
                (new ExistingProductDraftBuilder())->withStatus(
                    AbstractDraft::STATUS_REJECTED
                )->build(),
            ],
            'already_approved_existing_product_model_draft' => [
                (new ExistingProductModelDraftBuilder())->withStatus(
                    AbstractDraft::STATUS_APPROVED
                )->build(),
            ],
            'rejected_existing_product_model_draft'         => [
                (new ExistingProductModelDraftBuilder())->withStatus(
                    AbstractDraft::STATUS_REJECTED
                )->build(),
            ],
            'already_approved_new_product_draft'            => [
                (new NewProductDraftBuilder())->withStatus(AbstractDraft::STATUS_APPROVED)->build(),
            ],
            'rejected_new_product_draft'                    => [
                (new NewProductDraftBuilder())->withStatus(AbstractDraft::STATUS_REJECTED)->build(),
            ],
            'already_approved_new_product_model_draft'      => [
                (new NewProductModelDraftBuilder())->withStatus(
                    AbstractDraft::STATUS_APPROVED
                )->build(),
            ],
            'rejected_new_product_model_draft'              => [
                (new NewProductModelDraftBuilder())->withStatus(
                    AbstractDraft::STATUS_REJECTED
                )->build(),
            ],
        ];
    }

    /**
     * @dataProvider dataDraftsWithStatusNotNew
     */
    public function testApproveWhenDraftHasNoStatusNew(DraftInterface $draft): void
    {
        $approver = (new UserBuilder())->build();
        $this->expectException(DraftApproveFailedException::class);

        $draft->approve($approver);
    }

    /**
     * @dataProvider dataDrafts
     */
    public function testRejectWhenDraftHasStatusNew(DraftInterface $draft): void
    {
        $draft->reject();

        $this->assertEquals(
            AbstractDraft::STATUS_REJECTED,
            $draft->getStatus()
        );
    }

    /**
     * @dataProvider dataDraftsWithStatusNotNew
     */
    public function testRejectWhenDraftHasNoStatusNew(DraftInterface $draft): void
    {
        $this->expectException(DraftRejectFailedException::class);

        $draft->reject();
    }
}