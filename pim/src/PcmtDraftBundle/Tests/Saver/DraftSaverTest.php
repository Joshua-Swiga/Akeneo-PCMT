<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Saver;

use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Exception\DraftSavingFailedException;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\Exception\DraftWithNoChangesException;
use PcmtDraftBundle\Saver\DraftSaver;
use PcmtDraftBundle\Service\Draft\ChangesChecker;
use PcmtDraftBundle\Service\Draft\DraftExistenceChecker;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PcmtDraftBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductModelDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductModelDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\ConstraintViolationBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\ConstraintViolationListBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DraftSaverTest extends TestCase
{
    /** @var DraftSaver */
    private $draftSaver;

    /** @var EntityManagerInterface|MockObject */
    private $entityManagerMock;

    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcherMock;

    /** @var DraftExistenceChecker|MockObject */
    private $draftExistenceCheckerMock;

    /** @var ValidatorInterface|MockObject */
    private $productValidatorMock;

    /** @var ValidatorInterface|MockObject */
    private $productModelValidatorMock;

    /** @var GeneralObjectFromDraftCreator|MockObject */
    private $generalObjectFromDraftCreatorMock;

    /** @var ChangesChecker|MockObject */
    private $changesCheckerMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->draftExistenceCheckerMock = $this->createMock(DraftExistenceChecker::class);
        $this->productValidatorMock = $this->createMock(ValidatorInterface::class);
        $this->productModelValidatorMock = $this->createMock(ValidatorInterface::class);
        $this->generalObjectFromDraftCreatorMock = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->changesCheckerMock = $this->createMock(ChangesChecker::class);

        $this->draftSaver = new DraftSaver(
            $this->entityManagerMock,
            $this->eventDispatcherMock,
            $this->draftExistenceCheckerMock,
            $this->productValidatorMock,
            $this->productModelValidatorMock,
            $this->generalObjectFromDraftCreatorMock,
            $this->changesCheckerMock
        );
    }

    public function testSaveExistingProductDraft(): void
    {
        $draft = (new ExistingProductDraftBuilder())->withId(112)->build();

        $this->generalObjectFromDraftCreatorMock
            ->method('getObjectToSave')
            ->willReturn((new ProductBuilder())->build());

        $this->productValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn((new ConstraintViolationListBuilder())->build());

        $this->entityManagerMock->expects($this->once())->method('persist')->with($draft);
        $this->entityManagerMock->expects($this->once())->method('flush');
        $this->eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $this->draftSaver->save($draft);
    }

    public function testSaveExistingProductModelDraft(): void
    {
        $draft = (new ExistingProductModelDraftBuilder())->withId(11)->build();

        $this->generalObjectFromDraftCreatorMock
            ->method('getObjectToSave')
            ->willReturn((new ProductModelBuilder())->build());

        $this->productModelValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn((new ConstraintViolationListBuilder())->build());

        $this->entityManagerMock->expects($this->once())->method('persist')->with($draft);
        $this->entityManagerMock->expects($this->once())->method('flush');
        $this->eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $this->draftSaver->save($draft);
    }

    public function testSaveNewProductDraft(): void
    {
        $draft = (new NewProductDraftBuilder())->withId(2)->build();

        $this->generalObjectFromDraftCreatorMock
            ->method('getObjectToSave')
            ->willReturn((new ProductBuilder())->build());

        $this->productValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn((new ConstraintViolationListBuilder())->build());

        $this->entityManagerMock->expects($this->once())->method('persist')->with($draft);
        $this->entityManagerMock->expects($this->once())->method('flush');
        $this->eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $this->draftSaver->save($draft);
    }

    public function testSaveNewProductModelDraft(): void
    {
        $draft = (new NewProductModelDraftBuilder())->withId(2)->build();

        $this->generalObjectFromDraftCreatorMock
            ->method('getObjectToSave')
            ->willReturn((new ProductModelBuilder())->build());

        $this->productModelValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn((new ConstraintViolationListBuilder())->build());

        $this->entityManagerMock->expects($this->once())->method('persist')->with($draft);
        $this->entityManagerMock->expects($this->once())->method('flush');
        $this->eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $this->draftSaver->save($draft);
    }

    public function testSaveNewDraftForObjectThatAlreadyHasADraft(): void
    {
        $draft = (new ExistingProductDraftBuilder())->withId(0)->build();

        $this->generalObjectFromDraftCreatorMock
            ->method('getObjectToSave')
            ->willReturn((new ProductBuilder())->build());

        $this->draftExistenceCheckerMock->method('checkIfDraftForObjectAlreadyExists')->willReturn(
            true
        );

        $this->entityManagerMock->expects($this->never())->method('persist')->with($draft);
        $this->entityManagerMock->expects($this->never())->method('flush');
        $this->eventDispatcherMock->expects($this->never())->method('dispatch');

        $this->expectException(\InvalidArgumentException::class);

        $this->draftSaver->save($draft);
    }

    public function testSaveIncorrectObject(): void
    {
        $object = (new AttributeBuilder())->build();

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');
        $this->eventDispatcherMock->expects($this->never())->method('dispatch');

        $this->expectException(\InvalidArgumentException::class);

        $this->draftSaver->save($object);
    }

    public function testSaveWhenCreatorReturnNullInsteadOfObject(): void
    {
        $this->generalObjectFromDraftCreatorMock
            ->method('getObjectToSave')
            ->willReturn(null);

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');
        $this->eventDispatcherMock->expects($this->never())->method('dispatch');

        $this->expectException(DraftSavingFailedException::class);

        $this->draftSaver->save((new ExistingProductDraftBuilder())->withId(112)->build());
    }

    public function dataSaveWhenDraftHasStatusOtherThanNew(): array
    {
        return [
            'when_draft_is_already_approved' => [
                'status' => AbstractDraft::STATUS_APPROVED,
            ],
            'when_draft_is_already_rejected' => [
                'status' => AbstractDraft::STATUS_REJECTED,
            ],
        ];
    }

    /**
     * @dataProvider dataSaveWhenDraftHasStatusOtherThanNew
     */
    public function testSaveWhenDraftHasStatusOtherThanNew(int $draftStatus): void
    {
        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');
        $this->eventDispatcherMock->expects($this->never())->method('dispatch');

        $this->expectException(DraftSavingFailedException::class);

        $this->draftSaver->save(
            (new ExistingProductDraftBuilder())->withId(112)->withStatus($draftStatus)->build()
        );
    }

    public function testSaveWhenDraftDidNotPassValidation(): void
    {
        $draft = (new NewProductDraftBuilder())->build();

        $this->generalObjectFromDraftCreatorMock
            ->method('getObjectToSave')
            ->willReturn((new ProductBuilder())->build());

        $this->productValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(
                (new ConstraintViolationListBuilder())->withViolation(
                    (new ConstraintViolationBuilder())->build()
                )->build()
            );

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');
        $this->eventDispatcherMock->expects($this->never())->method('dispatch');

        $this->expectException(DraftViolationException::class);

        $this->draftSaver->save($draft);
    }

    public function testSaveWhenValidationIsTurnedOff(): void
    {
        $draft = (new NewProductDraftBuilder())->build();

        $this->generalObjectFromDraftCreatorMock
            ->method('getObjectToSave')
            ->willReturn((new ProductBuilder())->build());

        $this->productValidatorMock
            ->expects($this->never())
            ->method('validate');

        $this->entityManagerMock->expects($this->once())->method('persist');
        $this->entityManagerMock->expects($this->once())->method('flush');
        $this->eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $this->draftSaver->save($draft, [DraftSaver::OPTION_NO_VALIDATION => true]);
    }

    public function testSaveWhenNoChanges(): void
    {
        $draft = (new ExistingProductDraftBuilder())->withId(0)->build();

        $this->generalObjectFromDraftCreatorMock
            ->method('getObjectToSave')
            ->willReturn((new ProductBuilder())->build());

        $this->productValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn((new ConstraintViolationListBuilder())->build());

        $this->changesCheckerMock->expects($this->once())->method('checkIfChanges')->willReturn(false);

        $this->expectException(DraftWithNoChangesException::class);

        $options = [DraftSaver::OPTION_DONT_SAVE_IF_NO_CHANGES => true];
        $this->draftSaver->save($draft, $options);
    }
}