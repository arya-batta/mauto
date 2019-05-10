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

class ResendListOptinCommand extends ModeratedCommand
{
    protected function configure()
    {
        $this
            ->setName('le:listoptin:resend')
            ->setAliases(['le:listoptin:resend'])
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

            $listModel = $container->get('mautic.lead.model.listoptin');
            $listRepo  = $listModel->getListLeadRepository();
            $result    = $listRepo->getResendLists($dateinterval);
            $leadModel = $container->get('mautic.lead.model.lead');
            $leadModel->beginTransaction();
            try {
                foreach ($result as $list) {
                    $lead      = $list->getLead();
                    $lead      = $leadModel->getEntity($lead->getId());
                    $listoptin = $list->getList();
                    $listoptin = $listModel->getEntity($listoptin->getId());
                    $listModel->scheduleListOptInEmail($listoptin, $lead, $list);
                    $list->setIsrescheduled(0);
                    $listRepo->saveEntity($list);
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
