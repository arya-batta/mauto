<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\ReportBundle\Scheduler\Command;

use Mautic\ReportBundle\Exception\FileIOException;
use Mautic\ReportBundle\Model\ReportExporter;
use Mautic\ReportBundle\Scheduler\Option\ExportOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ExportSchedulerCommand extends Command
{
    /**
     * @var ReportExporter
     */
    private $reportExporter;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(ReportExporter $reportExporter, TranslatorInterface $translator)
    {
        parent::__construct();
        $this->reportExporter = $reportExporter;
        $this->translator     = $translator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('le:reports:scheduler')
            ->setDescription('Processes scheduler for report\'s export')
            ->addOption('--report', 'report', InputOption::VALUE_OPTIONAL, 'ID of report. Process all reports if not set.')
            ->addOption('--domain', '-d', InputOption::VALUE_REQUIRED, 'To load domain specific configuration', '');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $report = $input->getOption('report');

            try {
                $exportOption = new ExportOption($report);
            } catch (\InvalidArgumentException $e) {
                $output->writeln('<error>'.$this->translator->trans('mautic.report.schedule.command.invalid_parameter').'</error>');

                return;
            }

            try {
                $this->reportExporter->processExport($exportOption);

                $output->writeln('<info>'.$this->translator->trans('mautic.report.schedule.command.finished').'</info>');
            } catch (FileIOException $e) {
                $output->writeln('<error>'.$e->getMessage().'</error>');

                return;
            }
        } catch (\Exception $e) {
            $output->writeln('exception->'.$e->getMessage()."\n");

            return 0;
        }
    }
}
