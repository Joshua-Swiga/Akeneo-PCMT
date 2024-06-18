<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Normalizer;

use PcmtDraftBundle\Exception\DraftApproveFailedException;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\Normalizer\DraftViolationNormalizer;
use PcmtSharedBundle\Tests\TestDataBuilder\ConstraintViolationBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\ConstraintViolationListBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DraftViolationNormalizerTest extends TestCase
{
    /** @var DraftViolationNormalizer */
    private $draftViolationNormalizer;

    /** @var NormalizerInterface|MockObject */
    private $constraintViolationNormalizerMock;

    protected function setUp(): void
    {
        $this->constraintViolationNormalizerMock = $this->createMock(NormalizerInterface::class);

        $this->draftViolationNormalizer = new DraftViolationNormalizer(
            $this->constraintViolationNormalizerMock
        );
    }

    public function testNormalize(): void
    {
        $exceptionMock = $this->createMock(DraftViolationException::class);

        $exceptionMock
            ->method('getViolations')
            ->willReturn(
                (new ConstraintViolationListBuilder())
                    ->withViolation(
                        (new ConstraintViolationBuilder())
                            ->build()
                    )->withViolation(
                        (new ConstraintViolationBuilder())
                            ->build()
                    )->build()
            );

        $this->constraintViolationNormalizerMock
            ->expects($this->exactly(2))
            ->method('normalize');

        $this->draftViolationNormalizer->normalize($exceptionMock);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            'when_support'          => [
                'object' => $this->createMock(DraftViolationException::class),
                'result' => true,
            ],
            'when_does_not_support' => [
                'object' => $this->createMock(DraftApproveFailedException::class),
                'result' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(object $object, bool $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->draftViolationNormalizer->supportsNormalization($object));
    }
}