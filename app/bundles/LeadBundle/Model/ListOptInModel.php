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
use Mautic\CoreBundle\Helper\EmojiHelper;
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
    public function addLead($lead, $lists, $manuallyAdded = false, $dispatchEvent=true)
    {
        // $dateManipulated = new \DateTime();

        if (!$lead instanceof Lead) {
            $leadId = (is_array($lead) && isset($lead['id'])) ? $lead['id'] : $lead;
            $lead   = $this->em->getReference('MauticLeadBundle:Lead', $leadId);
        } else {
            //$leadId = $lead->getId();
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
            //$lists = [$lists->getId()];
        }

        if (!$lists instanceof LeadListOptIn) {
            if (!is_array($lists)) {
                $lists = [$lists];
            }
        }

        $persistLists   = [];
        $dispatchEvents = [];
        if (!$lists instanceof LeadListOptIn) {
            foreach ($lists as $listId) {
                if (!isset($this->leadChangeLists[$listId])) {
                    // List no longer exists in the DB so continue to the next
                    continue;
                }
                $listentity       = $this->getEntity($listId);
                $this->linkLeadListEntity($listentity, $lead, $manuallyAdded, $dispatchEvent);
                $dispatchEvents[] = $listId;
            }
        } else {
            $this->linkLeadListEntity($lists, $lead, $manuallyAdded, $dispatchEvent);
            $dispatchEvents[] = $lists->getId();
        }
        /* if (!empty($persistLists)) {
             $this->getRepository()->saveEntities($persistLists);
         } */
        if (!empty($dispatchEvents)) {
            foreach ($dispatchEvents as $listId) {
                if ($this->dispatcher->hasListeners(LeadEvents::LIST_OPT_IN_CHANGE)) {
                    $event = new ListOptInChangeEvent($lead, $this->leadChangeLists[$listId]);
                    $this->dispatcher->dispatch(LeadEvents::LIST_OPT_IN_CHANGE, $event);
                    unset($event);
                }
                if ($this->dispatcher->hasListeners(LeadEvents::LEAD_LIST_OPT_IN_ADD)) {
                    $event = new ListOptInChangeEvent($lead, $this->leadChangeLists[$listId]);
                    $this->dispatcher->dispatch(LeadEvents::LEAD_LIST_OPT_IN_ADD, $event);
                    unset($event);
                }
                if ($this->dispatcher->hasListeners(LeadEvents::LEAD_LIST_SEND_EMAIL) && $dispatchEvent) {
                    $listevent = new LeadListOptInEvent($this->leadChangeLists[$listId], false, $lead, $listId);
                    $this->dispatcher->dispatch(LeadEvents::LEAD_LIST_SEND_EMAIL, $listevent);
                    unset($listevent);
                }
            }
        }
        unset($lead, $lists, $dispatchEvents);
    }

    public function linkLeadListEntity(LeadListOptIn $list, $lead, $manuallyAdded, $dispatchEvent)
    {
        $dateManipulated  = new \DateTime();
        /** @var ListLeadOptIn $listLead */
        $listLead         = $this->getListLeadRepository()->getListEntityByid($lead->getId(), $list->getId());
        $confirmedLead    = $list->getListtype() ? 0 : 1;
        $unconfirmedLead  = $list->getListtype() ? 1 : 0;
        $isreschedule     = 0;
        if ($list->getResend()) {
            $isreschedule = 1;
        }
        if ($manuallyAdded && $list->getListtype()) {
            $confirmedLead   = 1;
            $unconfirmedLead = 0;
        }
        $unsubscribedLead = 0;
        if ($listLead != null) {
            $listLead->setManuallyRemoved(false);
            $listLead->setManuallyAdded($manuallyAdded);
            $listLead->setConfirmedLead($confirmedLead);
            $listLead->setUnconfirmedLead($unconfirmedLead);
            $listLead->setUnsubscribedLead($unsubscribedLead);
            $listLead->setIsrescheduled($isreschedule);
        } else {
            $listLead = new ListLeadOptIn();
            $listLead->setList($list);
            $listLead->setLead($lead);
            $listLead->setManuallyAdded($manuallyAdded);
            $listLead->setDateAdded($dateManipulated);
            $listLead->setConfirmedLead($confirmedLead);
            $listLead->setUnconfirmedLead($unconfirmedLead);
            $listLead->setUnsubscribedLead($unsubscribedLead);
            $listLead->setIsrescheduled($isreschedule);

            //  $persistLists[]   = $listLead;
        }
        $this->getRepository()->saveEntity($listLead);
        // Clear ListLead entities from Doctrine memory
        $this->em->clear('Mautic\LeadBundle\Entity\ListLeadOptIn');
        $listLead=null;
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
        if (!empty($deleteLists)) {
            foreach ($deleteLists as $listLead) {
                $listentity       = $this->getEntity($listLead->getList());
                if ($this->dispatcher->hasListeners(LeadEvents::REMOVE_LSIT_OPTIN)) {
                    $event = new ListOptInChangeEvent($lead, $listentity, false);
                    $this->dispatcher->dispatch(LeadEvents::REMOVE_LSIT_OPTIN, $event);
                    unset($event);
                }
            }
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
                //$content = str_replace('{{list_footer_text}}', $footertext, $content);
            }
            $unsubscribeurl = $this->buildUrl('le_subscribe_list', ['idhash' => $listlead->getId()]);
            $confirmurl     = $this->buildUrl('le_confirm_list', ['idhash' => $listlead->getId()]);

            $content = str_replace('%7B%7Bconfirmation_link%7D%7D', $confirmurl, $content);
            //$content = str_replace('{{list_unsubscribe_link}}', $unsubscribeurl, $content);
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
            //$divelement->appendChild($ptag1);

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

            if (false) {
                $br          = $doc->createElement('br');
                $brandfooter = $doc->createElement('div');
                $brandfooter->setAttribute('style', 'background-color:#ffffff;text-align:center;');
                $url  = 'http://anyfunnels.com/?utm-src=email-footer-link&utm-med='.$account->getDomainname();
                $atag = $doc->createElement('a');
                $atag->setAttribute('href', $url);
                $atag->setAttribute('target', '_blank');

                $imgtag = $doc->createElement('img');
                $icon   = 'https://anyfunnels.com/wp-content/uploads/leproduct/anyfunnels-footer2.png'; //$this->factory->get('templating.helper.assets')->getUrl('media/images/le_branding.png');
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
        $totalSegment =  [$this->translator->trans('le.form.display.color.blocks.blue'), 'mdi mdi-format-list-bulleted-type', $this->translator->trans('le.lead.list.optin.lists.all'),
            $this->getRepository()->getTotalListCount($viewOthers = $this->factory->get('mautic.security')->isGranted('lead:listoptin:viewother')),
        ];
        $activeSegment = [$this->translator->trans('le.form.display.color.blocks.green'), 'mdi mdi-format-list-bulleted-type', $this->translator->trans('le.lead.list.optin.lists.active'),
            $this->getRepository()->getTotalActiveListCount($viewOthers = $this->factory->get('mautic.security')->isGranted('lead:listoptin:viewother')),
        ];
        $inactiveSegment = [$this->translator->trans('le.form.display.color.blocks.red'), 'mdi mdi-format-list-bulleted-type', $this->translator->trans('le.lead.list.optin.lists.inactive'),
            $this->getRepository()->getTotalInactiveListCount($viewOthers = $this->factory->get('mautic.security')->isGranted('lead:listoptin:viewother')),
        ];

        $allBlockDetails[] = $totalSegment;
        $allBlockDetails[] = $activeSegment;
        $allBlockDetails[] = $inactiveSegment;

        return $allBlockDetails;
    }

    public function getListOptinByLead($leadId)
    {
        return $this->getListLeadRepository()->getListIDbyLeads($leadId);
    }

    public function scheduleListOptInEmail($listoptin, $lead, $listLead)
    {
        $customHtml = $this->replaceTokens($listoptin->getMessage(), $lead, $listLead, $listoptin);
        $this->sendEmailAction($lead, $listoptin, $customHtml);
    }

    public function sendEmailAction(Lead $lead, LeadListOptIn $listoptin, $bodyContent)
    {
        $emailModel    = $this->factory->getModel('email');
        $emailRepo     = $emailModel->getRepository();
        $leadEmail     = $lead->getEmail();
        $leadName      = $lead->getName();
        $mailer        = $this->factory->get('mautic.helper.mailer')->getMailer();

        // To lead
        $mailer->addTo($leadEmail, $leadName);

        // From user
        $user = $this->factory->get('mautic.helper.user')->getUser();

        $mailer->setFrom($listoptin->getFromaddress(), $listoptin->getFromname());

        // Set Content
        $mailer->setBody($bodyContent);
        $mailer->parsePlainText($bodyContent);

        // Set lead
        $leadFields       = $lead->getProfileFields();
        $mailer->setLead($leadFields);
        $mailer->setIdHash();

        // Ensure safe emoji for notification
        $subject = EmojiHelper::toHtml($listoptin->getSubject());
        $mailer->setSubject($subject);
        if ($mailer->send(true, false, false)) {
            if (!empty($email['templates'])) {
                $emailRepo->upCount($email['templates'], 'sent', 1, false);
            }
            $mailer->createEmailStat();
        }
    }

    public function getDefaultMailBodyContent()
    {
        $messageContent = "<div style='background-color:#f9f9f9;font-family:\"HelveticaNeue\",\"Helvetica Neue\",Helvetica,Arial,sans-serif;margin:0;padding:0'>

	<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"533px\">
		<tbody>
			<tr>
				<td style=\"text-align:center;padding:30px 0 20px 0\"><img alt=\"Logo\" class=\"CToWUd fr-fic fr-dii\" width=\"130\" height=\"40px\" src=\"https://anyfunnels.com/wp-content/uploads/leproduct/Your-Logo-Here.jpg\" style=\"padding: 0px;\"></td>
				<td style=\"text-align:center;padding:30px 0 20px 0\">
					<br>
				</td>
			</tr>
			<tr>
				<td>

					<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#ffffff;border-radius:4px;margin:0;padding:0\" width=\"100%\">
						<tbody>
							<tr>
								<td style='padding:30px;font-family:\"Open Sans\",\"HelveticaNeue\",\"Helvetica Neue\",Helvetica,Arial,sans-serif'>

									<p style=\"margin:0 0 16px;line-height:20px;color:#555555;font-size:14px\">Hi {leadfield=firstname},</p>

									<p style=\"margin:16px 0;line-height:20px;color:#555555;font-size:14px\">Kindly click on the below button to be a part of <strong>**Your Business Name**</strong>.</p>

									<p style=\"text-align:center;margin:32px 0\"><a href=\"{{confirmation_link}}\" style=\"display:inline-block;background:#00b27d;color:#ffffff;font-weight:bold;font-size:18px;text-decoration:none;padding:20px 30px;border-radius:4px\" target=\"_blank\">Confirm here</a></p>

									<p style=\"margin:0 0 16px;line-height:20px;color:#555555;font-size:14px\">Cheers,
										<br><a href=\"\"><strong>**Your Business Name**</strong></a>
										<a></a>
									</p>
									<a></a>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr></tr>
		</tbody>
	</table>
	<div class=\"yj6qo\">
		<br>
	</div>
	<div class=\"adL\">
		<br>
	</div>
</div>";

        return $messageContent;
    }
}
