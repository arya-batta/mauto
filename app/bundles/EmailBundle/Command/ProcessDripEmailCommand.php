<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 28/6/18
 * Time: 7:37 PM.
 */

namespace Mautic\EmailBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use Mautic\CoreBundle\Helper\ProgressBarHelper;
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
            $smHelper    =$container->get('le.helper.statemachine');
            if (!$smHelper->isAnyActiveStateAlive()) {
                $output->writeln('<info>'.'Account is not active to proceed further.'.'</info>');

                return 0;
            }
            $leadEventLogRepo      = $container->get('mautic.email.repository.leadEventLog');
            $dripEmailModel        = $container->get('mautic.email.model.dripemail');
            $this->dispatcher      = $container->get('event_dispatcher');
            $leadModel             = $container->get('mautic.lead.model.lead');
            $webhookModel          = $container->get('mautic.webhook.model.webhook');
            $emailModel            = $container->get('mautic.email.model.email');
            $translator            = $container->get('translator');
            date_default_timezone_set('UTC');
            $currentDate       = date('Y-m-d H:i:s');
            $emailLimit        = (!empty($options['email-limit'])) ? $options['email-limit'] : 300;
            $maxCount          =$leadEventLogRepo->getScheduledEventsCount($currentDate);
            if ($output) {
                $output->writeln($translator->trans('le.drip.email.lead.send.to_be_added', ['%events%' => $maxCount, '%batch%' => $emailLimit]));
            }
            $eventList         = $leadEventLogRepo->getScheduledEvents($currentDate, $emailLimit);
            $completedDripsIds = [];
            $pendingCount      = count($eventList);
            if ($pendingCount) {
                if ($output) {
                    $progress = ProgressBarHelper::init($output, $maxCount);
                    $progress->start();
                }
                $eventsProcessed=0;
                // Try to save some memory
                gc_enable();
                $triggerWebHookEvent=true;
                $webhookEvents      =$webhookModel->getEventWebooksByType(LeadEvents::LEAD_COMPLETED_DRIP_CAMPAIGN);
                if (!count($webhookEvents) || !is_array($webhookEvents)) {
                    $triggerWebHookEvent=false;
                }
                while ($pendingCount > 0) {
                    try {
                        $groupLeadsByEmail=[];
                        $dripEmailModel->beginTransaction();
                        foreach ($eventList as $leadEvent) {
                            $completedDrips = $leadEvent->getRotation();
                            $dripLead       =$leadEvent->getLead();
                            $dripEmail      =$leadEvent->getEmail();
                            if ($completedDrips == '1') {
                                $completedDripsIds[$leadEvent->getCampaign()->getId()][] = $dripLead->getId();
                            }
                            if (!empty($dripEmail)) {
                                $dripLeadData                                              = $leadModel->getRepository()->getLead($dripLead->getId());
                                $groupLeadsByEmail[$dripEmail->getId()][$dripLead->getId()]=$dripLeadData;
                                $leadEvent->setIsScheduled(0);
                                $leadEvent->setSystemTriggered($currentDate);
                                $leadEventLogRepo->saveEntity($leadEvent);
                                unset($dripLead, $dripEmail, $leadEvent, $dripLeadData);
                            }
                            ++$eventsProcessed;
                        }
                        foreach ($groupLeadsByEmail as $emailId => $leads) {
                            $options     = [
                                'source'        => ['email', $emailId],
                                'allowResends'  => false,
                                'customHeaders' => [
                                    'Precedence' => 'Bulk',
                                ],
                            ];
                            $dripEmailEntity=$emailModel->getEntity($emailId);
                            $emailModel->sendEmail($dripEmailEntity, $leads, $options);
                            unset($dripEmailEntity);
                        }
                        unset($groupLeadsByEmail);
                        $dripEmailModel->commitTransaction();
                    } catch (\Exception $ex) {
                        $output->writeln('Exception Occured at batch execution->'.$ex->getMessage());
                        $dripEmailModel->rollbackTransaction();
                        throw $ex;
                    }
                    if (!empty($completedDripsIds)) {
                        if ($this->dispatcher->hasListeners(LeadEvents::COMPLETED_DRIP_CAMPAIGN)) {
                            $lead  = new Lead();
                            $event = new LeadEvent($lead, true);
                            $event->setCompletedDripsIds($completedDripsIds);
                            $this->dispatcher->dispatch(LeadEvents::COMPLETED_DRIP_CAMPAIGN, $event);
                            unset($event);
                        }
                        if ($triggerWebHookEvent && $this->dispatcher->hasListeners(LeadEvents::LEAD_COMPLETED_DRIP_CAMPAIGN)) {
                            $lead  = new Lead();
                            $event = new LeadEvent($lead, true);
                            $event->setCompletedDripsIds($completedDripsIds);
                            $this->dispatcher->dispatch(LeadEvents::LEAD_COMPLETED_DRIP_CAMPAIGN, $event);
                            unset($event);
                        }
                        unset($completedDripsIds);
                    }
                    if ($output && $eventsProcessed < $maxCount) {
                        $progress->setProgress($eventsProcessed);
                    }
                    sleep(2);
                    $eventList         = $leadEventLogRepo->getScheduledEvents($currentDate, $emailLimit);
                    $pendingCount      = count($eventList);
                }
                // Free some memory
                gc_collect_cycles();
                if ($output) {
                    $progress->finish();
                    $output->writeln('');
                }
            }
        } catch (\Exception $e) {
            echo 'exception->'.$e->getMessage()."\n";
            $output->writeln('<info>'.'Exception Occured:'.$e->getMessage().'</info>');

            return 0;
        }
    }
}
