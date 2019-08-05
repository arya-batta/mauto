<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 28/6/18
 * Time: 7:37 PM.
 */

namespace Mautic\LeadBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateScoreCommand extends ModeratedCommand
{
    protected function configure()
    {
        $this
            ->setName('le:score:update')
            ->setAliases(['le:score:update'])
            ->setDescription('Update score based on last lead last activity')
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
            $output->writeln('<info>'.'===================================</info>');
            $date       = new \DateTime();
            $date->modify('-2 days');
            $dateinterval = $date->format('Y-m-d H:i:s');

            $leadRepo = $container->get('mautic.lead.repository.lead');
            $result   = $leadRepo->getHotAndWarmLead($dateinterval);
            /** @var \Mautic\LeadBundle\Model\LeadModel $leadModel */
            $leadModel             = $container->get('mautic.lead.model.lead');
            $leadModel->beginTransaction();
            try {
                $output->writeln('<info>'.'Total Leads to Modify Score(s):'.count($result).' </info>');
                foreach ($result as $key => $value) {
                    $leadScore = strtolower($value['leadscore']);
                    $leadId    = $value['leadid'];
                    $lead      = $leadModel->getEntity($leadId);
                    if (!empty($leadId)) {
                        if ($leadScore == 'hot') {
                            $lead->setScore('warm');
                        } else {
                            $lead->setScore('cold');
                        }
                    }
                    $leadModel->saveEntity($lead);
                    unset($lead);
                }
                unset($result);
                $output->writeln('<info>'.'Leads Score update Completed</info>');
                $leadModel->commitTransaction();
            } catch (\Exception $ex) {
                $leadModel->rollbackTransaction();
            }
            unset($result);
            $output->writeln('<info>'.'===================================</info>');
            $date       = new \DateTime();
            $date->modify('-60 days');
            $dateinterval = $date->format('Y-m-d H:i:s');
            $leadModel->beginTransaction();
            $result = $leadRepo->getActiveEngagedLeads($dateinterval);
            try {
                $output->writeln('<info>'.'Total Leads to Modify Lead Status as (Engaged):'.count($result).' </info>');
                foreach ($result as $key => $value) {
                    $leadId = $value['leadid'];
                    $lead   = $leadModel->getEntity($leadId);
                    $lead->setStatus(2); // Engaged Status
                    $leadModel->saveEntity($lead);
                    unset($lead);
                }
                $output->writeln('<info>'.'Leads (Engaged) Status update completed.</info>');
                $leadModel->commitTransaction();
            } catch (\Exception $ex) {
                $leadModel->rollbackTransaction();
            }
            unset($result);
            $output->writeln('<info>'.'===================================</info>');
            $leadModel->beginTransaction();
            $result = $leadRepo->getActiveLeads($dateinterval);
            try {
                $output->writeln('<info>'.'Total Leads to Modify Lead Status as (Active):'.count($result).' </info>');
                foreach ($result as $key => $value) {
                    $leadId = $value['leadid'];
                    $lead   = $leadModel->getEntity($leadId);
                    $lead->setStatus(1); // Active Status
                    $leadModel->saveEntity($lead);
                    unset($lead);
                }
                $output->writeln('<info>'.'Leads (Active) Status update completed.</info>');
                $leadModel->commitTransaction();
            } catch (\Exception $ex) {
                $leadModel->rollbackTransaction();
            }
            unset($result);
            $output->writeln('<info>'.'===================================</info>');
        } catch (\Exception $e) {
            $output->writeln('exception->'.$e->getMessage()."\n");

            return 0;
        }
    }
}
