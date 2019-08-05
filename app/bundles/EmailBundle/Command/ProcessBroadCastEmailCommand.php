<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 28/6/18
 * Time: 7:37 PM.
 */

namespace Mautic\EmailBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessBroadCastEmailCommand extends ModeratedCommand
{
    protected function configure()
    {
        $this
            ->setName('le:oneoff:send')
            ->setAliases(['le:oneoff:send'])
            ->setDescription('Send Drip Campaigns Scheduled for Leads')
            ->addOption('--email-limit', null, InputOption::VALUE_OPTIONAL, 'Limit number of Emails sent at a time. Defaults to value is 300.')
            ->addOption('--domain', '-d', InputOption::VALUE_REQUIRED, 'To load domain specific configuration', '');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $domain = $input->getOption('domain');
            if (!$this->checkRunStatus($input, $output, $domain)) {
                return 0;
            }
            $container           = $this->getContainer();
            $smHelper            =$container->get('le.helper.statemachine');
            if (!$smHelper->isAnyActiveStateAlive()) {
                $output->writeln('<info>'.'Account is not active to proceed further.'.'</info>');

                return 0;
            }
            $em                  = $container->get('doctrine')->getManager();
            $emailmodel          = $container->get('mautic.email.model.email');
            $coreParameterHelper = $container->get('mautic.helper.core_parameters');
            $leadModel           = $container->get('mautic.lead.model.lead');
            $timezone            = $coreParameterHelper->getParameter('default_timezone');
            $emails              = $emailmodel->getEntities(
                [
                    'filter'           => [
                        'force' => [
                            [
                                'column' => 'e.isScheduled',
                                'expr'   => 'eq',
                                'value'  => 1,
                            ],
                            [
                                'column' => 'e.emailType',
                                'expr'   => 'eq',
                                'value'  => 'list',
                            ],
                        ],
                    ],
                    'ignore_paginator' => true,
                ]
            );
            if (!count($emails)) {
                $output->writeln('<info>========================================</info>');
                $output->writeln('<info>No Email(s) has been Scheduled.</info>');
                $output->writeln('<info>========================================</info>');

                return 0;
            }
            $emailLimit = (!empty($options['email-limit'])) ? $options['email-limit'] : 500;
            foreach ($emails as $email) {
                $leads       = $emailmodel->getPendingLeads($email, null, false, $emailLimit); //$list->getId()
                $leadCount   = count($leads);
                $sentCount   = 0;
                $failedCount = 0;
                $options     = [
                    'source'        => ['email', $email->getId()],
                    'allowResends'  => false,
                    'customHeaders' => [
                        'Precedence' => 'Bulk',
                    ],
                ];
                while ($leadCount > 0) {
                    $sentCount += $leadCount;
                    $listErrors = $emailmodel->sendEmail($email, $leads, $options);
                    if (!empty($listErrors)) {
                        $listFailedCount = count($listErrors);
                        $sentCount -= $listFailedCount;
                        $failedCount += $listFailedCount;
                    }
                    sleep(2);
                    // Get the next batch of leads
                    $leads     = $emailmodel->getPendingLeads($email, null, false, $emailLimit); //$list->getId()
                    $leadCount = count($leads);
                }
                if ($leadCount == 0) {
                    $email->setIsScheduled(false);
                    $emailmodel->saveEntity($email);
                    $output->writeln('<info>========================================</info>');
                    $output->writeln('<info>Name of the Email Scheduled : '.$email->getName().'</info>');
                    $output->writeln('<info>Total Send Count for Email :'.$sentCount.'</info>');
                    $output->writeln('<info>Total Failed Count for Email :'.$failedCount.'</info>');
                    $output->writeln('<info>========================================</info>');
                }
            }
            $this->completeRun();

            return 0;
        } catch (\Exception $e) {
            $output->writeln('exception->'.$e->getMessage()."\n");

            return 0;
        }
    }
}
