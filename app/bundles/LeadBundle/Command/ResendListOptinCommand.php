<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 28/6/18
 * Time: 7:37 PM.
 */

namespace Mautic\LeadBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use Mautic\CoreBundle\Helper\ProgressBarHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResendListOptinCommand extends ModeratedCommand
{
    protected function configure()
    {
        $this
            ->setName('le:listoptin:resend')
            ->setAliases(['le:listoptin:resend'])
            ->setDescription('Update score based on last lead last activity')
            ->addOption('--batch-limit', null, InputOption::VALUE_OPTIONAL, 'Limit number of leads sent at a time. Defaults to value is 500.')
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
            $container  = $this->getContainer();
            $smHelper   =$container->get('le.helper.statemachine');
            if (!$smHelper->isAnyActiveStateAlive()) {
                $output->writeln('<info>'.'Account is not active to proceed further.'.'</info>');

                return 0;
            }
            $batchLimit = (!empty($options['batch-limit'])) ? $options['batch-limit'] : 500;
            $date       = new \DateTime();
            $date->modify('-2 days');
            $dateinterval = $date->format('Y-m-d H:i:s');

            $translator  = $container->get('translator');
            $listModel   = $container->get('mautic.lead.model.listoptin');
            $listRepo    = $listModel->getListLeadRepository();
            $maxCount    = $listRepo->getResendListsCount($dateinterval);
            if ($output) {
                $output->writeln($translator->trans('le.lead.resend.lead.send.to_be_added', ['%events%' => $maxCount, '%batch%' => $batchLimit]));
            }
            $result       = $listRepo->getResendLists($dateinterval, $batchLimit);
            $pendingCount = count($result);
            if ($pendingCount) {
                if ($output) {
                    $progress = ProgressBarHelper::init($output, $maxCount);
                    $progress->start();
                }
                $eventsProcessed = 0;
                // Try to save some memory
                gc_enable();
                $leadModel = $container->get('mautic.lead.model.lead');
                while ($pendingCount > 0) {
                    $leadModel->beginTransaction();
                    try {
                        foreach ($result as $list) {
                            $lead      = $list->getLead();
                            $lead      = $leadModel->getEntity($lead->getId()); //$lead->getId();
                            $listoptin = $list->getList();
                            $listoptin = $listModel->getEntity($listoptin->getId()); //$listoptin->getId();
                            if ($listoptin != null && $lead != null && $list != null && $lead->getId() != null) {
                                $listModel->scheduleListOptInEmail($listoptin, $lead, $list);
                                $list->setIsrescheduled(0);
                                $listRepo->saveEntity($list);
                            }
                            unset($list, $listoptin);
                            ++$eventsProcessed;
                        }
                        $leadModel->commitTransaction();
                    } catch (\Exception $ex) {
                        $leadModel->rollbackTransaction();
                    }
                    if ($output && $eventsProcessed < $maxCount) {
                        $progress->setProgress($eventsProcessed);
                    }
                    sleep(2);
                    $result         = $listRepo->getResendLists($dateinterval, $batchLimit);
                    $pendingCount   = count($result);
                }
                unset($result);
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
