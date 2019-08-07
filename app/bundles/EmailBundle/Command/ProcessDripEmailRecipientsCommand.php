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
use Symfony\Component\EventDispatcher\EventDispatcher;

class ProcessDripEmailRecipientsCommand extends ModeratedCommand
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    protected function configure()
    {
        $this
            ->setName('le:dripemail:rebuild')
            ->setAliases(['le:dripemail:rebuild'])
            ->setDescription('Send Drip Campaigns Scheduled for Leads')
            ->addOption('--batch-limit', '-b', InputOption::VALUE_OPTIONAL, 'Set batch size of contacts to process per round. Defaults to 300.', 300)
            ->addOption('--drip-id', '-i', InputOption::VALUE_OPTIONAL, 'Specific ID to rebuild. Defaults to all.', false)
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
            /** @var \Mautic\EmailBundle\Model\DripEmailModel $dripEmailModel */
            $dripEmailModel        = $container->get('mautic.email.model.dripemail');
            $this->dispatcher      = $container->get('event_dispatcher');
            $coreParameterHelper   = $container->get('mautic.helper.core_parameters');
            $translator            = $container->get('translator');
            /** @var \Mautic\LeadBundle\Model\LeadModel $leadModel */
            $leadModel             = $container->get('mautic.lead.model.lead');
            $timezone              = $coreParameterHelper->getParameter('default_timezone');
            date_default_timezone_set($timezone);
            $id                = $input->getOption('drip-id');
            $batch             = $input->getOption('batch-limit');
            $currentDate       = date('Y-m-d H:i:s');
            if ($id) {
                $drip = $dripEmailModel->getEntity($id);
                if ($drip !== null && $drip->getisScheduled()) {
                    $output->writeln('<info>'.$translator->trans('le.drip.email.lead.rebuild.rebuilding', ['%id%' => $id]).'</info>');
                    $processed = $dripEmailModel->rebuildLeadRecipients($drip, $batch, false, $output);
                    $output->writeln(
                        '<comment>'.$translator->trans('le.drip.email.lead.rebuild.leads_affected', ['%leads%' => $processed]).'</comment>'
                    );
                    $drip->setisScheduled(0);
                    $dripEmailModel->saveEntity($drip);
                } else {
                    $output->writeln('<error>'.$translator->trans('le.drip.email.lead.rebuild.not_found', ['%id%' => $id]).'</error>');
                }
            } else {
                $drips = $dripEmailModel->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'd.isPublished',
                                    'expr'   => 'eq',
                                    'value'  => 1,
                                ],
                            ],
                        ],
                        'iterator_mode' => true,
                    ]
                );

                while (($d = $drips->next()) !== false) {
                    // Get first item; using reset as the key will be the ID and not 0
                    $d = reset($d);

                    $output->writeln('<info>'.$translator->trans('le.drip.email.lead.rebuild.rebuilding', ['%id%' => $d->getId()]).'</info>');
                    if ($d->getisScheduled()) {
                        $processed = $dripEmailModel->rebuildLeadRecipients($d, $batch, false, $output);
                        $output->writeln(
                            '<comment>'.$translator->trans('le.drip.email.lead.rebuild.leads_affected', ['%leads%' => $processed]).'</comment>'."\n"
                        );
                        $d->setisScheduled(0);
                        $dripEmailModel->saveEntity($d);
                    }

                    unset($d);
                }

                unset($drips);
            }
            $this->completeRun();

            return 0;
        } catch (\Exception $e) {
            $output->writeln('exception->'.$e->getMessage()."\n");
            $this->completeRun();

            return 0;
        }
    }
}
