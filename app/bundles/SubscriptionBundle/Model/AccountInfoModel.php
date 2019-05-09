<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SubscriptionBundle\Model;

use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\SubscriptionBundle\Entity\Account;

/**
 * Class AccountInfoModel.
 */
class AccountInfoModel extends FormModel
{
    /**
     * {@inheritdoc}
     *
     * @return \Mautic\SubscriptionBundle\Entity\AccountRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticSubscriptionBundle:Account');
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return null|Account
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            return new Account();
        }

        $entity = parent::getEntity($id);

        return $entity;
    }

    public function getCurrentUser()
    {
        return $this->userHelper->getUser();
    }

    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof Account) {
            throw new MethodNotAllowedHttpException(['Account'], 'Entity must be of class Account()');
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('accountinfo', $entity, $options);
    }

    /**
     * Get line chart data of submissions.
     *
     * @param char      $unit          {@link php.net/manual/en/function.date.php#refsect1-function.date-parameters}
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param string    $dateFormat
     * @param array     $filter
     * @param bool      $canViewOthers
     *
     * @return array
     */
    public function getAccountLineChartData(
        $unit,
        \DateTime $dateFrom,
        \DateTime $dateTo,
        $dateFormat = null
    ) {
        $chart   = new LineChart($unit, $dateFrom, $dateTo, $dateFormat);
        $query   = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo);
        $lq      = $query->prepareTimeDataQuery('leads', 'date_added', []);
        $esq     = $query->prepareTimeDataQuery('email_stats', 'date_sent', ['is_failed' => 0]);
        $eoq     = $query->prepareTimeDataQuery('email_stats', 'date_read', ['is_read' => 1]);
        $ebq     = $query->prepareTimeDataQuery('lead_donotcontact', 'date_added', ['reason' => 1]);
        $euq     = $query->prepareTimeDataQuery('lead_donotcontact', 'date_added', ['reason' => 2]);
        $ecq     = $query->prepareTimeDataQuery('page_hits', 'date_hit', []);
        $ecq->join('t', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = t.email_id');
        $fq               = $query->prepareTimeDataQuery('form_submissions', 'date_submitted', []);
        $aq               = $query->prepareTimeDataQuery('asset_downloads', 'date_download', []);
        $leaddata         = $query->loadAndBuildTimeData($lq);
        $emailsent        = $query->loadAndBuildTimeData($esq);
        $emailopen        = $query->loadAndBuildTimeData($eoq);
        $emailclick       = $query->loadAndBuildTimeData($ecq);
        $emailunsubscribe = $query->loadAndBuildTimeData($euq);
        $emailbounce      = $query->loadAndBuildTimeData($ebq);
        $formdata         = $query->loadAndBuildTimeData($fq);
        $assetdata        = $query->loadAndBuildTimeData($aq);
        $chart->setDataset($this->translator->trans('le.dashboard.lead.count'), $leaddata);
        $chart->setDataset($this->translator->trans('le.dashboard.email.sent.count'), $emailsent);
        $chart->setDataset($this->translator->trans('le.dashboard.email.opened.count'), $emailopen);
        $chart->setDataset($this->translator->trans('le.dashboard.email.click.count'), $emailclick);

        if ($this->security->isAdmin()) {
            $chart->setDataset($this->translator->trans('le.dashboard.email.bounced.count'), $emailunsubscribe);
            $chart->setDataset($this->translator->trans('le.dashboard.email.unsubscribed.count'), $emailbounce);
        }
        $chart->setDataset($this->translator->trans('le.dashboard.form.submits'), $formdata);
        $chart->setDataset($this->translator->trans('le.dashboard.asset.downloads'), $assetdata);

        return $chart->render();
    }

    public function getCustomEmailStats()
    {
        $usermodel  =$this->factory->getModel('user.user');
        $currentuser= $usermodel->getCurrentUserEntity();
        $this->getRepository()->setCurrentUser($currentuser);

        $emailStats                = [];
        $emailStats['sent']        = $this->getRepository()->getTotalSentCounts();
        $emailStats['uopen']       = $this->getRepository()->getTotalUniqueOpenCounts();
        $emailStats['topen']       = $this->getRepository()->getTotalOpenCounts();
        $emailStats['click']       = $this->getRepository()->getEmailClickCounts();
        $emailStats['unsubscribe'] = $this->getRepository()->getTotalUnsubscribedCounts();
        $emailStats['bounce']      = $this->getRepository()->getTotalBounceCounts();
        $emailStats['spam']        = $this->getRepository()->getTotalSpamCounts();

        return $emailStats;
    }

    public function getCustomLeadStats()
    {
        $leadStats                          = [];
        $leadStats['allleads']              = $this->getRepository()->getTotalAllLeads();
        $leadStats['activeleads']           = $this->getRepository()->getAllActiveLeads();
        $leadStats['activeengagedleads']    = $this->getRepository()->getLeadsByStatus(2);
        $leadStats['activenotengagedleads'] = $this->getRepository()->getLeadsByStatus(1);
        $leadStats['invalid']               = $this->getRepository()->getLeadsByStatus(3);
        $leadStats['complaint']             = $this->getRepository()->getLeadsByStatus(4);
        $leadStats['unsubscribed']          = $this->getRepository()->getLeadsByStatus(5);
        $leadStats['notconfirmed']          = $this->getRepository()->getLeadsByStatus(6);
        $leadStats['inactiveleads']         = $this->getRepository()->getAllInActiveLeads();
        $leadStats['recentadded']           = $this->getRepository()->getRecentlyAddedLeadsCount();
        $leadStats['recentactive']          = $this->getRepository()->getRecentActiveLeadCount();

        return $leadStats;
    }

    public function getOverAllStats()
    {
        $stats                   = [];
        $stats['goalsachived']   = $this->getRepository()->getGoalsAchived();
        $stats['activeforms']    = $this->getRepository()->getActiveForms();
        $stats['activeworkflow'] = $this->getRepository()->getActiveWorkflows();
        $stats['activeasset']    = $this->getRepository()->getActiveAssets();
        $stats['submissions']    = $this->getRepository()->getFormSubmissionCount();
        $stats['downloads']      = $this->getRepository()->getAssetDownloadCount();

        return $stats;
    }
}
