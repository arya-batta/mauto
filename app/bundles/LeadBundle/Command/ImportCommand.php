<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Command;

use Mautic\LeadBundle\Exception\ImportDelayedException;
use Mautic\LeadBundle\Exception\ImportFailedException;
use Mautic\LeadBundle\Helper\Progress;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI Command to import data.
 */
class ImportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('le:import')
            ->setDescription('Imports data to LE')
            ->addOption('--id', '-i', InputOption::VALUE_OPTIONAL, 'Specific ID to import. Defaults to next in the queue.', false)
            ->addOption('--limit', '-l', InputOption::VALUE_OPTIONAL, 'Maximum number of records to import for this script execution.', 0)
            ->addOption('--domain', '-d', InputOption::VALUE_REQUIRED, 'To load domain specific configuration', '')
            ->setHelp(
                <<<'EOT'
The <info>%command.name%</info> command starts to import CSV files when some are created.

<info>php %command.full_name%</info>
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
            $translator = $this->getContainer()->get('translator');

            /** @var \Mautic\LeadBundle\Model\ImportModel $model */
            $model = $this->getContainer()->get('mautic.lead.model.import');

            $smHelper=$this->getContainer()->get('le.helper.statemachine');
            if (!$smHelper->isAnyActiveStateAlive()) {
                $output->writeln('<info>'.'Account is not active to proceed further.'.'</info>');

                return 0;
            }

            $imports  = [];
            $progress = new Progress($output);
            $id       = (int) $input->getOption('id');
            $limit    = (int) $input->getOption('limit');

            if ($id) {
                $import = $model->getEntity($id);

                // This specific import was not found
                if (!$import) {
                    $output->writeln('<error>'.$translator->trans('mautic.core.error.notfound', [], 'flashes').'</error>');

                    return 1;
                }
                $imports[] = $import;
            } else {
                $imports = $model->getImportToProcess();

                // No import waiting in the queue. Finish silently.
                if ($imports === null || empty($imports)) {
                    return 0;
                }
            }

            foreach ($imports as $key => $import) {
                $start = microtime(true);

                $output->writeln('<info>'.$translator->trans(
                        'le.lead.import.is.starting',
                        [
                            '%id%'    => $import->getId(),
                            '%lines%' => $import->getLineCount(),
                        ]
                    ).'</info>');

                try {
                    $model->beginImport($import, $progress, $limit);
                } catch (ImportFailedException $e) {
                    $output->writeln('<error>'.$translator->trans(
                            'le.lead.import.failed',
                            [
                                '%reason%' => $import->getStatusInfo(),
                            ]
                        ).'</error>');

                    // return 1;
                    continue;
                } catch (ImportDelayedException $e) {
                    $output->writeln('<info>'.$translator->trans(
                            'le.lead.import.delayed',
                            [
                                '%reason%' => $import->getStatusInfo(),
                            ]
                        ).'</info>');

                    // return 0;
                    continue;
                }

                // Success
                $output->writeln('<info>'.$translator->trans(
                        'le.lead.import.result',
                        [
                            '%lines%'   => $import->getProcessedRows(),
                            '%created%' => $import->getInsertedCount(),
                            '%updated%' => $import->getUpdatedCount(),
                            '%ignored%' => $import->getIgnoredCount(),
                            '%time%'    => round(microtime(true) - $start, 2),
                        ]
                    ).'</info>');
            }

            return 0;
        } catch (\Exception $e) {
            $output->writeln('exception->'.$e->getMessage()."\n");

            return 0;
        }
    }
}
