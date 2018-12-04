<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SubscriptionBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use Mautic\SubscriptionBundle\Entity\Billing;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePaymentCommand extends ModeratedCommand
{
    protected function configure()
    {
        $this
            ->setName('le:payment:update')
            ->setAliases(['le:payment:update'])
            ->setDescription('Update payment based on last payment validity end')
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
            // $translator = $container->get('translator');
            // $translator->trans('mautic.campaign.rebuild.leads_affected', ['%leads%' => $processed])
            $paymentrepository  =$container->get('le.subscription.repository.payment');
            $lastpayment        =$paymentrepository->getLastPayment();
            $licenseinfohelper  =$container->get('mautic.helper.licenseinfo');
            $licenseinfo        =$licenseinfohelper->getLicenseEntity();
            $accountStatus      =$licenseinfo->getAppStatus();
            if ($accountStatus != 'Active') {
                $output->writeln('<info>'.'Account is not active to proceed further.'.'</info>');

                return 0;
            }
            if ($lastpayment != null) {
                $paymenthelper     =$container->get('le.helper.payment');
                if ($licenseinfo != null) {
                    $totalrecordcount =$licenseinfo->getTotalRecordCount();
                    $actualrecordcount=$licenseinfo->getActualRecordCount();
                    $validitytill     = $lastpayment->getValidityTill();
                    $currentdate      = date('Y-m-d');
                    $planname         = $lastpayment->getPlanName();
                    $planamount       = $lastpayment->getAmount();
                    $lastamount       = $lastpayment->getNetamount();
                    $plancredits      = $lastpayment->getBeforeCredits();
                    $stripecardrepo   = $container->get('le.subscription.repository.stripecard');
                    $stripecards      = $stripecardrepo->findAll();
                    $stripecard       = null;
                    if (sizeof($stripecards) > 0) {
                        $stripecard = $stripecards[0];
                    }
                    $monthcount = 1;
                    if ($planname != 'leplan1') {
                        $planname = 'leplan1';
                    }
                    if ($planname == 'leplan2') {
                        $monthcount = 12;
                    }
                    if ($stripecard != null) {
                        $ismoreusage=false;
                        if ($totalrecordcount != 'UL' && $totalrecordcount < $actualrecordcount) {
                            $ismoreusage=true;
                        }
                        $isvalidityexpired=false;
                        if (strtotime($validitytill) < strtotime($currentdate)) {
                            $isvalidityexpired=true;
                        }
                        if ($ismoreusage || $isvalidityexpired) {
                            $output->writeln('<info>'.'Total Record Count:'.$totalrecordcount.'</info>');
                            $output->writeln('<info>'.'Actual Record Count:'.$actualrecordcount.'</info>');
                            $multiplx=1;
                            if ($actualrecordcount > 0) {
                                $multiplx   = ceil($actualrecordcount / 5000);
                                $multiplx   = $multiplx - 5;
                            }
                            if ($isvalidityexpired) {
                                $netamount   = (($planamount)); // + (10 * $multiplx));
                                $netcredits  = (($plancredits)); // + (5000 * $multiplx));
                                $validitytill=date('Y-m-d', strtotime('-1 day +'.$monthcount.' months'));
                            } elseif ($ismoreusage) {
                                //$amount1   =$this->getProrataAmount($currentdate, $validitytill, $lastamount);
                                $excesscount=$actualrecordcount - $totalrecordcount;
                                $amtmultiplx=1;
                                if ($excesscount > 0) {
                                    $amtmultiplx   =ceil($excesscount / 5000);
                                }
                                $netamount   = (10 * $amtmultiplx);
                                $netcredits  = (($plancredits) + (5000 * $multiplx));
                                $netamount   =$this->getProrataAmount($output, $currentdate, $validitytill, $netamount);
                                //$output->writeln('<info>'.'Refund Amount:'.$amount1.'</info>');
                                // $output->writeln('<info>'.'Charged Amount:'.$amount2.'</info>');
                                // $netamount=$amount2 - $amount1;
                                $output->writeln('<info>'.'Net Amount:'.$netamount.'</info>');
                            }
                            if ($netamount > 0) {
                                $this->attemptStripeCharge($output, $stripecard, $paymenthelper, $paymentrepository, $planname, $planamount, $plancredits, $netamount, $netcredits, $validitytill);
                            } else {
                                $output->writeln('<error>'.'Amount is too less to charge:'.$netamount.'</error>');
                            }
                        } else {
                            $output->writeln('<info>'."Plan validity available upto $validitytill".'</info>');
                        }
                    } else {
                        $output->writeln('<error>'.'Customer Credit Card details not found.'.'</error>');
                    }
                } else {
                    $output->writeln('<error>'.'License info details not found.'.'</error>');
                }
            } else {
                $output->writeln('<error>'.'Last payment details not found.'.'</error>');
//                $output->writeln(
//                    '<comment>'."LeadsEngage Comment".'</comment>'."\n"
//                );
            }
            $this->completeRun();

            return 0;
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            $body = $e->getJsonBody();
            $err  = $body['error'];

            //  print('Status is:' . $e->getHttpStatus() . "\n");
            // print('Type is:' . $err['type'] . "\n");
            //print('Code is:' . $err['code'] . "\n");
            // param is '' in this case
            //  print('Param is:' . $err['param'] . "\n");
            // print('Message is:' . $err['message'] . "\n");
            $errormsg      ='Card Error:'.$err['message'];
            $payment       =$paymentrepository->captureStripePayment('', '', $planamount, $netamount, $plancredits, $netcredits, $validitytill, $planname, null, null, $errormsg);

            $licenseinfohelper->suspendApplication();
        } catch (\Stripe\Error\RateLimit $e) {
            $errormsg= 'Too many requests made to the API too quickly';
            // Too many requests made to the API too quickly
        } catch (\Stripe\Error\InvalidRequest $e) {
            $errormsg= "Invalid parameters were supplied to Stripe's API->".$e->getMessage();
            // Invalid parameters were supplied to Stripe's API
        } catch (\Stripe\Error\Authentication $e) {
            $errormsg= "Authentication with Stripe's API failed";
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
        } catch (\Stripe\Error\ApiConnection $e) {
            $errormsg= 'Network communication with Stripe failed';
            // Network communication with Stripe failed
        } catch (\Stripe\Error\Base $e) {
            $errormsg= 'Display a very generic error to the user, and maybe send->'.$e->getMessage();
            // Display a very generic error to the user, and maybe send
            // yourself an email
        } catch (\Exception $e) {
            $errormsg= 'General Error:'.$e->getMessage();
            // Something else happened, completely unrelated to Stripe
        }
        if ($errormsg != '') {
            $output->writeln('<error>'.$errormsg.'</error>');
            echo 'exception->'.$errormsg."\n";

            return 0;
        }
    }

    protected function attemptStripeCharge($output, $stripecard, $paymenthelper, $paymentrepository, $planname, $planamount, $plancredits, $netamount, $netcredits, $validitytill)
    {
        $container  = $this->getContainer();
        $apikey     =$container->get('mautic.helper.core_parameters')->getParameter('stripe_api_key');
        \Stripe\Stripe::setApiKey($apikey);
        $charges = \Stripe\Charge::create([
            'amount'   => $netamount * 100, //100 cents = 1 dollar
            'currency' => 'usd',
            //"source" => $token, // obtained with Stripe.js
            'customer'             => $stripecard->getCustomerID(),
            'description'          => 'charge for leadsengage product purchase',
            'capture'              => true,
            'statement_descriptor' => 'leadsengage purchase',
        ], [
            'idempotency_key' => $paymenthelper->getUUIDv4(),
        ]);
        if (isset($charges)) {
            $orderid         = uniqid();
            $chargeid        = $charges->id;
            $status          = $charges->status;
            $failure_code    = $charges->failure_code;
            $failure_message = $charges->failure_message;
            if ($status == 'succeeded') {
                $todaydate     = date('Y-m-d');
                $payment       =$paymentrepository->captureStripePayment($orderid, $chargeid, $planamount, $netamount, $plancredits, $netcredits, $validitytill, $planname, null, null, 'Paid');
                $subsrepository=$container->get('le.core.repository.subscription');
                $subsrepository->updateContactCredits($netcredits, $validitytill, $todaydate);
                $output->writeln('<info>'.'Plan Renewed Successfully'.'</info>');
                $output->writeln('<info>'.'Transaction ID:'.$chargeid.'</info>');
                $output->writeln('<info>'.'Amount($):'.$netamount.'</info>');
                $output->writeln('<info>'.'Contact Credits:'.$netcredits.'</info>');
                $billingmodel  = $container->get('mautic.model.factory')->getModel('subscription.billinginfo');
                $billingrepo   = $billingmodel->getRepository();
                $billingentity = $billingrepo->findAll();
                if (sizeof($billingentity) > 0) {
                    $billing = $billingentity[0]; //$model->getEntity(1);
                } else {
                    $billing = new Billing();
                }
                if ($billing->getAccountingemail() != '') {
                    $mailer       = $container->get('le.transactions.sendgrid_api');
                    $paymenthelper=$container->get('le.helper.payment');
                    $paymenthelper->sendPaymentNotification($payment, $billing, $mailer);
                }
            } else {
                $payment       =$paymentrepository->captureStripePayment($orderid, $chargeid, $planamount, $netamount, $plancredits, $netcredits, $validitytill, $planname, null, null, $status);
                $output->writeln('<error>'.'Plan renewed failed due to some technical issues.'.'</error>');
                $output->writeln('<error>'.'Failure Code:'.$failure_code.'</error>');
                $output->writeln('<error>'.'Failure Message:'.$failure_message.'</error>');
            }
        } else {
            $output->writeln('<error>'.'Plan renewed failed due to some technical issues.'.'</error>');
        }
    }

    protected function getProrataAmount($output, $start, $end, $amount)
    {
        $date1        = new \DateTime($start);
        $date2        = new \DateTime($end);
        $diff         = $date2->diff($date1)->format('%a');
        $diff         = $diff + 1;
        $output->writeln('<info>'.'Billing Days:'.$diff.'</info>');
        $prorataamount=$amount * ($diff / 31);

        return round($prorataamount);
    }
}
