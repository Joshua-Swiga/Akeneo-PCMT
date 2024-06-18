<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Command\Creator;

use Doctrine\ORM\EntityManagerInterface;
use PcmtCoreBundle\Exception\InvalidJobConfigurationException;
use PcmtCoreBundle\Exception\UnknownJobException;
use PcmtCoreBundle\Registry\PcmtConnectorJobParametersRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcmtJobCreator extends ContainerAwareCommand
{
    /** @var string */
    protected static $defaultName = 'pcmt:job-creator';

    /** @var int */
    private $trialCount = 4;

    public function configure(): void
    {
        $this->addArgument('jobName', InputArgument::REQUIRED, 'Pcmt Job registry code. Used to parse job creation parameters. Defined in PcmtConnectorJobParametersRegistry::class');
    }

    /**
     * @return bool|int|null
     *
     * @throws InvalidJobConfigurationException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $jobInstanceParameters = PcmtConnectorJobParametersRegistry::getConfig($input->getArgument('jobName'));
            $jobInstanceClass = $this->getContainer()->getParameter('akeneo_batch.entity.job_instance.class');
            $jobInstance = $this->getEntityManager()->getRepository($jobInstanceClass)->findOneBy(['code' => $jobInstanceParameters['code']]);

            if (null === $jobInstance) {
                throw new UnknownJobException('Job  ' . $jobInstanceParameters['code'] . ' undefinded.');
            }

            $output->writeln('Job  ' . $jobInstanceParameters['code'] . ' found. Starting job execution.');

            return true;
        } catch (UnknownJobException $exception) {
            $output->writeln($exception->getMessage());

            if ($this->trialCount > 0) {
                $this->trialCount --;
                $output->writeln('Trying to (re)create job instance: ' . $jobInstanceParameters['code']);

                if ($this->createJobInstanceFromParameters($jobInstanceParameters, $output)) {
                    $this->execute($input, $output);
                }
            } else {
                throw new InvalidJobConfigurationException('Unable to (re)create job instance: ' . $jobInstanceParameters['code'] . ' check jobs.yml.');
            }
        }
    }

    private function createJobInstanceFromParameters(array $jobInstanceParameters, OutputInterface $output): bool
    {
        $command = $this->getApplication()->find('akeneo:batch:create-job');

        $arguments = [
            'connector' => $jobInstanceParameters['connector'],
            'job'       => $jobInstanceParameters['job'],
            'type'      => $jobInstanceParameters['type'],
            'code'      => $jobInstanceParameters['code'],
            'config'    => $jobInstanceParameters['config'] ?? null,
        ];

        $input = new ArrayInput($arguments);

        if (0 === $command->run($input, $output)) {
            return true;
        }

        return false;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }
}