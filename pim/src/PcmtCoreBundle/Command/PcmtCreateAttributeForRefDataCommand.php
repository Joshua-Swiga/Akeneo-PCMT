<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Command;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use PcmtCoreBundle\Updater\AttributeUpdater;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcmtCreateAttributeForRefDataCommand extends ContainerAwareCommand
{
    /**
     * run inside terminal in fpm docker: bin/console $defaultName
     */
    /** @var string */
    protected static $defaultName = 'pcmt:generate-ref-data-attr';

    public function configure(): void
    {
        parent::configure();
        $this->addArgument('ref-data-name', InputArgument::REQUIRED, 'The name of the reference data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln([
            'Reference Data Attribute Creator',
            '============',
        ]);
        // retrieve the argument value using getArgument()
        $refDataName = $input->getArgument('ref-data-name');
        $output->writeln('Reference Data: '.$refDataName);
        // create as Attribute
        $this->createAttributeForReferenceData($refDataName);
        $output->writeln('done');
    }

    private function createAttributeForReferenceData(string $refDataName): void
    {
        /** @var AttributeFactory */
        $attributeFactory = $this->getContainer()->get('pim_catalog.factory.attribute');
        /** @var AttributeUpdater $attributeUpdater */
        $attributeUpdater = $this->getContainer()->get('pim_catalog.updater.attribute');
        /** @var AttributeSaver $attributeSaver */
        $attributeSaver = $this->getContainer()->get('pim_catalog.saver.attribute');
        // create attribute
        $gs1Attribute = $attributeFactory->create();
        // set attribute's data
        $attributeUpdater->update($gs1Attribute, [
            'code'                => mb_strtolower($refDataName),
            'group'               => 'technical',
            'reference_data_name' => $refDataName,
            'type'                => 'pim_reference_data_simpleselect',
            'required'            => false,
        ]);
        // save attribute into database
        $attributeSaver->save($gs1Attribute);
    }
}