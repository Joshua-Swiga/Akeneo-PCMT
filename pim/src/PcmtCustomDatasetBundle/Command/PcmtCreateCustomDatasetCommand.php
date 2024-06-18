<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Command;

use PcmtCustomDatasetBundle\Helper\ReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command that we can use in terminal to import all data from xlsx files
 * That command if automatic run with installer_data command
 * xlsx files path: Resources/fixtures/pcmt_global/import_files/
 */
class PcmtCreateCustomDatasetCommand extends ContainerAwareCommand
{
    /**
     * run inside terminal in fpm docker: bin/console $defaultName
     */
    /** @var string */
    protected static $defaultName = 'pcmt:custom-dataset:create';

    /** @var string */
    protected $filesFolderPath;

    /** @var string */
    protected $tmpAttributeGroupsFile = '/tmp/tmp_2_attribute_groups.xlsx';

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $bundleDir = __DIR__ . '/../';
        $this->filesFolderPath = $bundleDir . 'Resources/fixtures/pcmt_global/import_files/2020-07-08/';
        $this->createAttributeGroupsWithoutAttributes($output);
        $importList = $this->getImportList();
        $bar = new ProgressBar($output, count($importList));
        $bar->setFormat('verbose');
        $bar->start();
        foreach ($importList as $import) {
            if (0 === strncasecmp($import['fileName'], '/tmp/', 5)) {
                $currentFilePath = $import['fileName'];
            } else {
                $currentFilePath = $this->filesFolderPath . $import['fileName'];
            }
            $totalPath = str_replace('/', '\/', $currentFilePath);
            $arguments = [
                'code'       => $import['code'],
                '--no-debug' => true,
                '--no-log'   => true,
                '-v'         => true,
                '--config'   => sprintf('{"filePath": "%s"}', $totalPath),
            ];
            $output->writeln("\nnow: " . $import['code'] . '...');
            $this->executeCommand($output, $arguments);
            $bar->advance();
        }
        $bar->finish();
        $output->writeln('');
        $output->writeln('All data loaded');
        $this->removeTmpFile();
        $output->writeln('Tmp data removed');
    }

    protected function createAttributeGroupsWithoutAttributes(OutputInterface $output): void
    {
        $columnToFilter = 'ColumnToFilter';
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($this->filesFolderPath . '2_attribute_groups.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumn++;
        for ($col = 'A'; $col !== $highestColumn; ++$col) {
            $value = $worksheet->getCell($col . '1')->getFormattedValue();
            if ('attributes' === $value) {
                $columnToFilter = $col;

                break;
            }
        }
        $filterSubset = new ReadFilter($columnToFilter);
        $output->writeln('--------------------------------');
        $output->writeln('Column to filter in 2_attribute_groups.xlsx: ' . $columnToFilter);
        $output->writeln('--------------------------------');
        $reader->setReadFilter($filterSubset);
        $spreadsheet = $reader->load($this->filesFolderPath . '2_attribute_groups.xlsx');
        $writer = new XlsxWriter($spreadsheet);
        $writer->save($this->tmpAttributeGroupsFile);
    }

    private function executeCommand(OutputInterface $output, array $arguments): int
    {
        try {
            $command = $this->getApplication()->find('akeneo:batch:job');
            $input = new ArrayInput($arguments);

            return $command->run($input, $output);
        } catch (\Throwable $exception) {
            $output->writeln($exception);
            die;
        }
    }

    private function removeTmpFile(): void
    {
        foreach ($this->getImportList() as $import) {
            if (0 === strncasecmp($import['fileName'], '/tmp/', 5)) {
                unlink($import['fileName']) or die("Couldn't delete file");
            }
        }
    }

    private function getImportList(): array
    {
        return [
            [
                'code'     => 'xlsx_attribute_group_import',
                'fileName' => $this->tmpAttributeGroupsFile,
            ],
            [
                'code'     => 'xlsx_category_import',
                'fileName' => '1_categories.xlsx',
            ],
            [
                'code'     => 'xlsx_attribute_import',
                'fileName' => '3_attributes.xlsx',
            ],
            [
                'code'     => 'xlsx_attribute_option_import',
                'fileName' => '4_attribute_options.xlsx',
            ],
            [
                'code'     => 'xlsx_family_import',
                'fileName' => '5_families.xlsx',
            ],
            [
                'code'     => 'xlsx_family_variant_import',
                'fileName' => '6_family_variants.xlsx',
            ],
            [
                'code'     => 'xlsx_product_model_first_import',
                'fileName' => '7_product_models.xlsx',
            ],
            [
                'code'     => 'pcmt_xlsx_product_first_import',
                'fileName' => '8_1_products_trade_items_rh.xlsx',
            ],
            [
                'code'     => 'pcmt_xlsx_product_first_import',
                'fileName' => '8_2_products_trade_items_gdsn_queue.xlsx',
            ],
            [
                'code'     => 'import_map_suppliers_first',
                'fileName' => '9_masterdata_entries_all.xlsx',
            ],
            [
                'code'     => 'pcmt_xlsx_datagrid_view_import',
                'fileName' => '11_datagrid_view.xlsx',
            ],
        ];
    }
}