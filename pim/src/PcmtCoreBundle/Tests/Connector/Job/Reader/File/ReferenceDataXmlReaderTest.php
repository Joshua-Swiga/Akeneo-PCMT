<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Connector\Job\Reader;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PcmtCoreBundle\Connector\Job\Reader\File\ReferenceDataXmlReader;
use PcmtCoreBundle\Entity\ReferenceData\LanguageCode;
use PcmtCoreBundle\Util\Adapter\FileGetContentsWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;
use Sabre\Xml\Service;

class ReferenceDataXmlReaderTest extends TestCase
{
    /** @var string */
    private $testResourcesDirectory = 'src/PcmtCoreBundle/Tests/TestResources';

    /** @var Service|Mock */
    protected $xmlParserMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var JobParameters|MockObject */
    private $jobParametersMock;

    /** @var FileGetContentsWrapper|MockObject */
    private $fileGetContentsWrapper;

    /** @var ReferenceDataXmlReader */
    private $referenceDataXmlReader;

    protected function setUp(): void
    {
        $this->fileGetContentsWrapper = new FileGetContentsWrapper();
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);
        $this->referenceDataXmlReader = new ReferenceDataXmlReader($this->fileGetContentsWrapper);
        $this->referenceDataXmlReader->setStepExecution($this->stepExecutionMock);
        $this->xmlParserMock = $this->createMock(Service::class);
    }

    public function dataReadWrongFileFormat(): array
    {
        return [
            [$this->testResourcesDirectory . '/file01.yml'],
            [$this->testResourcesDirectory . '/file.docx'],
            [$this->testResourcesDirectory . '/file.cpp'],
        ];
    }

    /**
     * @dataProvider dataReadWrongFileFormat
     */
    public function testReadWrongFileFormat(string $filePath): void
    {
        $this->stepExecutionMock->expects($this->exactly(2))
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->at(0))
            ->method('get')
            ->with('filePath')
            ->willReturn($filePath);

        $this->jobParametersMock->expects($this->at(1))
            ->method('get')
            ->with('xmlMapping')
            ->willReturn(null);

        $this->expectException(\Throwable::class);
        $this->referenceDataXmlReader->read();
    }

    public function dataRead(): array
    {
        return [
            [
                $this->testResourcesDirectory . '/RefDataExample.xml',
                [
                    [
                        'class'      => LanguageCode::class,
                        'version'    => '1',
                        'code'       => 'yo',
                        'name'       => 'Yoruba',
                        'definition' => null,
                    ],
                    [
                        'class'      => LanguageCode::class,
                        'version'    => '1',
                        'code'       => 'za',
                        'name'       => 'Chuang',
                        'definition' => null,
                    ],
                    [
                        'class'      => LanguageCode::class,
                        'version'    => '1',
                        'code'       => 'zh',
                        'name'       => 'Chinese',
                        'definition' => null,
                    ],
                    [
                        'class'      => LanguageCode::class,
                        'version'    => '1',
                        'code'       => 'zu',
                        'name'       => 'Zulu',
                        'definition' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataRead
     *
     * @throws \Throwable
     */
    public function testRead(string $filePath, array $result): void
    {
        $this->stepExecutionMock->expects($this->exactly(1))
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->referenceDataXmlReader->setFilePath($filePath);

        $this->jobParametersMock->expects($this->at(0))
            ->method('get')
            ->willReturn(null);

        $this->referenceDataXmlReader->flush();
        $this->referenceDataXmlReader->read();

        $this->assertSame($result, $this->referenceDataXmlReader->getProcessed());
    }

    public function dataReadFail(): array
    {
        return [
            [$this->testResourcesDirectory . '/RefDataExampleFail.xml'],
            [$this->testResourcesDirectory . '/RefDataExampleEmpty.xml'],
        ];
    }

    /**
     * @dataProvider dataReadFail
     *
     * @throws \Throwable
     */
    public function testReadFail(string $filePath): void
    {
        $this->expectException(\Throwable::class);

        $this->stepExecutionMock->expects($this->exactly(1))
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->referenceDataXmlReader->setFilePath($filePath);

        $this->jobParametersMock->expects($this->at(0))
            ->method('get')
            ->willReturn(null);

        $this->referenceDataXmlReader->flush();
        $this->referenceDataXmlReader->read();
    }

    public function testFlush(): void
    {
        $this->referenceDataXmlReader->flush();

        $this->assertSame([], $this->referenceDataXmlReader->getProcessed());
    }
}