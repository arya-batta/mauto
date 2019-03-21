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
            ->addOption('--email-limit', null, InputOption::VALUE_OPTIONAL, 'Limit number of Emails sent at a time. Defaults to value is 300.')
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
            date_default_timezone_set('UTC');
            $currentDate       = date('Y-m-d H:i:s');
            $eventList         = $leadEventLogRepo->getScheduledEvents($currentDate);
            $emailsCount       = 0;
            $emailLimit        = (!empty($options['email-limit'])) ? $options['email-limit'] : 300;
            $completedDripsIds = [];
            /*$licenseinfohelper = $container->get('mautic.helper.licenseinfo');
            $pending = count($eventList);
            if ($licenseinfohelper->isLeadsEngageEmailExpired($pending)) {
                $output->writeln('<info>========================================</info>');
                $output->writeln('<info>Email credits expired for LeadsEngage Provider</info>');
                $output->writeln('<info>========================================</info>');
                return 1;
            }*/
            foreach ($eventList as $leadEvent) {
                $completedDrips = $leadEvent->getRotation();
                if ($completedDrips == '1') {
                    $completedDripsIds[$leadEvent->getCampaign()->getId()][] = $leadEvent->getLead()->getId();
                }
                if ($emailLimit == $emailsCount) {
                    sleep(2);
                    $emailsCount = 0;
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
                        $leadEvent->setFailedReason('This Lead is Skipped because of Lead is DoNotContact');
                        $leadEventLogRepo->saveEntity($leadEvent);
                        continue;
                    }
                    $emailsCount = $emailsCount++;
                    $result      = $dripEmailModel->sendDripEmailtoLead($leadEvent->getEmail(), $leadEvent->getLead());
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
                if ($completedDrips == '1') {
                    if ($this->dispatcher->hasListeners(LeadEvents::LEAD_COMPLETED_DRIP_CAMPAIGN)) {
                        $lead  = $leadEvent->getLead();
                        $event = new LeadEvent($lead, true);
                        $event->setDrip($leadEvent->getCampaign());
                        $this->dispatcher->dispatch(LeadEvents::LEAD_COMPLETED_DRIP_CAMPAIGN, $event);
                        unset($event);
                    }
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
