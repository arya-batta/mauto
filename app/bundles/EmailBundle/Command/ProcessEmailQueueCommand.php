<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use Mautic\CoreBundle\Helper\EmojiHelper;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Entity\Stat;
use Mautic\EmailBundle\Event\QueueEmailEvent;
use Mautic\LeadBundle\Entity\DoNotContact;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * CLI command to process the e-mail queue.
 */
class ProcessEmailQueueCommand extends ModeratedCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('le:emails:send')
            ->setDescription('Processes SwiftMail\'s mail queue')
            ->addOption('--message-limit', null, InputOption::VALUE_OPTIONAL, 'Limit number of messages sent at a time. Defaults to value set in config.')
            ->addOption('--time-limit', null, InputOption::VALUE_OPTIONAL, 'Limit the number of seconds per batch. Defaults to value set in config.')
            ->addOption('--do-not-clear', null, InputOption::VALUE_NONE, 'By default, failed messages older than the --recover-timeout setting will be attempted one more time then deleted if it fails again.  If this is set, sending of failed messages will continue to be attempted.')
            ->addOption('--recover-timeout', null, InputOption::VALUE_OPTIONAL, 'Sets the amount of time in seconds before attempting to resend failed messages.  Defaults to value set in config.')
            ->addOption('--clear-timeout', null, InputOption::VALUE_OPTIONAL, 'Sets the amount of time in seconds before deleting failed messages.  Defaults to value set in config.')
            ->addOption('--domain', '-d', InputOption::VALUE_REQUIRED, 'To load domain specific configuration', '')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command is used to process the application's e-mail queue

<info>php %command.full_name%</info>
EOT
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $options               = $input->getOptions();
            $env                   = (!empty($options['env'])) ? $options['env'] : 'dev';
            $container             = $this->getContainer();
            $dispatcher            = $container->get('event_dispatcher');
            $translator            = $container->get('translator');
            $skipClear             = $input->getOption('do-not-clear');
            $quiet                 = $input->getOption('quiet');
            $timeout               = $input->getOption('clear-timeout');
            $queueMode             = $container->get('mautic.helper.core_parameters')->getParameter('mailer_spool_type');
            $licenseinfohelper     = $container->get('mautic.helper.licenseinfo');
            $sendResultText        = 'Success';
            if (!$this->checkRunStatus($input, $output)) {
                return 0;
            }
            if ($queueMode != 'file') {
                $output->writeln('Anyfunnels is not set to use queue email.');
                $this->completeRun();

                return 0;
            }
            $smHelper=$container->get('le.helper.statemachine');
            if (!$smHelper->isAnyActiveStateAlive()) {
                $output->writeln('<info>'.'Account is not active to proceed further.'.'</info>');
                $this->updateAllQueuedMailsAsFailed($container, $output, $translator->trans('le.email.failed.reason1'));
                $this->completeRun();

                return 0;
            }
            $elasticAccountState=$this->checkElasticAccountState($container, $output, $translator);
            if (!$elasticAccountState) {
                $this->completeRun();

                return 0;
            }
            if (empty($timeout)) {
                $timeout = $container->getParameter('mautic.mailer_spool_clear_timeout');
            }
            if (!$skipClear) {
                //Swift mailer's send command does not handle failed messages well rather it will retry sending forever
                //so let's first handle emails stuck in the queue and remove them if necessary
                $transport = $this->getContainer()->get('swiftmailer.transport.real');
                if (!$transport->isStarted()) {
                    $transport->start();
                }

                $spoolPath = $container->getParameter('mautic.mailer_spool_path');
                if (file_exists($spoolPath)) {
                    $finder  = Finder::create()->in($spoolPath)->name('*.{finalretry,sending,tryagain}');
//                    $pending = count($finder);
//                    if ($licenseinfohelper->isLeadsEngageEmailExpired($pending)) {
//                        $output->writeln('<info>========================================</info>');
//                        $output->writeln('<info>Email credits expired for LeadsEngage Provider</info>');
//                        $output->writeln('<info>========================================</info>');
//                        return 0;
//                    }
                    foreach ($finder as $failedFile) {
                        $file = $failedFile->getRealPath();

                        $lockedtime = filectime($file);
                        if (!(time() - $lockedtime) > $timeout) {
                            //the file is not old enough to be resent yet
                            continue;
                        }

                        //rename the file so no other process tries to find it
                        $tmpFilename = str_replace(['.finalretry', '.sending', '.tryagain'], '', $failedFile);
                        $tmpFilename .= '.finalretry';
                        rename($failedFile, $tmpFilename);

                        $message = unserialize(file_get_contents($tmpFilename));
                        if ($message !== false && is_object($message) && get_class($message) === 'Swift_Message') {
                            $tryAgain = false;
//                            if ($dispatcher->hasListeners(EmailEvents::EMAIL_RESEND)) {
//                                $event = new QueueEmailEvent($message);
//                                $dispatcher->dispatch(EmailEvents::EMAIL_RESEND, $event);
//                                $tryAgain = $event->shouldTryAgain();
//                            }
                            try {
                                $transport->send($message);
                            } catch (\Swift_TransportException $e) {
                                $sendResultText = 'Failed '.$e->getMessage();
                                $emailmodel     = $container->get('mautic.email.model.email');
                                $this->invokeFailedEventDispatcher($emailmodel, $message, $translator, $sendResultText);
                            }
                        } else {
                            // $message isn't a valid message file
                            $tryAgain = false;
                        }
                        if ($tryAgain) {
                            $retryFilename = str_replace('.finalretry', '.tryagain', $tmpFilename);
                            rename($tmpFilename, $retryFilename);
                        } else {
                            //delete the file, either because it sent or because it failed
                            unlink($tmpFilename);
                        }
                    }
                }
            }

            //now process new emails
            if (!$quiet) {
                $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            }

            $command     = $this->getApplication()->find('swiftmailer:spool:send');
            $commandArgs = [
            'command' => 'swiftmailer:spool:send',
            '--env'   => $env,
        ];
            if ($quiet) {
                $commandArgs['--quiet'] = true;
            }

            //set spool message limit
            if ($msgLimit = $input->getOption('message-limit')) {
                $commandArgs['--message-limit'] = $msgLimit;
            } elseif ($msgLimit = $container->getParameter('mautic.mailer_spool_msg_limit')) {
                $commandArgs['--message-limit'] = $msgLimit;
            } else {
                $commandArgs['--message-limit']=2500;
            }
            //set time limit
            if ($timeLimit = $input->getOption('time-limit')) {
                $commandArgs['--time-limit'] = $timeLimit;
            } elseif ($timeLimit = $container->getParameter('mautic.mailer_spool_time_limit')) {
                $commandArgs['--time-limit'] = $timeLimit;
            }

            //set the recover timeout
            if ($timeout = $input->getOption('recover-timeout')) {
                $commandArgs['--recover-timeout'] = $timeout;
            } elseif ($timeout = $container->getParameter('mautic.mailer_spool_recover_timeout')) {
                $commandArgs['--recover-timeout'] = $timeout;
            }
            $input      = new ArrayInput($commandArgs);
            $returnCode = $command->run($input, $output);

            $this->completeRun();
            if ($sendResultText != 'Success') {
                $container->get('mautic.helper.notification')->sendNotificationonFailure(true, false);
            }
            if ($returnCode !== 0) {
                return $returnCode;
            }

            return 0;
        } catch (\Exception $e) {
            $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
            $output->writeln('<error>'.'exception->'.$e->getMessage().'</error>');
            $this->checkElasticAccountState($container, $output, $translator);
            //$this->getContainer()->get('mautic.helper.notification')->sendNotificationonFailure(true, false, $e->getMessage());
            $this->completeRun();

            return 0;
        }
    }

    public function checkElasticAccountState($container, $output, $translator)
    {
        $providerStatus=$this->checkEmailProviderStatus($container, $output);
        if (!$providerStatus) {
            $this->updateAllQueuedMailsAsFailed($container, $output, $translator->trans('le.email.failed.reason2'));

            return false;
        } else {
            return true;
        }
    }

    public function checkEmailProviderStatus($container, $output)
    {
        $smHelper           =$container->get('le.helper.statemachine');
        $paymentrepository  =$container->get('le.subscription.repository.payment');
        $lastpayment        = $paymentrepository->getLastPayment();
        $prefix             = 'Trial';
        if ($lastpayment != null) {
            $prefix = 'Customer';
        }
        if (!$smHelper->isStateAlive($prefix.'_Inactive_Under_Review')) {
            $elasticApiHelper=$container->get('mautic.helper.elasticapi');
            if (!$elasticApiHelper->checkAccountState()) {
                try {
                    $smHelper->makeStateInActive([$prefix.'_Active']);
                    $smHelper->newStateEntry($prefix.'_Inactive_Under_Review', '');
                    $smHelper->addStateWithLead();
                    $smHelper->sendInactiveUnderReviewEmail();
                    $output->writeln('<info>App enters into '.$prefix.'_Inactive_Under_Review</info>');

                    return false;
                } catch (\Exception $ex) {
                    $output->writeln('<error>'.'Exception Occurs at under review process:'.$ex->getMessage().'</error>');

                    return false;
                }
            } else {
                $output->writeln('<info>Elastic Email Account is active</info>');

                return true;
            }
        } else {
            return false;
        }
    }

    public function invokeFailedEventDispatcher($emailModel, $message, $translator, $reason, $flush=true)
    {
        if (isset($message->leadIdHash)) {
            $stat = $emailModel->getEmailStatus($message->leadIdHash);
            if ($stat !== null) {
//                $reason = $translator->trans('le.email.dnc.failed', [
//                    '%subject%' => EmojiHelper::toShort($message->getSubject()),
//                ]);
                $emailModel->setDoNotContact($stat, $reason, DoNotContact::IS_CONTACTABLE, $flush);
                unset($stat);
                if ($flush) {
                    $emailModel->getEntityManager()->clear(Stat::class);
                }
            }
        }
//        $dispatcher = $container->get('event_dispatcher');
//        if ($dispatcher->hasListeners(EmailEvents::EMAIL_FAILED)) {
//            $event = new QueueEmailEvent($message);
//            $dispatcher->dispatch(EmailEvents::EMAIL_FAILED, $event);
//        }
    }

    public function updateAllQueuedMailsAsFailed($container, $output, $reason)
    {
        try {
            $emailmodel            = $container->get('mautic.email.model.email');
            $translator            = $container->get('translator');
            $spoolPath             = $container->getParameter('mautic.mailer_spool_path');
            if (file_exists($spoolPath)) {
                // Try to save some memory
                gc_enable();
                $output->writeln('<info>'.'Process started to update all queued mails as failed'.'</info>');
                $finder      = Finder::create()->in($spoolPath)->name('*.*');
                $updatedCount=0;
                $batch       =500;
                $allflushed  =true;
                foreach ($finder as $messageFile) {
                    if (false) { //with stat update
                        $message = unserialize($messageFile->getContents());
                        if ($message !== false && is_object($message) && get_class($message) === 'Swift_Message') {
                            $this->invokeFailedEventDispatcher($emailmodel, $message, $translator, $reason, false);
                            //delete the file, either because it sent or because it failed
                            unlink($messageFile);
                            ++$updatedCount;
                            --$batch;
                            if ($batch == 0) {
                                sleep(2);
                                // Free some memory
                                gc_collect_cycles();
                                $batch=500;
                                $emailmodel->getEntityManager()->flush();
                                $emailmodel->getEntityManager()->clear(Stat::class);
                                $allflushed=true;
                            } else {
                                $allflushed=false;
                            }
                        }
                        unset($message, $messageFile);
                    } else { //without stat update
                        unlink($messageFile);
                        ++$updatedCount;
                        --$batch;
                        if ($batch == 0) {
                            sleep(2);
                            // Free some memory
                            gc_collect_cycles();
                            $batch=1000;
                        }
                        unset($messageFile);
                    }
                }
                unset($finder);

                if (!$allflushed) {
                    $emailmodel->getEntityManager()->flush();
                    $emailmodel->getEntityManager()->clear(Stat::class);
                }
                $output->writeln('<info>'.'Updated email count is '.$updatedCount.'</info>');
                $output->writeln('<info>'.'Process completed to update all queued mails as failed'.'</info>');
            }
        } catch (\Exception $ex) {
            $output->writeln('<info>'.'Exception Occurs at spool path cleanup:'.$ex->getMessage().'</info>');
        }
    }
}
