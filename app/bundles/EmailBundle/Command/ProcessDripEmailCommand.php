<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 28/6/18
 * Time: 7:37 PM.
 */

namespace Mautic\EmailBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\LeadBundle\LeadEvents;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ProcessDripEmailCommand extends ModeratedCommand
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    protected function configure()
    {
        $this
            ->setName('le:dripemail:send')
            ->setAliases(['le:dripemail:send'])
            ->setDescription('Send Drip Campaigns Scheduled for Leads')
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
            $this->dispatcher      = $container->get('event_dispatcher');
            $coreParameterHelper   = $container->get('mautic.helper.core_parameters');
            $leadModel             = $container->get('mautic.lead.model.lead');
            $timezone              = $coreParameterHelper->getParameter('default_timezone');
            date_default_timezone_set($timezone);
            $currentDate       = date('Y-m-d H:i:s');
            $eventList         = $leadEventLogRepo->getScheduledEvents($currentDate);
            $completedDripsIds = [];
            foreach ($eventList as $leadEvent) {
                $completedDrips = $leadEvent->getRotation();
                if ($completedDrips == '1') {
                    $completedDripsIds[$leadEvent->getCampaign()->getId()][] = $leadEvent->getLead()->getId();
                }
                if (!empty($leadEvent->getEmail())) {
                    $isContactableReason = $leadModel->isContactable($leadEvent->getLead(), 'email');
                    $isDoNotContact      = 0;
                    if (DoNotContact::IS_CONTACTABLE !== $isContactableReason) {
                        $isDoNotContact = 1;
                    }
                    if ($isDoNotContact) {
                        $leadEvent->setIsScheduled(0);
                        $leadEvent->setSystemTriggered($currentDate);
                        $leadEventLogRepo->saveEntity($leadEvent);
                        continue;
                    }
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

            if ($this->dispatcher->hasListeners(LeadEvents::COMPLETED_DRIP_CAMPAIGN)) {
                $lead  = new Lead();
                $event = new LeadEvent($lead, true);
                $event->setCompletedDripsIds($completedDripsIds);
                $this->dispatcher->dispatch(LeadEvents::COMPLETED_DRIP_CAMPAIGN, $event);
                unset($event);
            }
        } catch (\Exception $e) {
            echo 'exception->'.$e->getMessage()."\n";
            $output->writeln('<info>'.'Exception Occured:'.$e->getMessage().'</info>');

            return 0;
        }
    }
}
