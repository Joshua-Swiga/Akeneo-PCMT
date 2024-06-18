<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Builder;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtDraftBundle\Service\Builder\ResponseBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseBuilderTest extends TestCase
{
    public function buildPaginatedResponseDataProvider(): array
    {
        $input = [];
        $output = [];
        $numberOfElements = (2 * ResponseBuilder::PER_PAGE) + 1;
        for ($i = 0; $i < $numberOfElements; $i++) {
            $input[$i] = 'value' . $i;
            $output[$i] = 'value' . $i;
        }
        $output['params']['total'] = $numberOfElements;
        $output['params']['firstPage'] = 1;
        $output['params']['currentPage'] = 1;
        $output['params']['lastPage'] = 3;

        return [
            [
                $input,
                $output,
            ],
        ];
    }

    public function testBuildEmptyResponse(): void
    {
        $serializer = $this->createMock(NormalizerInterface::class);
        $responseBuilder = new ResponseBuilder($serializer);

        $response = $responseBuilder->build();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(new \stdClass()), $response->getContent());
    }

    public function testBuildResponseForDraftWithNormalizer(): void
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $responseBuilder = new ResponseBuilder($normalizer);
        $draft = $this->createMock(AttributeInterface::class);
        $normalizerResult = [
            'id' => 1234,
        ];

        $normalizer
            ->method('supportsNormalization')
            ->willReturn(true);

        $normalizer
            ->method('normalize')
            ->with([$draft])
            ->willReturn($normalizerResult);

        $response = $responseBuilder
            ->setData([$draft])
            ->build();

        $this->assertSame(json_encode($normalizerResult), $response->getContent());
    }

    public function testBuildResponseForDraftWithNormalizerAndIncludeProductInContext(): void
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $responseBuilder = new ResponseBuilder($normalizer);
        $draft = $this->createMock(AttributeInterface::class);
        $normalizerResult = [
            'id'      => 1234,
            'product' => [
                'id' => 4321,
            ],
        ];

        $normalizer
            ->method('supportsNormalization')
            ->willReturn(true);

        $normalizer
            ->method('normalize')
            ->with([$draft], null, ['include_product' => true])
            ->willReturn($normalizerResult);

        $response = $responseBuilder
            ->setData([$draft])
            ->setContext(['include_product' => true])
            ->build();

        $this->assertSame(json_encode($normalizerResult), $response->getContent());
    }

    public function testBuildResponseForDraftsListWithNormalizer(): void
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $responseBuilder = new ResponseBuilder($normalizer);
        $draft1 = $this->createMock(AttributeInterface::class);
        $draft2 = $this->createMock(AttributeInterface::class);
        $normalizerResult = [
            ['id' => 1234],
            ['id' => 1235],
        ];

        $normalizer
            ->method('supportsNormalization')
            ->willReturn(true);

        $normalizer
            ->method('normalize')
            ->with(
                [
                    $draft1,
                    $draft2,
                ]
            )
            ->willReturn($normalizerResult);

        $response = $responseBuilder
            ->setData(
                [
                    $draft1,
                    $draft2,
                ]
            )
            ->build();

        $this->assertSame(json_encode($normalizerResult), $response->getContent());
    }

    /**
     * @dataProvider buildPaginatedResponseDataProvider
     */
    public function testBuildPaginatedResponse(array $input, array $result): void
    {
        $serializer = $this->createMock(NormalizerInterface::class);
        $responseBuilder = new ResponseBuilder($serializer);
        $total = count($input);
        $response = $responseBuilder->buildPaginatedResponse($input, $total, ResponseBuilder::FIRST_PAGE);

        $this->assertJsonStringEqualsJsonString(json_encode(new JsonResponse($result)), json_encode($response));
    }

    /**
     * @dataProvider dataGetLastPage
     */
    public function testGetLastPage(int $total, int $expectedLastPage): void
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $responseBuilder = new ResponseBuilder($normalizer);
        $lastPage = $responseBuilder->getLastPage($total);
        $this->assertSame($expectedLastPage, $lastPage);
    }

    public function dataGetLastPage(): array
    {
        $page = 5;
        $numberOfElements = $page * ResponseBuilder::PER_PAGE;

        return [
            '0 elements'    => [0, 1],
            '1 element'     => [1, 1],
            'full pages'    => [$numberOfElements, $page],
            'more elements' => [$numberOfElements + 2, $page + 1],
        ];
    }
}