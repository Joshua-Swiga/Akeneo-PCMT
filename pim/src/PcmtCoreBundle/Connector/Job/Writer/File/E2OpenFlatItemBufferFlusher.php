<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Writer\File;

use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;

class E2OpenFlatItemBufferFlusher extends FlatItemBufferFlusher
{
    /**
     * {@inheritdoc}
     */
    protected function writeIntoSingleFile(FlatItemBuffer $buffer, array $writerOptions, $filePath)
    {
        $writtenFiles = [];

        $headers = $this->sortHeaders($buffer->getHeaders());
        $hollowItem = array_fill_keys($headers, '');

        $writer = $this->getWriter($filePath, $writerOptions);
        if ($headers) {
            $headers[0] = '#' . $headers[0];
        }
        $writer->addRow($headers);

        foreach ($buffer as $incompleteItem) {
            $item = array_replace($hollowItem, $incompleteItem);
            $writer->addRow($item);

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }

        $writer->close();
        $writtenFiles[] = $filePath;

        return $writtenFiles;
    }

    /**
     * {@inheritdoc}
     */
    protected function writeIntoSeveralFiles(
        FlatItemBuffer $buffer,
        array $writerOptions,
        $maxLinesPerFile,
        $basePathname
    ) {
        $writtenFiles = [];
        $basePathPattern = $this->getNumberedPathname($basePathname);
        $writtenLinesCount = 0;
        $fileCount = 1;

        $headers = $this->sortHeaders($buffer->getHeaders());
        $hollowItem = array_fill_keys($headers, '');
        if ($headers) {
            $headers[0] = '#' . $headers[0];
        }
        foreach ($buffer as $count => $incompleteItem) {
            if (0 === $writtenLinesCount % $maxLinesPerFile) {
                $filePath = $this->resolveFilePath(
                    $buffer,
                    $maxLinesPerFile,
                    $basePathPattern,
                    $fileCount
                );
                $writtenLinesCount = 0;
                $writer = $this->getWriter($filePath, $writerOptions);
                $writer->addRow($headers);
            }

            $item = array_replace($hollowItem, $incompleteItem);
            $writer->addRow($item);
            $writtenLinesCount++;

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }

            if (0 === $writtenLinesCount % $maxLinesPerFile || $buffer->count() === $count + 1) {
                $writer->close();
                $writtenFiles[] = $filePath;
                $fileCount++;
            }
        }

        return $writtenFiles;
    }
}
