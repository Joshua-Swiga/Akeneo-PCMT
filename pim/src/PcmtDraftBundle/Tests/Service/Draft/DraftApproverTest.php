<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\Service\Draft\DraftApprover;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductModelDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\TokenBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\UserBuilder;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PcmtSharedBundle\Tests\TestDataBuilder\ConstraintViolationBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\ConstraintViolationListBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DraftApproverTest extends TestCase
{
    /** @var DraftApprover */
    private $approver;

    /** @var EntityManagerInterface|MockObject */
    private $entityManagerMock;

    /** @var TokenStorageInterface|MockObject */
    private $tokenStorageMock;

    /** @var ValidatorInterface|MockObject */
    private $validatorMock;

    /** @var SaverInterface|MockObject */
    private $saverMock;

    /** @var GeneralObjectFromDraftCreator|MockObject */
    private $creatorMock;

    /** @var CategoryPermissionsCheckerInterface|MockObject */
    private $categoryPermissionsCheckerMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->saverMock = $this->createMock(SaverInterface::class);
        $this->creatorMock = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->categoryPermissionsCheckerMock = $this->createMock(CategoryPermissionsCheckerInterface::class);

        $this->approver = new DraftApprover(
            $this->entityManagerMock,
            $this->tokenStorageMock,
            $this->validatorMock,
            $this->saverMock,
            $this->creatorMock,
            $this->categoryPermissionsCheckerMock
        );
    }

    public function testApproveNewProductDraft(): void
    {
        $draftToApprove = (new NewProductDraftBuilder())->build();
        $correspondingProduct = (new ProductBuilder())->build();
        $user = (new UserBuilder())->build();
        $token = (new TokenBuilder())->withUser($user)->build();
        $violations = (new ConstraintViolationListBuilder())->build();

        $this->creatorMock
            ->method('getObjectToSave')
            ->willReturn($correspondingProduct);

        $this->tokenStorageMock
            ->method('getToken')
            ->willReturn($token);

        $this->validatorMock
            ->method('validate')
            ->willReturn($violations);

        $this->saverMock
            ->expects($this->once())
            ->method('save');
        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist');
        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        $this->approver->approve($draftToApprove);

        $this->assertEquals(AbstractDraft::STATUS_APPROVED, $draftToApprove->getStatus());
    }

    public function testApproveNewProductDraftWhenValidationFails(): void
    {
        $draftToApprove = (new NewProductDraftBuilder())->build();
        $correspondingObject = (new ProductBuilder())->build();
        $user = (new UserBuilder())->build();
        $token = (new TokenBuilder())->withUser($user)->build();
        $violations = (new ConstraintViolationListBuilder())
            ->withViolation((new ConstraintViolationBuilder())->build())
            ->build();

        $this->creatorMock
            ->method('getObjectToSave')
            ->willReturn($correspondingObject);

        $this->tokenStorageMock
            ->method('getToken')
            ->willReturn($token);

        $this->validatorMock
            ->method('validate')
            ->willReturn($violations);

        $this->expectException(DraftViolationException::class);

        $this->saverMock
            ->expects($this->never())
            ->method('save');
        $this->entityManagerMock
            ->expects($this->never())
            ->method('persist');
        $this->entityManagerMock
            ->expects($this->never())
            ->method('flush');

        $this->approver->approve($draftToApprove);
    }

    public function testApproveExistingProductDraft(): void
    {
        $draftToApprove = (new ExistingProductDraftBuilder())->build();
        $correspondingObject = (new ProductBuilder())->build();
        $user = (new UserBuilder())->build();
        $token = (new TokenBuilder())->withUser($user)->build();
        $violations = (new ConstraintViolationListBuilder())->build();

        $this->categoryPermissionsCheckerMock
            ->method('hasAccessToProduct')
            ->willReturn(true);

        $this->creatorMock
            ->method('getObjectToSave')
            ->willReturn($correspondingObject);

        $this->tokenStorageMock
            ->method('getToken')
            ->willReturn($token);

        $this->validatorMock
            ->method('validate')
            ->willReturn($violations);

        $this->saverMock
            ->expects($this->once())
            ->method('save');
        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist');
        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        $this->approver->approve($draftToApprove);

        $this->assertEquals(AbstractDraft::STATUS_APPROVED, $draftToApprove->getStatus());
    }

    public function testApproveExistingProductModelDraft(): void
    {
        $draftToApprove = (new ExistingProductModelDraftBuilder())->build();
        $correspondingObject = (new ProductModelBuilder())->build();
        $user = (new UserBuilder())->build();
        $token = (new TokenBuilder())->withUser($user)->build();
        $violations = (new ConstraintViolationListBuilder())->build();

        $this->categoryPermissionsCheckerMock
            ->method('hasAccessToProduct')
            ->willReturn(true);

        $this->creatorMock
            ->method('getObjectToSave')
            ->willReturn($correspondingObject);

        $this->tokenStorageMock
            ->method('getToken')
            ->willReturn($token);

        $this->validatorMock
            ->method('validate')
            ->willReturn($violations);

        $this->saverMock
            ->expects($this->once())
            ->method('save');
        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist');
        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        $this->approver->approve($draftToApprove);

        $this->assertEquals(AbstractDraft::STATUS_APPROVED, $draftToApprove->getStatus());
    }

    public function testApproveWhenThereIsNoCorrespondingObject(): void
    {
        $draftToApprove = (new ExistingProductDraftBuilder())->withProduct(null)->build();

        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('pcmt.entity.draft.error.no_corresponding_object');

        $this->saverMock
            ->expects($this->never())
            ->method('save');
        $this->entityManagerMock
            ->expects($this->never())
            ->method('persist');
        $this->entityManagerMock
            ->expects($this->never())
            ->method('flush');

        $this->approver->approve($draftToApprove);
    }

    public function testApproveWhenObjectToSaveIsNull(): void
    {
        $draftToApprove = (new ExistingProductDraftBuilder())->build();
        $user = (new UserBuilder())->build();
        $token = (new TokenBuilder())->withUser($user)->build();

        $this->creatorMock
            ->method('getObjectToSave')
            ->willReturn(null);

        $this->tokenStorageMock
            ->method('getToken')
            ->willReturn($token);

        $this->categoryPermissionsCheckerMock
            ->method('hasAccessToProduct')
            ->willReturn(false);

        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('pcmt.entity.draft.error.no_corresponding_object');

        $this->saverMock
            ->expects($this->never())
            ->method('save');
        $this->entityManagerMock
            ->expects($this->never())
            ->method('persist');
        $this->entityManagerMock
            ->expects($this->never())
            ->method('flush');

        $this->approver->approve($draftToApprove);
    }

    public function testApproveExistingProductDraftWhenUserHasNoAccessToTheProductsCategory(): void
    {
        $draftToApprove = (new ExistingProductDraftBuilder())->build();
        $correspondingObject = (new ProductBuilder())->build();
        $user = (new UserBuilder())->build();
        $token = (new TokenBuilder())->withUser($user)->build();
        $violations = (new ConstraintViolationListBuilder())->build();

        $this->categoryPermissionsCheckerMock
            ->method('hasAccessToProduct')
            ->willReturn(false);

        $this->creatorMock
            ->method('getObjectToSave')
            ->willReturn($correspondingObject);

        $this->tokenStorageMock
            ->method('getToken')
            ->willReturn($token);

        $this->validatorMock
            ->method('validate')
            ->willReturn($violations);

        $this->saverMock
            ->expects($this->never())
            ->method('save');
        $this->entityManagerMock
            ->expects($this->never())
            ->method('persist');
        $this->entityManagerMock
            ->expects($this->never())
            ->method('flush');

        $this->expectException(DraftViolationException::class);

        $this->approver->approve($draftToApprove);
    }

    public function testApproveNewObjectDraftThrowsException(): void
    {
        $draftToApprove = (new NewProductDraftBuilder())->build();

        $this->creatorMock
            ->expects($this->once())
            ->method('getObjectToSave')
            ->willReturn(null);

        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('pcmt.entity.draft.error.no_corresponding_object');

        $this->saverMock
            ->expects($this->never())
            ->method('save');
        $this->entityManagerMock
            ->expects($this->never())
            ->method('persist');
        $this->entityManagerMock
            ->expects($this->never())
            ->method('flush');

        $this->approver->approve($draftToApprove);
    }
}