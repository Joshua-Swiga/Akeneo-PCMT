<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PcmtRulesBundle\Service\ImageVerificationService;
use PcmtSharedBundle\Tests\TestDataBuilder\FileInfoBuilder;
use PHPUnit\Framework\TestCase;

class ImageVerificationServiceTest extends TestCase
{
    /** @var string */
    private $testResourcesDirectory = 'src/PcmtRulesBundle/Tests/TestResources';

    public function dataVerifyIfSame(): array
    {
        $name = 'img.png';
        $file = new \SplFileInfo($this->testResourcesDirectory . '/' . $name);

        return [
            [(new FileInfoBuilder())->build(), $file, false],
            [(new FileInfoBuilder())->withOriginalFilename($name)->withSize(1)->build(), $file, false],
            [(new FileInfoBuilder())->withOriginalFilename($name)->withSize(7893)->build(), $file, true],
            [(new FileInfoBuilder())->withOriginalFilename('xxxx' . $name)->withSize(7893)->build(), $file, false],
        ];
    }

    /**
     * @dataProvider dataVerifyIfSame
     */
    public function testVerifyIfSame(FileInfo $file, \SplFileInfo $downloadedFile, bool $expectedResult): void
    {
        $service = $this->getService();
        $this->assertEquals($expectedResult, $service->verifyIfSame($file, $downloadedFile));
    }

    public function getService(): ImageVerificationService
    {
        return new ImageVerificationService();
    }
}