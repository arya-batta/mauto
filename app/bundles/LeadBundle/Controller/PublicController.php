<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Controller;

use Mautic\CoreBundle\Controller\FormController as CommonFormController;
use Mautic\LeadBundle\Event\LeadListOptInEvent;
use Mautic\LeadBundle\Event\ListOptInChangeEvent;
use Mautic\LeadBundle\LeadEvents;
use Symfony\Component\HttpFoundation\Response;

class PublicController extends CommonFormController
{
    use FrequencyRuleTrait;

    /**
     * @param $idHash
     *
     * @return Response
     */
    public function indexAction($idhash)
    {
        /** @var \Mautic\LeadBundle\Model\ListOptInModel $model */
        $model        = $this->getModel('lead.listoptin');
        $listleadrepo = $model->getListLeadRepository();
        /** @var \Mautic\LeadBundle\Entity\ListLeadOptIn $list */
        $list  = $listleadrepo->getEntity($idhash);
        if ($list == null) {
            return $this->notFound();
        }

        $translator = $this->get('translator');

        $list->setConfirmedLead(1);
        $list->setUnconfirmedLead(0);
        $list->setUnsubscribedLead(0);
        $listleadrepo->saveEntity($list);

        $viewParams['name']              = 'Unsubscribed';
        $viewParams['actionName']        = 'unsubscribe';
        $viewParams['subscriptiontitle'] = 'le.email.unsubscribe.title';
        $contentTemplate                 = 'MauticLeadBundle:ListOptIn:unsubscribe.html.php';

        return $this->render($contentTemplate, $viewParams);
    }

    public function listconfirmAction($idhash)
    {
        /** @var \Mautic\LeadBundle\Model\ListOptInModel $model */
        $model        = $this->getModel('lead.listoptin');
        $listleadrepo = $model->getListLeadRepository();
        /** @var \Mautic\LeadBundle\Entity\ListLeadOptIn $list */
        $list  = $listleadrepo->getEntity($idhash);
        if ($list == null) {
            return $this->notFound();
        }

        /** @var \Mautic\LeadBundle\Model\LeadModel $leadmodel */
        $leadmodel = $this->getModel('lead');
        /** @var \Mautic\LeadBundle\Entity\Lead Lead */
        $lead      = $leadmodel->getEntity($list->getLead()->getId());
        if ($lead == null) {
            return $this->notFound();
        }
        $translator = $this->get('translator');
        if (!$list->getConfirmedLead()) {
            $list->setConfirmedLead(1);
            $list->setUnconfirmedLead(0);
            $list->setUnsubscribedLead(0);
            $listleadrepo->saveEntity($list);

            if (($this->dispatcher->hasListeners(LeadEvents::LIST_OPT_IN_CHANGE))) {
                $event = new ListOptInChangeEvent($lead, $list->getList());
                //$listevent = new LeadListOptInEvent($list->getList() , false, $lead, $list->getId());
                $this->dispatcher->dispatch(LeadEvents::LIST_OPT_IN_CHANGE, $event);
                unset($event);

                $listevent = new LeadListOptInEvent($list->getList(), false, $lead, $list->getId());
                $this->dispatcher->dispatch(LeadEvents::LEAD_LIST_SENDTHANKYOU_EMAIL, $listevent);
                unset($listevent);
            }
        }

        $viewParams['name']              = 'Unsubscribed';
        $viewParams['actionName']        = 'unsubscribe';
        $viewParams['subscriptiontitle'] = 'le.email.unsubscribe.title';
        $viewParams['message']           = '';
        $viewParams['email']             = $lead->getEmail();
        $contentTemplate                 = 'MauticLeadBundle:ListOptIn:confirm.html.php';

        return $this->render($contentTemplate, $viewParams);
    }

    /**
     * @param $idHash
     *
     * @return Response
     */
    public function listsubscribeAction($idhash)
    {
        /** @var \Mautic\LeadBundle\Model\ListOptInModel $model */
        $model        = $this->getModel('lead.listoptin');
        $listleadrepo = $model->getListLeadRepository();
        /** @var \Mautic\LeadBundle\Entity\ListLeadOptIn $list */
        $list  = $listleadrepo->getEntity($idhash);
        if ($list == null) {
            return $this->notFound();
        }
        $translator = $this->get('translator');
        /** @var \Mautic\LeadBundle\Model\LeadModel $leadmodel */
        $leadmodel = $this->getModel('lead');
        /** @var \Mautic\LeadBundle\Entity\Lead Lead */
        $lead      = $leadmodel->getEntity($list->getLead()->getId());
        if ($lead == null) {
            return $this->notFound();
        }
        if (!$list->getConfirmedLead()) {
            $list->setConfirmedLead(1);
            $list->setUnconfirmedLead(0);
            $list->setUnsubscribedLead(0);
            $listleadrepo->saveEntity($list);
        }

        $actionRoute = $this->generateUrl('le_unsubscribe_list', ['idhash' => $idhash]);
        $viewParams  = [
            'name'        => 'UnSubscribe',
            'email'       => $lead->getEmail(),
            'lead'        => $lead,
            'message'     => '',
            'actionroute' => $actionRoute,
            'actionName'  => 'subscribe',
        ];
        $viewParams['content']           = '';
        $contentTemplate                 = 'MauticLeadBundle:ListOptIn:unsubscribe.html.php';

        return $this->render($contentTemplate, $viewParams);
    }

    /**
     * @param $idHash
     *
     * @return Response
     */
    public function listunsubscribeAction($idhash)
    {
        /** @var \Mautic\LeadBundle\Model\ListOptInModel $model */
        $model        = $this->getModel('lead.listoptin');
        $listleadrepo = $model->getListLeadRepository();
        /** @var \Mautic\LeadBundle\Entity\ListLeadOptIn $list */
        $list  = $listleadrepo->getEntity($idhash);
        if ($list == null) {
            return $this->notFound();
        }
        /** @var \Mautic\LeadBundle\Model\LeadModel $leadmodel */
        $leadmodel = $this->getModel('lead');
        /** @var \Mautic\LeadBundle\Entity\Lead Lead */
        $lead      = $leadmodel->getEntity($list->getLead()->getId());
        if ($lead == null) {
            return $this->notFound();
        }
        $translator = $this->get('translator');

        $list->setConfirmedLead(0);
        $list->setUnconfirmedLead(0);
        $list->setUnsubscribedLead(1);
        $listleadrepo->saveEntity($list);

        if (($this->dispatcher->hasListeners(LeadEvents::LEAD_LIST_SENDGOODBYE_EMAIL))) {
            $listevent = new LeadListOptInEvent($list->getList(), false, $lead, $list->getId());
            $this->dispatcher->dispatch(LeadEvents::LEAD_LIST_SENDGOODBYE_EMAIL, $listevent);
            unset($listevent);
        }

        $actionRoute = $this->generateUrl('le_resubscribe_list', ['idhash' => $idhash]);
        $message     = $translator->trans(
            'le.lead.list.optin.unsubscribed.success',
            [
                '%resubscribeUrl%' => '|URL|',
                '%email%'          => '|EMAIL|',
            ]
        );
        $message = str_replace(
            [
                '|URL|',
                '|EMAIL|',
            ],
            [
                $actionRoute,
                $lead->getEmail(),
            ],
            $message
        );
        $viewParams  = [
            'name'        => 'UnSubscribe',
            'email'       => $lead->getEmail(),
            'lead'        => $lead,
            'message'     => $message,
            'actionroute' => $actionRoute,
            'actionName'  => 'unsubscribe',
        ];
        $viewParams['subscriptiontitle'] = 'le.email.unsubscribe.title';
        $contentTemplate                 = 'MauticLeadBundle:ListOptIn:unsubscribe.html.php';

        return $this->render($contentTemplate, $viewParams);
    }

    /**
     * @param $idhash
     *
     * @return Response
     */
    public function listresubscribeAction($idhash)
    {
        /** @var \Mautic\LeadBundle\Model\ListOptInModel $model */
        $model        = $this->getModel('lead.listoptin');
        $listleadrepo = $model->getListLeadRepository();
        /** @var \Mautic\LeadBundle\Entity\ListLeadOptIn $list */
        $list  = $listleadrepo->getEntity($idhash);
        if ($list == null) {
            return $this->notFound();
        }
        /** @var \Mautic\LeadBundle\Model\LeadModel $leadmodel */
        $leadmodel = $this->getModel('lead');
        /** @var \Mautic\LeadBundle\Entity\Lead Lead */
        $lead      = $leadmodel->getEntity($list->getLead()->getId());
        if ($lead == null) {
            return $this->notFound();
        }
        $translator = $this->get('translator');

        $list->setConfirmedLead(1);
        $list->setUnconfirmedLead(0);
        $list->setUnsubscribedLead(0);
        $listleadrepo->saveEntity($list);

        $actionRoute = $this->generateUrl('le_unsubscribe_list', ['idhash' => $idhash]);
        $message     = $this->translator->trans(
            'le.lead.list.resubscribed.success',
            [
                '%unsubscribeUrl%' => '|URL|',
                '%email%'          => '|EMAIL|',
            ]
        );
        $message = str_replace(
            [
                '|URL|',
                '|EMAIL|',
            ],
            [
                $this->generateUrl('le_subscribe_list', ['idhash' => $idhash]),
                $lead->getEmail(),
            ],
            $message
        );
        $viewParams  = [
            'name'        => 'Subscrptions',
            'email'       => $lead->getEmail(),
            'lead'        => $lead,
            'message'     => $message,
            'actionroute' => $actionRoute,
            'actionName'  => 'resubscribe',
        ];
        $viewParams['subscriptiontitle'] = 'le.email.resubscribe.title';
        $contentTemplate                 = 'MauticLeadBundle:ListOptIn:unsubscribe.html.php';

        return $this->render($contentTemplate, $viewParams);
    }
}
