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
            $date       = new \DateTime();
            $date->modify('-2 days');
            $dateinterval = $date->format('Y-m-d H:i:s');

            $leadRepo = $container->get('mautic.lead.repository.lead');
            $result   = $leadRepo->getHotAndWarmLead($dateinterval);

            foreach ($result as $key => $value) {
                $leadScore = strtolower($value['leadscore']);
                $leadId    = $value['leadid'];
                $output->writeln('<info>'.'To be Modified Lead Score:'.$leadScore.'</info>');
                $output->writeln('<info>'.'To be Modified Lead ID:'.$leadId.'</info>');

                if (!empty($leadId)) {
                    if ($leadScore == 'hot') {
                        $leadRepo->updateContactScore('warm', $leadId);
                        $output->writeln('<info>'.'Update LeadSocre As Warm LeadID:'.$leadId.'</info>');
                    } else {
                        $leadRepo->updateContactScore('cold', $leadId);
                        $output->writeln('<info>'.'Update LeadScore As Cold LeadID:'.$leadId.'</info>');
                    }
                }
            }
            $date       = new \DateTime();
            $date->modify('-60 days');
            $dateinterval = $date->format('Y-m-d H:i:s');
            /** @var \Mautic\LeadBundle\Model\LeadModel $leadModel */
            $leadModel             = $container->get('mautic.lead.model.lead');

            $leadModel->beginTransaction();
            $result = $leadRepo->getActiveEngagedLeads($dateinterval);
            try {
                foreach ($result as $key => $value) {
                    $leadId = $value['leadid'];
                    $lead   = $leadModel->getEntity($leadId);
                    $lead->setStatus(2); // Engaged Status
                    $leadModel->saveEntity($lead);
                    unset($lead);
                }
                $leadModel->commitTransaction();
            } catch (\Exception $ex) {
                $leadModel->rollbackTransaction();
            }
            $leadModel->beginTransaction();
            $result = $leadRepo->getActiveLeads($dateinterval);
            try {
                foreach ($result as $key => $value) {
                    $lead = $leadModel->getEntity($leadId);
                    $lead->setStatus(1); // Active Status
                    $leadModel->saveEntity($lead);
                    unset($lead);
                }
                $leadModel->commitTransaction();
            } catch (\Exception $ex) {
                $leadModel->rollbackTransaction();
            }
        } catch (\Exception $e) {
            echo 'exception->'.$e->getMessage()."\n";
            $output->writeln('<info>'.'Exception Occured:'.$e->getMessage().'</info>');

            return 0;
        }
    }
}
