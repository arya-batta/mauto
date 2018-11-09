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

class ProcessDripEmailCommand extends ModeratedCommand
{
    protected function configure()
    {
        $this
            ->setName('le:dripemail:send')
            ->setAliases(['le:dripemail:send'])
            ->setDescription('Send Drip Emails Scheduled for Leads')
            ->addOption('--domain', '-d', InputOption::VALUE_REQUIRED, 'To load domain specific configuration', '');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $domain    = $input->getOption('domain');
            if (!$this->checkRunStatus($input, $output, $domain)) {
                return 0;
            }
            $container   = $this->getContainer();

            $leadEventLogRepo      = $container->get('mautic.email.repository.leadEventLog');
            $dripEmailModel        = $container->get('mautic.email.model.dripemail');
            $coreParameterHelper   = $container->get('mautic.helper.core_parameters');
            $timezone              = $coreParameterHelper->getParameter('default_timezone');
            date_default_timezone_set($timezone);
            $currentDate      = date('Y-m-d H:i:s');
            $eventList        = $leadEventLogRepo->getScheduledEvents($currentDate);
            foreach ($eventList as $leadEvent) {
                if (!empty($leadEvent->getEmail())) {
                    $result = $dripEmailModel->sendDripEmailtoLead($leadEvent->getEmail(), $leadEvent->getLead());
                    if ($result['result']) {
                        $leadEvent->setIsScheduled(0);
                        $leadEvent->setSystemTriggered($currentDate);
                    } else {
                        $leadEvent->setFailedReason($result['result']);
                    }
                    $leadEventLogRepo->saveEntity($leadEvent);
                    $output->writeln('<info>========================================</info>');
                    $output->writeln('<info>'.'To be Modified Email ID:'.$leadEvent->getEmail()->getId().'</info>');
                    $output->writeln('<info>'.'To be Modified Lead ID:'.$leadEvent->getLead()->getId().'</info>');
                    $output->writeln('<info>========================================</info>');
                }
            }
        } catch (\Exception $e) {
            echo 'exception->'.$e->getMessage()."\n";
            $output->writeln('<info>'.'Exception Occured:'.$e->getMessage().'</info>');

            return 0;
        }
    }
}
