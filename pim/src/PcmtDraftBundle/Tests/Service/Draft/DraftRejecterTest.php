<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\AbstractProductDraft;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\Service\Draft\DraftRejecter;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DraftRejecterTest extends TestCase
{
    /** @var EntityManagerInterface */
    private $entityManagerMock;

    /** @var TokenStorageInterface */
    private $tokenStorageMock;

    /** @var MockObject|CategoryPermissionsCheckerInterface */
    private $categoryPermissionsCheckerMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $user = $this->createMock(UserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $this->tokenStorageMock->method('getToken')->willReturn($token);

        $this->categoryPermissionsCheckerMock = $this->createMock(CategoryPermissionsCheckerInterface::class);

        parent::setUp();
    }

    private function getDraftRejecterInstance(): DraftRejecter
    {
        return new DraftRejecter(
            $this->entityManagerMock,
            $this->tokenStorageMock,
            $this->categoryPermissionsCheckerMock
        );
    }

    /**
     * @dataProvider dataReject
     */
    public function testReject(AbstractProductDraft $draft): void
    {
        $objectToSave = (new ProductBuilder())->build();

        $draft
            ->method('getProduct')
            ->willReturn($objectToSave);

        $this->entityManagerMock->expects($this->once())->method('persist');
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->categoryPermissionsCheckerMock->method('hasAccessToProduct')->willReturn(true);

        $service = $this->getDraftRejecterInstance();

        /** @var MockObject $draft */
        $draft->expects($this->once())->method('setStatus')->with();

        $service->reject($draft);
    }

    /**
     * @dataProvider dataReject
     */
    public function testRejectThrowsExceptionWhenNoAccess(AbstractProductDraft $draft): void
    {
        $objectToSave = (new ProductBuilder())->build();

        $draft
            ->method('getProduct')
            ->willReturn($objectToSave);

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');

        $this->categoryPermissionsCheckerMock->method('hasAccessToProduct')->willReturn(false);

        $service = $this->getDraftRejecterInstance();

        $this->expectException(DraftViolationException::class);

        /** @var MockObject $draft */
        $draft->expects($this->never())->method('setStatus')->with();

        $service->reject($draft);
    }

    public function dataReject(): array
    {
        $draft = $this->createMock(AbstractProductDraft::class);

        return [
            [$draft],
        ];
    }

    /**
     * @dataProvider dataRejectNoObject
     */
    public function testRejectNoObject(AbstractProductDraft $draft): void
    {
        $draft
            ->method('getProduct')
            ->willReturn(null);

        $this->entityManagerMock->expects($this->once())->method('persist');
        $this->entityManagerMock->expects($this->once())->method('flush');

        $service = $this->getDraftRejecterInstance();

        /** @var MockObject $draft */
        $draft->expects($this->once())->method('setStatus')->with();

        $service->reject($draft);
    }

    public function dataRejectNoObject(): array
    {
        $draft = $this->createMock(AbstractProductDraft::class);

        return [
            [$draft],
        ];
    }
}