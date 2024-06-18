<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Listener;

use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\PimDataGridBundle\Datasource\RepositoryDatasource;
use PcmtRulesBundle\Listener\AddUsernameToGridListener;
use PcmtRulesBundle\Tests\TestDataBuilder\QueryBuilderBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AddUsernameToGridListenerTest extends TestCase
{
    /** @var TokenStorageInterface|MockObject */
    private $tokenStorageMock;

    /** @var BuildAfter|MockObject */
    private $eventMock;

    /** @var UserInterface|MockObject */
    private $userMock;

    /** @var QueryBuilder */
    private $queryBuilder;

    protected function setUp(): void
    {
        $expression = new Expr();
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->method('getExpressionBuilder')->willReturn($expression);
        $this->queryBuilder = (new QueryBuilderBuilder($entityManagerMock))->build();
        $datasourceMock = $this->createMock(RepositoryDatasource::class);
        $datasourceMock->method('getParameters')->willReturn([]);
        $datasourceMock->method('getQueryBuilder')->willReturn($this->queryBuilder);
        $datagridMock = $this->createMock(DatagridInterface::class);
        $datagridMock->method('getDatasource')->willReturn($datasourceMock);

        $this->userMock = $this->createMock(UserInterface::class);
        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->method('getUser')->willReturn($this->userMock);
        $this->tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $this->tokenStorageMock->method('getToken')->willReturn($tokenMock);
        $this->eventMock = $this->createMock(BuildAfter::class);
        $this->eventMock->method('getDatagrid')->willReturn($datagridMock);
    }

    /**
     * @dataProvider dataOnBuildAfter
     */
    public function testOnBuildAfter(?Role $role, string $expectedEnding): void
    {
        $this->userMock->method('getRole')->willReturn($role);

        $listener = $this->getListenerInstance();
        $listener->onBuildAfter($this->eventMock);

        $this->assertStringEndsWith($expectedEnding, $this->queryBuilder->getDQL());
    }

    public function dataOnBuildAfter(): array
    {
        return [
            [null, 'WHERE e.user = :user'],
            [new Role('ROLE_ADMIN'), 'WHERE e.user = :user OR e.user IS NULL'],
        ];
    }

    private function getListenerInstance(): AddUsernameToGridListener
    {
        return new AddUsernameToGridListener($this->tokenStorageMock);
    }
}