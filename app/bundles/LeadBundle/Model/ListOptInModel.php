<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Model;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadListOptIn;
use Mautic\LeadBundle\Entity\ListLead;
use Mautic\LeadBundle\Entity\ListLeadOptIn;
use Mautic\LeadBundle\Entity\OperatorListTrait;
use Mautic\LeadBundle\Event\LeadListEvent;
use Mautic\LeadBundle\Event\LeadListOptInEvent;
use Mautic\LeadBundle\Event\ListOptInChangeEvent;
use Mautic\LeadBundle\LeadEvents;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class ListOptInModel
 * {@inheritdoc}
 */
class ListOptInModel extends FormModel
{
    use OperatorListTrait;

    /**
     * @var CoreParametersHelper
     */
    protected $coreParametersHelper;
    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    /**
     * ListModel constructor.
     *
     * @param CoreParametersHelper $coreParametersHelper
     * @param IntegrationHelper    $integrationHelper
     */
    public function __construct(CoreParametersHelper $coreParametersHelper, IntegrationHelper $integrationHelper)
    {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->integrationHelper    = $integrationHelper;
    }

    /**
     * Used by addLead and removeLead functions.
     *
     * @var array
     */
    private $leadChangeLists = [];

    /**
     * {@inheritdoc}
     *
     * @return \Mautic\LeadBundle\Entity\LeadListOptInRepository
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function getRepository()
    {
        /** @var \Mautic\LeadBundle\Entity\LeadListOptInRepository $repo */
        $repo = $this->em->getRepository('MauticLeadBundle:LeadListOptIn');

        return $repo;
    }

    /**
     * Returns the repository for the table that houses the leads associated with a list.
     *
     * @return \Mautic\LeadBundle\Entity\ListLeadOptInRepository
     */
    public function getListLeadRepository()
    {
        return $this->em->getRepository('MauticLeadBundle:ListLeadOptIn');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getPermissionBase()
    {
        return 'lead:listoptin';
    }

    /**
     * {@inheritdoc}
     *
     * @param      $entity
     * @param bool $unlock
     *
     * @return mixed|void
     */
    public function saveEntity($entity, $unlock = true)
    {
        $isNew = ($entity->getId()) ? false : true;

        //make sure alias is not already taken
        $repo      = $this->getRepository();
        $event     = $this->dispatchEvent('pre_save', $entity, $isNew);
        $repo->saveEntity($entity);
        $this->dispatchEvent('post_save', $entity, $isNew, $event);
    }

    /**
     * {@inheritdoc}
     *
     * @param       $entity
     * @param       $formFactory
     * @param null  $action
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof LeadListOptIn) {
            throw new MethodNotAllowedHttpException(['LeadListOptIn'], 'Entity must be of class LeadListOptIn()');
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('leadlistoptin', $entity, $options);
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return null|object
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            return new LeadListOptIn();
        }

        $entity = parent::getEntity($id);

        return $entity;
    }

    /**
     * {@inheritdoc}
     *
     * @param $action
     * @param $event
     * @param $entity
     * @param $isNew
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof LeadListOptIn) {
            throw new MethodNotAllowedHttpException(['LeadListOptIn'], 'Entity must be of class LeadListOptIn()');
        }

        switch ($action) {
            case 'pre_save':
                $name = LeadEvents::LIST_PRE_SAVE;
                break;
            case 'post_save':
                $name = LeadEvents::LIST_POST_SAVE;
                break;
            case 'pre_delete':
                $name = LeadEvents::LIST_PRE_DELETE;
                break;
            case 'post_delete':
                $name = LeadEvents::LIST_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new LeadListEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }
            $this->dispatcher->dispatch($name, $event);

            return $event;
        } else {
            return null;
        }
    }

    /**
     * Get a list of global lead lists.
     *
     * @return mixed
     */
    public function getListsOptIn()
    {
        $leadlistrepo=$this->em->getRepository('MauticLeadBundle:LeadListOptIn');
        $leadlistrepo->setCurrentUser($this->userHelper->getUser());
        $lists = $leadlistrepo->getListsOptIn();

        return $lists;
    }

    /**
     * Add lead to lists.
     *
     * @param array|Lead          $lead
     * @param array|LeadListOptIn $lists
     * @param bool                $manuallyAdded
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function addLead($lead, $lists, $manuallyAdded = false)
    {
        $dateManipulated = new \DateTime();

        if (!$lead instanceof Lead) {
            $leadId = (is_array($lead) && isset($lead['id'])) ? $lead['id'] : $lead;
            $lead   = $this->em->getReference('MauticLeadBundle:Lead', $leadId);
        } else {
            $leadId = $lead->getId();
        }

        if (!$lists instanceof LeadListOptIn) {
            //make sure they are ints
            $searchForLists = [];
            foreach ($lists as $k => &$l) {
                $l = (int) $l;
                if (!isset($this->leadChangeLists[$l])) {
                    $searchForLists[] = $l;
                }
            }

            if (!empty($searchForLists)) {
                $listEntities = $this->getEntities([
                    'filter' => [
                        'force' => [
                            [
                                'column' => 'l.id',
                                'expr'   => 'in',
                                'value'  => $searchForLists,
                            ],
                        ],
                    ],
                ]);

                foreach ($listEntities as $list) {
                    $this->leadChangeLists[$list->getId()] = $list;
                }
            }

            unset($listEntities, $searchForLists);
        } else {
            $this->leadChangeLists[$lists->getId()] = $lists;

            $lists = [$lists->getId()];
        }

        if (!is_array($lists)) {
            $lists = [$lists];
        }

        $persistLists   = [];
        $dispatchEvents = [];

        foreach ($lists as $listId) {
            if (!isset($this->leadChangeLists[$listId])) {
                // List no longer exists in the DB so continue to the next
                continue;
            }

            $listentity       = $this->getEntity($listId);
            $listLead         = $this->getListLeadRepository()->getListEntityByid($leadId, $listId);
            $confirmedLead    = $listentity->getListtype() == 'single' ? 1 : 0;
            $unconfirmedLead  = $listentity->getListtype() == 'single' ? 0 : 1;
            $unsubscribedLead = 0;
            if ($listLead != null) {
                $listLead->setManuallyRemoved(false);
                $listLead->setManuallyAdded($manuallyAdded);
                $listLead->setConfirmedLead($confirmedLead);
                $listLead->setUnconfirmedLead($unconfirmedLead);
                $listLead->setUnsubscribedLead($unsubscribedLead);

                $dispatchEvents[] = $listId;
            } else {
                $listLead = new ListLeadOptIn();
                $listLead->setList($this->leadChangeLists[$listId]);
                $listLead->setLead($lead);
                $listLead->setManuallyAdded($manuallyAdded);
                $listLead->setDateAdded($dateManipulated);
                $listLead->setConfirmedLead($confirmedLead);
                $listLead->setUnconfirmedLead($unconfirmedLead);
                $listLead->setUnsubscribedLead($unsubscribedLead);

                //  $persistLists[]   = $listLead;
                $dispatchEvents[] = $listId;
                $this->getRepository()->saveEntity($listLead);
            }
        }

        /* if (!empty($persistLists)) {
             $this->getRepository()->saveEntities($persistLists);
         } */

        // Clear ListLead entities from Doctrine memory
        $this->em->clear('Mautic\LeadBundle\Entity\ListLeadOptIn');

        if (!empty($dispatchEvents)) {
            foreach ($dispatchEvents as $listId) {
                $event = new ListOptInChangeEvent($lead, $this->leadChangeLists[$listId]);
                $this->dispatcher->dispatch(LeadEvents::LIST_OPT_IN_CHANGE, $event);
                $listevent = new LeadListOptInEvent($this->leadChangeLists[$listId], false, $lead, $listId);
                $this->dispatcher->dispatch(LeadEvents::LEAD_LIST_SEND_EMAIL, $listevent);
                unset($event);
                unset($listevent);
            }
        }

        unset($lead, $lists);
    }

    /**
     * Remove a lead from lists.
     *
     * @param      $lead
     * @param      $lists
     * @param bool $manuallyRemoved
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function removeLead($lead, $lists, $manuallyRemoved = false)
    {
        if (!$lead instanceof Lead) {
            $leadId = (is_array($lead) && isset($lead['id'])) ? $lead['id'] : $lead;
            $lead   = $this->em->getReference('MauticLeadBundle:Lead', $leadId);
        } else {
            $leadId = $lead->getId();
        }

        if (!$lists instanceof LeadListOptIn) {
            //make sure they are ints
            $searchForLists = [];
            foreach ($lists as $k => &$l) {
                $l = (int) $l;
                if (!isset($this->leadChangeLists[$l])) {
                    $searchForLists[] = $l;
                }
            }

            if (!empty($searchForLists)) {
                $listEntities = $this->getEntities([
                    'filter' => [
                        'force' => [
                            [
                                'column' => 'l.id',
                                'expr'   => 'in',
                                'value'  => $searchForLists,
                            ],
                        ],
                    ],
                ]);

                foreach ($listEntities as $list) {
                    $this->leadChangeLists[$list->getId()] = $list;
                }
            }

            unset($listEntities, $searchForLists);
        } else {
            $this->leadChangeLists[$lists->getId()] = $lists;

            $lists = [$lists->getId()];
        }

        if (!is_array($lists)) {
            $lists = [$lists];
        }

        $persistLists   = [];
        $deleteLists    = [];

        foreach ($lists as $listId) {
            if (!isset($this->leadChangeLists[$listId])) {
                // List no longer exists in the DB so continue to the next
                continue;
            }

            $listLead = $this->getListLeadRepository()->getListEntityByid($leadId, $listId);

            if ($listLead == null) {
                // Lead is not part of this list
                continue;
            }

            if (($manuallyRemoved && $listLead->wasManuallyAdded()) || (!$manuallyRemoved && !$listLead->wasManuallyAdded())) {
                //lead was manually added and now manually removed or was not manually added and now being removed
                $deleteLists[]    = $listLead;
            } elseif ($manuallyRemoved && !$listLead->wasManuallyAdded()) {
                $listLead->setManuallyRemoved(true);

                $persistLists[]   = $listLead;
            }

            unset($listLead);
        }

        if (!empty($persistLists)) {
            $this->getRepository()->saveEntities($persistLists);
        }

        if (!empty($deleteLists)) {
            $this->getRepository()->deleteEntities($deleteLists);
        }

        // Clear ListLead entities from Doctrine memory
        $this->em->clear('Mautic\LeadBundle\Entity\ListLeadOptIn');

        unset($lead, $deleteLists, $persistLists, $lists);
    }

    public function replaceTokens($content, $lead, ListLeadOptIn $listlead, LeadListOptIn $list)
    {
        if (!empty($content)) {
            if ((strpos($content, '{{list_footer_text}}') === false)) {
                $bodycontent = $this->alterEmailBodyContent($content);
                $content     = $bodycontent;
                $footertext  = $list->getFooterText();
                if ($footertext == '' || $footertext == null) {
                    $footertext = $this->translator->trans('le.lead.list.optin.default.footer_text');
                }
                $content = str_replace('{{list_footer_text}}', $footertext, $content);
            }
            $unsubscribeurl = $this->buildUrl('le_subscribe_list', ['idhash' => $listlead->getId()]);
            $confirmurl     = $this->buildUrl('le_confirm_list', ['idhash' => $listlead->getId()]);

            $content = str_replace('%7B%7Bconfirmation_link%7D%7D', $confirmurl, $content);
            $content = str_replace('{{list_unsubscribe_link}}', $unsubscribeurl, $content);
        }

        return $content;
    }

    /**
     * Alter Elastic Email Body Content to hide their own subscription url and account address.
     *
     * @param \Swift_Message $bodyContent
     */
    public function alterEmailBodyContent($bodyContent)
    {
        $doc                      = new \DOMDocument();
        $doc->strictErrorChecking = false;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$bodyContent);
        // Get body tag.
        $body = $doc->getElementsByTagName('body');
        if ($body and $body->length > 0) {
            $body = $body->item(0);
            //create the div element to append to body element
            $divelement = $doc->createElement('div');
            $divelement->setAttribute('style', 'margin-top:30px;background-color:#ffffff;border-top:1px solid #d0d0d0;font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-weight: normal;');
            $ptag1      = $doc->createElement('span', '{{list_footer_text}}');
            $divelement->setAttribute('class', 'list_footerText');
            $divelement->appendChild($ptag1);

            //actually append the element
            $body->appendChild($divelement);

            $accountmodel  = $this->factory->getModel('subscription.accountinfo');
            $accrepo       = $accountmodel->getRepository();
            $accountentity = $accrepo->findAll();
            if (sizeof($accountentity) > 0) {
                $account = $accountentity[0]; //$model->getEntity(1);
            } else {
                $account = new Account();
            }

            if ($account->getNeedpoweredby()) {
                $br          = $doc->createElement('br');
                $brandfooter = $doc->createElement('div');
                $brandfooter->setAttribute('style', 'background-color:#ffffff;text-align:center;');
                $url  = 'http://anyfunnels.com/?utm-src=email-footer-link&utm-med='.$account->getDomainname();
                $atag = $doc->createElement('a');
                $atag->setAttribute('href', $url);
                $atag->setAttribute('target', '_blank');

                $imgtag = $doc->createElement('img');
                $icon   = 'http://anyfunnels.com/wp-content/uploads/leproduct/email-footer.png'; //$this->factory->get('templating.helper.assets')->getUrl('media/images/le_branding.png');
                $imgtag->setAttribute('src', $icon);
                $imgtag->setAttribute('style', 'height:35px;width:160px;margin-top:10px;margin-bottom:5px;');
                $imgtag->setAttribute('title', 'Free Marketing Automation Software');
                $atag->appendChild($imgtag);
                $brandfooter->appendChild($atag);
                $body->appendChild($br);
                $body->appendChild($brandfooter);
                $content = $doc->saveHTML();
            }
            $bodyContent = $doc->saveHTML();
        }
        libxml_clear_errors();

        return $bodyContent;
    }

    public function getListOptinsBlocks()
    {
        $totalSegment =  [$this->translator->trans('le.form.display.color.blocks.blue'), 'fa fa-list-ul', $this->translator->trans('le.lead.list.optin.lists.all'),
            $this->getRepository()->getTotalListCount($viewOthers = $this->factory->get('mautic.security')->isGranted('lead:listoptin:viewother')),
        ];
        $activeSegment = [$this->translator->trans('le.form.display.color.blocks.green'), 'fa fa-list-ul', $this->translator->trans('le.lead.list.optin.lists.active'),
            $this->getRepository()->getTotalActiveListCount($viewOthers = $this->factory->get('mautic.security')->isGranted('lead:listoptin:viewother')),
        ];
        $inactiveSegment = [$this->translator->trans('le.form.display.color.blocks.red'), 'fa fa-list-ul', $this->translator->trans('le.lead.list.optin.lists.inactive'),
            $this->getRepository()->getTotalInactiveListCount($viewOthers = $this->factory->get('mautic.security')->isGranted('lead:listoptin:viewother')),
        ];

        $allBlockDetails[] = $totalSegment;
        $allBlockDetails[] = $activeSegment;
        $allBlockDetails[] = $inactiveSegment;

        return $allBlockDetails;
    }
}
