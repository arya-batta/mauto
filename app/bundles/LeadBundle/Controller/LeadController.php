<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Mautic\CoreBundle\Helper\EmojiHelper;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\CoreBundle\Model\IteratorExportDataModel;
use Mautic\LeadBundle\DataObject\LeadManipulator;
use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadNoteRepository;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Model\NoteModel;
use Mautic\SubscriptionBundle\Entity\KYC;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LeadController extends FormController
{
    use LeadDetailsTrait, FrequencyRuleTrait;

    /**
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = 1)
    {
        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'lead:leads:viewown',
                'lead:leads:viewother',
                'lead:leads:create',
                'lead:leads:editown',
                'lead:leads:editother',
                'lead:leads:deleteown',
                'lead:leads:deleteother',
                'lead:imports:view',
                'lead:imports:create',
            ],
            'RETURN_ARRAY'
        );

        $videoarg       = $this->request->get('login');
        $loginsession   = $this->get('session');
        $loginarg       = $loginsession->get('isLogin');
        $dbhost         = $this->coreParametersHelper->getParameter('le_db_host');
        $showsetup      = false;
        $billformview   = '';
        $accformview    = '';
        $userformview   = '';
        $videoURL       = '';
        $showvideo      = false;
        $kycview        = $this->get('mautic.helper.licenseinfo')->getFirstTimeSetup($dbhost, $loginarg);

        $ismobile = InputHelper::isMobile();
        if (sizeof($kycview) > 0) {
            $showsetup      = false;
            $billformview   = $kycview[0];
            $accformview    = $kycview[1];
            $userformview   = $kycview[2];
            $videoURL       = '';
            $showvideo      = false;
            $billing        = $kycview[3];
            $account        = $kycview[4];
            $userEntity     = $kycview[5];
            /** @var \Mautic\SubscriptionBundle\Model\KYCModel $kycmodel */
            $kycmodel         = $this->getModel('subscription.kycinfo');
            $kycrepo          = $kycmodel->getRepository();
            $kycentity        = $kycrepo->findAll();
            if (sizeof($kycentity) > 0) {
                $kyc = $kycentity[0]; //$model->getEntity(1);
            } else {
                $kyc = new KYC();
            }
            $step = '';
            if ($userEntity->getFirstName() == '' || $userEntity->getMobile() == '') {
                $step = 'flname';
            } elseif ($account->getAccountname() == '' || $kyc->getIndustry() == '' || $kyc->getPrevioussoftware() == '' || $account->getWebsite() == '') {
                $step = 'aboutyourbusiness';
            } elseif ($billing->getCompanyaddress() == '' || $billing->getCity() == '') {
                $step = 'addressinfo';
            }
            if ($step != '') {
                //return $this->delegateRedirect($this->generateUrl('le_welcome_action', ['step' => $step]));
            }
        } else {
            $loginsession->set('isLogin', false);
        }

        if (!$permissions['lead:leads:viewown'] && !$permissions['lead:leads:viewother']) {
            return $this->accessDenied();
        }

        if ($this->request->getMethod() == 'POST') {
            $this->setListFilters();
        }

        /** @var \Mautic\LeadBundle\Model\LeadModel $model */
        $model   = $this->getModel('lead');
        $session = $this->get('session');
        //set limits
        $limit = $session->get('mautic.lead.limit', $this->get('mautic.helper.core_parameters')->getParameter('default_pagelimit'));
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $this->request->get('search', $session->get('mautic.lead.filter', ''));
        $session->set('mautic.lead.filter', $search);

        //do some default filtering
        $orderBy    = $session->get('mautic.lead.orderby', 'l.last_active');
        $orderByDir = $session->get('mautic.lead.orderbydir', 'DESC');

        $filter      = ['string' => $search, 'force' => ''];
        $translator  = $this->get('translator');
        $anonymous   = $translator->trans('le.lead.lead.searchcommand.isanonymous');
        $listCommand = $translator->trans('le.lead.lead.searchcommand.list');
        $mine        = $translator->trans('mautic.core.searchcommand.ismine');
        $indexMode   = $this->request->get('view', $session->get('mautic.lead.indexmode', 'list'));
        $listFilters = [
            'filters' => [
                'placeholder' => $this->get('translator')->trans('le.lead.lead.filter.placeholder'),
                'multiple'    => true,
            ],
        ];

        $listFilters['filters']['groups'] = [];

        $session->set('mautic.lead.indexmode', $indexMode);

        $anonymousShowing = false;
        /*if ($indexMode != 'list' || ($indexMode == 'list' && strpos($search, $anonymous) === false)) {
            //remove anonymous leads unless requested to prevent clutter
            $filter['force'] .= " !$anonymous";
        } elseif (strpos($search, $anonymous) !== false && strpos($search, '!'.$anonymous) === false) {
            $anonymousShowing = true;
        }*/
        $values         = [];
        $currentFilters = $session->get('mautic.lead.list_filters', []);
        $updatedFilters = $this->request->get('filters', false);
        $ignoreListJoin = true;

        if ($updatedFilters) {
            // Filters have been updated

            // Parse the selected values
            $newFilters     = [];
            $updatedFilters = json_decode($updatedFilters, true);

            if ($updatedFilters) {
                foreach ($updatedFilters as $updatedFilter) {
                    list($clmn, $fltr) = explode(':', $updatedFilter);

                    $newFilters[$clmn][] = $fltr;
                }

                $currentFilters = $newFilters;
            } else {
                $currentFilters = [];
            }
        }
        $session->set('mautic.lead.list_filters', $currentFilters);

        if (!empty($currentFilters)) {
            $listIds      = [];
            $listOptinIds = [];
            $TagIds       = [];
            foreach ($currentFilters as $type => $typeFilters) {
                switch ($type) {
                    case 'list':
                        $key = 'lists';
                        break;
                    case 'category':
                        $key = 'categories';
                        break;
                    case 'listoptin':
                        $key = 'listoptins';
                        break;
                    case 'tag':
                        $key = 'tags';
                        break;
                }
                $listFilters['filters']['groups']['mautic.core.filter.'.$key]['values'] = $typeFilters;

                foreach ($typeFilters as $fltr) {
                    if ($type == 'list') {
                        $listIds[] = (int) $fltr;
                    } elseif ($type == 'listoptin') {
                        $listOptinIds[] = (int) $fltr;
                    } elseif ($type == 'tag') {
                        $TagIds[] = (int) $fltr;
                    }
                }
            }

            if (!empty($listIds)) {
                $listmodel       = $this->getModel('lead.list');
                $leadlist_search = '';
                for ($lid = 0; $lid < sizeof($listIds); ++$lid) {
                    $leadlist = $listmodel->getEntity($listIds[$lid]);
                    $values[] = $listIds[$lid];
                    if ($lid == 0 && $filter['string'] == '') {
                        $leadlist_search = 'segment:';
                    } else {
                        $leadlist_search .= ' or segment:';
                    }
                    if ($leadlist != null) {
                        $leadlist_search .= $leadlist->getAlias();
                    }
                }
                $filter['string'] .= $leadlist_search;
                // $filter['string'] = "segment:sadmin-segment OR segment:test-segement1";
            }
            if (!empty($listOptinIds)) {
                $listoptinmodel       = $this->getModel('lead.listoptin');
                $leadlistoptin_search = '';
                for ($lid = 0; $lid < sizeof($listOptinIds); ++$lid) {
                    $leadlistoptin = $listoptinmodel->getEntity($listOptinIds[$lid]);
                    if ($lid == 0 && $filter['string'] == '') {
                        $leadlistoptin_search = 'list:';
                    } else {
                        $leadlistoptin_search .= ' or list:';
                    }
                    if ($leadlistoptin != null) {
                        $leadlistoptin_search .= $leadlistoptin->getId();
                    }
                }
                $filter['string'] .= $leadlistoptin_search;
                // $filter['string'] = "segment:sadmin-segment OR segment:test-segement1";
            }
            if (!empty($TagIds)) {
                $tagmodel         = $this->getModel('lead.tag');
                $tagssearchString = '';
                for ($tid = 0; $tid < sizeof($TagIds); ++$tid) {
                    $tagEntity = $tagmodel->getEntity($TagIds[$tid]);
                    if ($tid == 0 && $filter['string'] == '') {
                        $tagssearchString = 'tag:';
                    } else {
                        $tagssearchString .= ' or tag:';
                    }
                    if ($tagEntity != null) {
                        $tagssearchString .= $tagEntity->getId();
                    }
                }

                $filter['string'] .= $tagssearchString;
                // $filter['string'] = "segment:sadmin-segment OR segment:test-segement1";
            }
        }

        if (!$permissions['lead:leads:viewother'] && $ignoreListJoin) {
            $filter['force'] .= " $mine";
        }
        $results = $model->getEntities([
            'start'          => $start,
            'limit'          => $limit,
            'filter'         => $filter,
            'orderBy'        => $orderBy,
            'orderByDir'     => $orderByDir,
            'withTotalCount' => true,
            'ignoreListJoin' => $ignoreListJoin,
        ]);

        $count = $results['count'];
        unset($results['count']);

        $leads = $results['results'];
        unset($results);

        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            if ($count === 1) {
                $lastPage = 1;
            } else {
                $lastPage = (ceil($count / $limit)) ?: 1;
            }
            $session->set('mautic.lead.page', $lastPage);
            $returnUrl = $this->generateUrl('le_contact_index', ['page' => $lastPage]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $lastPage],
                    'contentTemplate' => 'MauticLeadBundle:Lead:index',
                    'passthroughVars' => [
                        'activeLink'    => '#le_contact_index',
                        'leContent'     => 'lead',
                    ],
                ]
            );
        }

        //set what page currently on so that we can return here after form submission/cancellation
        $session->set('mautic.lead.page', $page);

        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        $listArgs = [];
        if (!$this->get('mautic.security')->isGranted('lead:lists:viewother')) {
            $listArgs['filter']['force'] = " $mine";
        }

        $lists = $this->getModel('lead.list')->getUserLists();

        $listFilters['filters']['groups']['mautic.core.filter.lists'] = [
            'options' => $lists,
            'prefix'  => 'list',
            'values'  => $values,
        ];
        $listFilters['filters']['groups']['le.core.filter.listoptin'] = [
            'options' => $this->getModel('lead.listoptin')->getListsOptIn(),
            'prefix'  => 'listoptin',
            'values'  => (empty($currentFilters) || !isset($currentFilters['listoptin'])) ? [] : array_values($currentFilters['listoptin']),
        ];
        $listFilters['filters']['groups']['le.core.filter.tags'] = [
            'options' => $this->getModel('lead.tag')->getTagsList(),
            'prefix'  => 'tag',
            'values'  => (empty($currentFilters) || !isset($currentFilters['tag'])) ? [] : array_values($currentFilters['tag']),
        ];
        //check to see if in a single list
        $inSingleList = (substr_count($search, "$listCommand:") === 1) ? true : false;
        $list         = [];
        if ($inSingleList) {
            preg_match("/$listCommand:(.*?)(?=\s|$)/", $search, $matches);

            if (!empty($matches[1])) {
                $alias = $matches[1];
                foreach ($lists as $l) {
                    if ($alias === $l['alias']) {
                        $list = $l;
                        break;
                    }
                }
            }
        }

        // Get the max ID of the latest lead added
        $maxLeadId         = $model->getRepository()->getMaxLeadId();
        $activeLeads       = $model->getRepository()->getActiveLeadCount($permissions['lead:leads:viewother']);
        $recentlyAdded     = $model->getRepository()->getRecentlyAddedLeadsCount($permissions['lead:leads:viewother']);
        $doNotContactLeads = $model->getRepository()->getDoNotContactLeadsCount($permissions['lead:leads:viewother']);
        $totalLeadsCount   = $model->getRepository()->getTotalLeadsCount($permissions['lead:leads:viewother']);
        // We need the EmailRepository to check if a lead is flagged as do not contact
        /** @var \Mautic\EmailBundle\Entity\EmailRepository $emailRepo */
        $emailRepo = $this->getModel('email')->getRepository();

        return $this->delegateView(
            [
                'viewParameters' => [
                    'searchValue'          => $search,
                    'filters'              => $listFilters,
                    'items'                => $leads,
                    'page'                 => $page,
                    'totalItems'           => $count,
                    'limit'                => $limit,
                    'permissions'          => $permissions,
                    'tmpl'                 => $tmpl,
                    'indexMode'            => $indexMode,
                    'lists'                => $lists,
                    'currentList'          => $list,
                    'security'             => $this->get('mautic.security'),
                    'inSingleList'         => $inSingleList,
                    'noContactList'        => $emailRepo->getDoNotEmailList(array_keys($leads)),
                    'maxLeadId'            => $maxLeadId,
                    'anonymousShowing'     => $anonymousShowing,
                    'showvideo'            => $showvideo,
                    'videoURL'             => $videoURL,
                    'showsetup'            => $showsetup,
                    'billingform'          => $billformview,
                    'accountform'          => $accformview,
                    'userform'             => $userformview,
                    'isMobile'             => $ismobile,
                    'recentlyAdded'        => $recentlyAdded,
                    'donotContact'         => $doNotContactLeads,
                    'activeLeads'          => $activeLeads,
                    'totalLeadsCount'      => $totalLeadsCount,
                    'isEmailSearch'        => $this->isEmailStatSearch($search),
                ],
                'contentTemplate' => "MauticLeadBundle:Lead:{$indexMode}.html.php",
                'passthroughVars' => [
                    'activeLink'    => '#le_contact_index',
                    'leContent'     => 'lead',
                    'route'         => $this->generateUrl('le_contact_index', ['page' => $page]),
                ],
            ]
        );
    }

    /**
     * @return JsonResponse|Response
     */
//    public function quickAddAction()
//    {
//        /** @var \Mautic\LeadBundle\Model\LeadModel $model */
//        $model = $this->getModel('lead.lead');
//
//        // Get the quick add form
//        $action = $this->generateUrl('le_contact_action', ['objectAction' => 'new', 'qf' => 1]);
//
//        $fields =$this->getQuickAddFields();
//
//        $quickForm = $model->createForm($model->getEntity(), $this->get('form.factory'), $action, ['fields' => $fields, 'isShortForm' => true]);
//
//        //set the default owner to the currently logged in user
//        $currentUser = $this->get('security.context')->getToken()->getUser();
//        $quickForm->get('owner')->setData($currentUser);
//
//        return $this->delegateView(
//            [
//                'viewParameters' => [
//                    'form' => $quickForm->createView(),
//                ],
//                'contentTemplate' => 'MauticLeadBundle:Lead:quickadd.html.php',
//                'passthroughVars' => [
//                    'activeLink'    => '#le_contact_index',
//                    'leContent' => 'lead',
//                    'route'         => false,
//                ],
//            ]
//        );
//    }
    public function getQuickAddFields()
    {
        return  $this->getModel('lead.field')->getEntities(
        [
            'filter' => [
                'force' => [
                    [
                        'column' => 'f.isPublished',
                        'expr'   => 'eq',
                        'value'  => true,
                    ],
                    [
                        'column' => 'f.isShortVisible',
                        'expr'   => 'eq',
                        'value'  => true,
                    ],
                    [
                        'column' => 'f.object',
                        'expr'   => 'like',
                        'value'  => 'lead',
                    ],
                ],
            ],
            'hydration_mode' => 'HYDRATE_ARRAY',
        ]
    );
    }

    /**
     * Loads a specific lead into the detailed panel.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($objectId)
    {
        //dump($objectId);
        /** @var \Mautic\LeadBundle\Model\LeadModel $model */
        $model = $this->getModel('lead.lead');
        if ($this->get('mautic.helper.licenseinfo')->redirectToCardinfo()) {
            return $this->delegateRedirect($this->generateUrl('le_accountinfo_action', ['objectAction' => 'cardinfo']));
        }
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->delegateRedirect($this->generateUrl('le_pricing_index'));
        }
        /** @var \Mautic\LeadBundle\Entity\Lead $lead */
        $lead = $model->getEntity($objectId);

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'lead:leads:viewown',
                'lead:leads:viewother',
                'lead:leads:create',
                'lead:leads:editown',
                'lead:leads:editother',
                'lead:leads:deleteown',
                'lead:leads:deleteother',
            ],
            'RETURN_ARRAY'
        );

        if ($lead === null) {
            //get the page we came from
            $page = $this->get('session')->get('mautic.lead.page', 1);

            //set the return URL
            $returnUrl = $this->generateUrl('le_contact_index', ['page' => $page]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'MauticLeadBundle:Lead:index',
                    'passthroughVars' => [
                        'activeLink'    => '#le_contact_index',
                        'leContent'     => 'contact',
                    ],
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'le.lead.lead.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ]
            );
        }

        if (!$this->get('mautic.security')->hasEntityAccess(
            'lead:leads:viewown',
            'lead:leads:viewother',
            $lead->getPermissionUser()
        )
        ) {
            return $this->accessDenied();
        }

        $fields            = $lead->getFields();
        $integrationHelper = $this->get('mautic.helper.integration');
        $socialProfiles    = (array) $integrationHelper->getUserProfiles($lead, $fields);
        $socialProfileUrls = $integrationHelper->getSocialProfileUrlRegex(false);
        /* @var \Mautic\LeadBundle\Model\CompanyModel $model */

        $companyModel = $this->getModel('lead.company');

        $companiesRepo = $companyModel->getRepository();
        $companies     = $companiesRepo->getCompaniesByLeadId($objectId);
        // Set the social profile templates
        if ($socialProfiles) {
            foreach ($socialProfiles as $integration => &$details) {
                if ($integrationObject = $integrationHelper->getIntegrationObject($integration)) {
                    if ($template = $integrationObject->getSocialProfileTemplate()) {
                        $details['social_profile_template'] = $template;
                    }
                }

                if (!isset($details['social_profile_template'])) {
                    // No profile template found
                    unset($socialProfiles[$integration]);
                }
            }
        }

        /** @var NoteModel $noteModel */
        $noteModel = $this->getModel('lead.note');
        /** @var LeadNoteRepository $repo */
        $repo  = $noteModel->getRepository();
        $repo->setCurrentUser($noteModel->getCurrentUser());

        // We need the EmailRepository to check if a lead is flagged  as do not contact
        /** @var \Mautic\EmailBundle\Entity\EmailRepository $emailRepo */
        $emailRepo       = $this->getModel('email')->getRepository();
        $integrationRepo = $this->get('doctrine.orm.entity_manager')->getRepository('MauticPluginBundle:IntegrationEntity');
        $pageHitDetails  = $this->getPageHitsDetails($lead);
        $listRepository  = $this->getModel('lead.List')->getListLeadRepository();
        $segments        = $listRepository->getSegmentIDbyLeads($lead->getId());

        $segmentName    = [];
        foreach ($segments as $segment) {
            $segmentName[] = $listRepository->getSegmentNameByID($segment['leadlist_id']);
        }

        $listoptinRepository  = $this->getModel('lead.listoptin')->getListLeadRepository();
        $lists                = $listoptinRepository->getListIDbyLeads($lead->getId());

        $listName    = [];
        foreach ($lists as $list) {
            $listName[] = $listoptinRepository->getlistnameByID($list['leadlist_id']);
        }
        $dateRangeValues = $this->request->get('daterange', []);
        $action          = $this->generateUrl(
            'le_contact_action',
            [
                'objectAction' => 'view',
                'objectId'     => $lead->getId(),
            ]
        );
        $dateRangeForm   = $this->get('form.factory')->create('daterange', $dateRangeValues, ['action' => $action]);
        $engagements     = $this->getEngagementData($lead,
            new \DateTime($dateRangeForm->get('date_from')->getData()),
            new \DateTime($dateRangeForm->get('date_to')->getData()));

        return $this->delegateView(
            [
                'viewParameters' => [
                    'lead'              => $lead,
                    'avatarPanelState'  => $this->request->cookies->get('mautic_lead_avatar_panel', 'expanded'),
                    'fields'            => $fields,
                    'companies'         => $companies,
                    'socialProfiles'    => $socialProfiles,
                    'socialProfileUrls' => $socialProfileUrls,
                    'places'            => $this->getPlaces($lead),
                    'permissions'       => $permissions,
                    'events'            => $this->getEngagements($lead),
                    'pageHitDetails'    => $pageHitDetails,
                    'upcomingEvents'    => $this->getScheduledCampaignEvents($lead),
                    'engagementData'    => $engagements,
                    'noteCount'         => $this->getModel('lead.note')->getNoteCount($lead, true),
                    'integrations'      => $integrationRepo->getIntegrationEntityByLead($lead->getId()),
                    'auditlog'          => $this->getAuditlogs($lead),
                    'doNotContact'      => $emailRepo->checkDoNotEmail($fields['core']['email']['value']),
                    'leadNotes'         => $this->forward(
                        'MauticLeadBundle:Note:index',
                        [
                            'leadId'     => $lead->getId(),
                            'ignoreAjax' => 1,
                        ]
                    )->getContent(),
                    'security'         => $this->get('mautic.security'),
                    'segmentName'      => $segmentName,
                    'listName'         => $listName,
                    'dateRangeForm'    => $dateRangeForm->createView(),
                ],
                'contentTemplate' => 'MauticLeadBundle:Lead:lead.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_contact_index',
                    'leContent'     => 'lead',
                    'route'         => $this->generateUrl(
                        'le_contact_action',
                        [
                            'objectAction' => 'view',
                            'objectId'     => $lead->getId(),
                        ]
                    ),
                ],
            ]
        );
    }

    public function getPageHitsDetails($lead)
    {
        $leadRepo       = $this->getModel('lead')->getRepository();
        $engagement     = $this->getEngagements($lead);
        $dataValues     = '';
        foreach ($engagement['events'] as $counter => $event) {
            if ($event['event'] == 'page.hit') {
                $contactId  = $event['contactId'];
                $dataValues = $leadRepo->getPageHitDetails($contactId);
            }
        }

        return $dataValues;
    }

    /**
     * Generates new form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        /** @var LeadModel $model */
        $model = $this->getModel('lead.lead');
        $lead  = $model->getEntity();
        if ($this->get('mautic.helper.licenseinfo')->redirectToCardinfo()) {
            return $this->delegateRedirect($this->generateUrl('le_accountinfo_action', ['objectAction' => 'cardinfo']));
        }
        $isValidRecordAdd = $this->get('mautic.helper.licenseinfo')->isValidRecordAdd();
        $actualrecord     = $this->get('mautic.helper.licenseinfo')->getActualRecordCount();
        $totalrecord      = $this->get('mautic.helper.licenseinfo')->getTotalRecordCount();
        $actualrecord     = number_format($actualrecord);
        $totalrecord      = $totalrecord == 'UL' ? 'Unlimited' : number_format($totalrecord);
        if (!$this->get('mautic.security')->isGranted('lead:leads:create')) {
            return $this->accessDenied();
        }

        //set the page we came from
        $page = $this->get('session')->get('mautic.lead.page', 1);

        $fields      =[];
        $action      =[];
        $formtemplate='form';
        $inQuickForm = $this->request->get('qf', false);
        if ($inQuickForm) {
            $action      = $this->generateUrl('le_contact_action', ['objectAction' => 'new', 'qf' => 1]);
            $fields      =$this->getQuickAddFields();
            $formtemplate='quickadd';
            if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
                return $this->redirectToPricing();
            }
        } else {
            if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
                return $this->delegateRedirect($this->generateUrl('le_pricing_index'));
            }
            $action = $this->generateUrl('le_contact_action', ['objectAction' => 'new']);
            $fields = $this->getModel('lead.field')->getPublishedFieldArrays('lead');
        }
        $viewParameters = ['page' => $page];
        $returnUrl      = $this->generateUrl('le_contact_index', $viewParameters);
        if (!$isValidRecordAdd) {
            $inQuickForm = $this->request->get('qf', false);
            $msg         = $this->translator->trans('le.record.count.exceeds', ['%USEDCOUNT%' => $actualrecord, '%ACTUALCOUNT%' => $totalrecord]);
            $this->addFlash($msg);
            if (!$inQuickForm) {
                $postActionVars = [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'MauticLeadBundle:Lead:index',
                    'passthroughVars' => [
                        'activeLink'    => '#le_contact_index',
                        'leContent'     => 'lead',
                    ],
                ];

                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars
                    )
                );
            } else {
                $template       = 'MauticLeadBundle:Lead:index';

                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                        'passthroughVars' => [
                            'activeLink'    => '#le_contact_index',
                            'leContent'     => 'lead',
                            'closeModal'    => 1, //just in case in quick form
                        ],
                    ]
                );
            }
        }
        $form = $model->createForm($lead, $this->get('form.factory'), $action, ['fields' => $fields, 'isShortForm' => $inQuickForm]);

        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //get custom field values
                    $data = $this->request->request->get('lead');
                    if ($data['email'] == '') {
                        $form['email']->addError(
                            new FormError(
                                $this->get('translator')->trans('le.core.email.required', [], 'validators')
                            )
                        );

                        return $this->delegateView(
                            [
                                'viewParameters' => [
                                    'form'   => $form->createView(),
                                    'lead'   => $lead,
                                    'fields' => $model->organizeFieldsByGroup($fields),
                                ],
                                'contentTemplate' => 'MauticLeadBundle:Lead:'.$formtemplate.'.html.php',
                                'passthroughVars' => [
                                    'activeLink'    => '#le_contact_index',
                                    'leContent'     => 'lead',
                                    'route'         => $this->generateUrl(
                                        'le_contact_action',
                                        [
                                            'objectAction' => 'new',
                                        ]
                                    ),
                                ],
                            ]
                        );
                    }

                    //pull the data from the form in order to apply the form's formatting
                    foreach ($form as $f) {
                        if ('companies' !== $f->getName()) {
                            $data[$f->getName()] = $f->getData();
                        }
                    }

                    $companies = [];
                    if (isset($data['companies'])) {
                        $companies = $data['companies'];
                        unset($data['companies']);
                    }
                    $segment = [];
                    if (isset($data['lead_lists'])) {
                        $segment = $data['lead_lists'];
                        unset($data['lead_lists']);
                    }
                    $lists = [];
                    if (isset($data['lead_listsoptin'])) {
                        $lists = $data['lead_listsoptin'];
                        unset($data['lead_listsoptin']);
                    }
                    $model->setFieldValues($lead, $data, true);

                    if ($isValidRecordAdd) {
                        //form is valid so process the data
                        $lead->setManipulator(new LeadManipulator(
                        'lead',
                        'lead',
                        null,
                        $this->get('mautic.helper.user')->getUser()->getName()
                    ));
                        $model->saveEntity($lead);
                        $this->get('mautic.helper.licenseinfo')->intRecordCount('1', true);
                    } else {
                        $msg = $this->translator->trans('le.record.count.exceeds', ['%USEDCOUNT%' => $actualrecord, '%ACTUALCOUNT%' => $totalrecord]);
                        $this->addFlash($msg);
                    }

                    if (!empty($companies)) {
                        $model->modifyCompanies($lead, $companies);
                    }
                    if (!empty($segment)) {
                        $model->modifySegments($lead, $segment);
                    }
                    if (!empty($lists)) {
                        $model->modifyListOptIn($lead, $lists);
                    }
                    $model->isTagsChanged($lead);
                    // Upload avatar if applicable
                    $image =  'gravatar';
                    if (!$inQuickForm) {
                        $image = $form['preferred_profile_image']->getData();
                    }
                    if ($image == 'custom') {
                        // Check for a file
                        /** @var UploadedFile $file */
                        if ($file = $form['custom_avatar']->getData()) {
                            $this->uploadAvatar($lead);
                        }
                    }

                    $identifier = $this->get('translator')->trans($lead->getPrimaryIdentifier());

                    $this->addFlash(
                        'mautic.core.notice.created',
                        [
                            '%name%'      => $identifier,
                            '%menu_link%' => 'le_contact_index',
                            '%url%'       => $this->generateUrl(
                                'le_contact_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $lead->getId(),
                                ]
                            ),
                        ]
                    );

                    if ($inQuickForm) {
                        $viewParameters = ['page' => $page];
                        $returnUrl      = $this->generateUrl('le_contact_index', $viewParameters);
                        $template       = 'MauticLeadBundle:Lead:index';
                    } elseif ($form->get('buttons')->get('save')->isClicked()) {
                        $viewParameters = [
                            'objectAction' => 'view',
                            'objectId'     => $lead->getId(),
                        ];
                        $returnUrl = $this->generateUrl('le_contact_action', $viewParameters);
                        $template  = 'MauticLeadBundle:Lead:view';
                    } else {
                        if ($isValidRecordAdd) {
                            return $this->editAction($lead->getId(), true);
                        }
                    }
                }
            } else {
                $viewParameters = ['page' => $page];
                $returnUrl      = $this->generateUrl('le_contact_index', $viewParameters);
                $template       = 'MauticLeadBundle:Lead:index';
            }

            if ($valid) { // success
                if ($isValidRecordAdd) {
                    return $this->postActionRedirect(
                        [
                            'returnUrl'       => $returnUrl,
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => $template,
                            'passthroughVars' => [
                                'activeLink'    => '#le_contact_index',
                                'leContent'     => 'lead',
                                'closeModal'    => 1, //just in case in quick form
                            ],
                        ]
                    );
                }
            } elseif ($cancelled) { //failure
                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                        'passthroughVars' => [
                            'activeLink'    => '#le_contact_index',
                            'leContent'     => 'lead',
                            'closeModal'    => 1, //just in case in quick form
                        ],
                    ]
                );
            }
        } else {
            //set the default owner to the currently logged in user
            $currentUser = $this->get('security.context')->getToken()->getUser();
            $form->get('owner')->setData($currentUser);
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'   => $form->createView(),
                    'lead'   => $lead,
                    'fields' => $model->organizeFieldsByGroup($fields),
                ],
                'contentTemplate' => 'MauticLeadBundle:Lead:'.$formtemplate.'.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_contact_index',
                    'leContent'     => 'lead',
                    'route'         => $this->generateUrl(
                        'le_contact_action',
                        [
                            'objectAction' => 'new',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Generates edit form.
     *
     * @param            $objectId
     * @param bool|false $ignorePost
     *
     * @return array|JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($objectId, $ignorePost = false)
    {
        /** @var LeadModel $model */
        $model = $this->getModel('lead.lead');
        $lead  = $model->getEntity($objectId);
        if ($this->get('mautic.helper.licenseinfo')->redirectToCardinfo()) {
            return $this->delegateRedirect($this->generateUrl('le_accountinfo_action', ['objectAction' => 'cardinfo']));
        }
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->delegateRedirect($this->generateUrl('le_pricing_index'));
        }
        //set the page we came from
        $page = $this->get('session')->get('mautic.lead.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('le_contact_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticLeadBundle:Lead:index',
            'passthroughVars' => [
                'activeLink'    => '#le_contact_index',
                'leContent'     => 'lead',
            ],
        ];
        //lead not found
        if ($lead === null) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'le.lead.lead.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'lead:leads:editown',
            'lead:leads:editother',
            $lead->getPermissionUser()
        )
        ) {
            return $this->accessDenied();
        } elseif ($model->isLocked($lead)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $lead, 'lead.lead');
        }

        $action = $this->generateUrl('le_contact_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $fields = $this->getModel('lead.field')->getPublishedFieldArrays('lead');
        $form   = $model->createForm($lead, $this->get('form.factory'), $action, ['fields' => $fields]);

        ///Check for a submitted form and process it
        if (!$ignorePost && $this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $data = $this->request->request->get('lead');
                    if ($data['email'] == '') {
                        $form['email']->addError(
                            new FormError(
                                $this->get('translator')->trans('le.core.email.required', [], 'validators')
                            )
                        );

                        return $this->delegateView(
                            [
                                'viewParameters' => [
                                    'form'   => $form->createView(),
                                    'lead'   => $lead,
                                    'fields' => $lead->getFields(), //pass in the lead fields as they are already organized by ['group']['alias']
                                ],
                                'contentTemplate' => 'MauticLeadBundle:Lead:form.html.php',
                                'passthroughVars' => [
                                    'activeLink'    => '#le_contact_index',
                                    'leContent'     => 'lead',
                                    'route'         => $this->generateUrl(
                                        'le_contact_action',
                                        [
                                            'objectAction' => 'edit',
                                            'objectId'     => $lead->getId(),
                                        ]
                                    ),
                                ],
                            ]
                        );
                    }

                    //pull the data from the form in order to apply the form's formatting
                    foreach ($form as $f) {
                        if (('companies' !== $f->getName()) && ('company' !== $f->getName())) {
                            $data[$f->getName()] = $f->getData();
                        }
                    }

                    $companies = [];
                    if (isset($data['companies'])) {
                        $companies = $data['companies'];
                        unset($data['companies']);
                    }

                    $segment = [];
                    if (isset($data['lead_lists'])) {
                        $segment = $data['lead_lists'];
                        unset($data['lead_lists']);
                    }

                    $lists = [];
                    if (isset($data['lead_listsoptin'])) {
                        $lists = $data['lead_listsoptin'];
                        unset($data['lead_listsoptin']);
                    }

                    $model->setFieldValues($lead, $data, true);

                    //form is valid so process the data
                    $lead->setManipulator(new LeadManipulator(
                        'lead',
                        'lead',
                        $objectId,
                        $this->get('mautic.helper.user')->getUser()->getName()
                    ));
                    $model->saveEntity($lead, $form->get('buttons')->get('save')->isClicked());
                    $model->modifyCompanies($lead, $companies);
                    $model->modifySegments($lead, $segment);
                    $model->modifyListOptIn($lead, $lists);
                    $model->isTagsChanged($lead);
                    // Upload avatar if applicable
                    $image = $form['preferred_profile_image']->getData();
                    if ($image == 'custom') {
                        // Check for a file
                        /** @var UploadedFile $file */
                        if ($file = $form['custom_avatar']->getData()) {
                            $this->uploadAvatar($lead);

                            // Note the avatar update so that it can be forced to update
                            $this->get('session')->set('mautic.lead.avatar.updated', true);
                        }
                    }

                    $identifier = $this->get('translator')->trans($lead->getPrimaryIdentifier());

                    $this->addFlash(
                        'mautic.core.notice.updated',
                        [
                            '%name%'      => $identifier,
                            '%menu_link%' => 'le_contact_index',
                            '%url%'       => $this->generateUrl(
                                'le_contact_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $lead->getId(),
                                ]
                            ),
                        ]
                    );
                }
            } else {
                //unlock the entity
                $model->unlockEntity($lead);
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $lead->getId(),
                ];

                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars,
                        [
                            'returnUrl'       => $this->generateUrl('le_contact_action', $viewParameters),
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => 'MauticLeadBundle:Lead:view',
                        ]
                    )
                );
            } elseif ($valid) {
                // Refetch and recreate the form in order to populate data manipulated in the entity itself
                $lead = $model->getEntity($objectId);
                $form = $model->createForm($lead, $this->get('form.factory'), $action, ['fields' => $fields]);
            }
        } else {
            //lock the entity
            $model->lockEntity($lead);
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'   => $form->createView(),
                    'lead'   => $lead,
                    'fields' => $lead->getFields(), //pass in the lead fields as they are already organized by ['group']['alias']
                ],
                'contentTemplate' => 'MauticLeadBundle:Lead:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_contact_index',
                    'leContent'     => 'lead',
                    'route'         => $this->generateUrl(
                        'le_contact_action',
                        [
                            'objectAction' => 'edit',
                            'objectId'     => $lead->getId(),
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Upload an asset.
     *
     * @param Lead $lead
     */
    private function uploadAvatar(Lead $lead)
    {
        $file      = $this->request->files->get('lead[custom_avatar]', null, true);
        $avatarDir = $this->get('mautic.helper.template.avatar')->getAvatarPath(true);

        if (!file_exists($avatarDir)) {
            mkdir($avatarDir);
        }

        $file->move($avatarDir, 'avatar'.$lead->getId());

        //remove the file from request
        $this->request->files->remove('lead');
    }

    /**
     * Generates merge form and action.
     *
     * @param   $objectId
     *
     * @return array|JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function mergeAction($objectId)
    {
        /** @var \Mautic\LeadBundle\Model\LeadModel $model */
        $model    = $this->getModel('lead');
        $mainLead = $model->getEntity($objectId);
        $page     = $this->get('session')->get('mautic.lead.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('le_contact_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticLeadBundle:Lead:index',
            'passthroughVars' => [
                'activeLink'    => '#le_contact_index',
                'leContent'     => 'lead',
            ],
        ];

        if ($mainLead === null) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'le.lead.lead.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        }

        //do some default filtering
        $session = $this->get('session');
        $search  = $this->request->get('search', $session->get('mautic.lead.merge.filter', ''));
        $session->set('mautic.lead.merge.filter', $search);
        $leads = [];

        if (!empty($search)) {
            $filter = [
                'string' => $search,
                'force'  => [
                    [
                        'column' => 'l.date_identified',
                        'expr'   => 'isNotNull',
                        'value'  => $mainLead->getId(),
                    ],
                    [
                        'column' => 'l.id',
                        'expr'   => 'neq',
                        'value'  => $mainLead->getId(),
                    ],
                ],
            ];

            $leads = $model->getEntities(
                [
                    'limit'          => 25,
                    'filter'         => $filter,
                    'orderBy'        => 'l.firstname,l.lastname,l.company,l.email',
                    'orderByDir'     => 'ASC',
                    'withTotalCount' => false,
                ]
            );
        }

        $leadChoices = [];
        foreach ($leads as $l) {
            $leadChoices[$l->getId()] = $l->getPrimaryIdentifier();
        }

        $action = $this->generateUrl('le_contact_action', ['objectAction' => 'merge', 'objectId' => $mainLead->getId()]);

        $form = $this->get('form.factory')->create(
            'lead_merge',
            [],
            [
                'action' => $action,
                'leads'  => $leadChoices,
            ]
        );

        if ($this->request->getMethod() == 'POST') {
            $valid = true;
            if (!$this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $data      = $form->getData();
                    $secLeadId = $data['lead_to_merge'];
                    $secLead   = $model->getEntity($secLeadId);

                    if ($secLead === null) {
                        return $this->postActionRedirect(
                            array_merge(
                                $postActionVars,
                                [
                                    'flashes' => [
                                        [
                                            'type'    => 'error',
                                            'msg'     => 'le.lead.lead.error.notfound',
                                            'msgVars' => ['%id%' => $secLead->getId()],
                                        ],
                                    ],
                                ]
                            )
                        );
                    } elseif (
                        !$this->get('mautic.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $mainLead->getPermissionUser())
                        || !$this->get('mautic.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $secLead->getPermissionUser())
                    ) {
                        return $this->accessDenied();
                    } elseif ($model->isLocked($mainLead)) {
                        //deny access if the entity is locked
                        return $this->isLocked($postActionVars, $secLead, 'lead');
                    } elseif ($model->isLocked($secLead)) {
                        //deny access if the entity is locked
                        return $this->isLocked($postActionVars, $secLead, 'lead');
                    }

                    //Both leads are good so now we merge them
                    $mainLead = $model->mergeLeads($mainLead, $secLead, false);
                }
            }

            if ($valid) {
                $viewParameters = [
                    'objectId'     => $mainLead->getId(),
                    'objectAction' => 'view',
                ];

                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $this->generateUrl('le_contact_action', $viewParameters),
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => 'MauticLeadBundle:Lead:view',
                        'passthroughVars' => [
                            'closeModal'    => 1,
                            'leContent'     => 'lead',
                        ],
                    ]
                );
            }
        }

        $tmpl = $this->request->get('tmpl', 'index');

        return $this->delegateView(
            [
                'viewParameters' => [
                    'tmpl'         => $tmpl,
                    'leads'        => $leads,
                    'searchValue'  => $search,
                    'action'       => $action,
                    'form'         => $form->createView(),
                    'currentRoute' => $this->generateUrl(
                        'le_contact_action',
                        [
                            'objectAction' => 'merge',
                            'objectId'     => $mainLead->getId(),
                        ]
                    ),
                ],
                'contentTemplate' => 'MauticLeadBundle:Lead:merge.html.php',
                'passthroughVars' => [
                    'route'  => false,
                    'target' => ($tmpl == 'update') ? '.lead-merge-options' : null,
                ],
            ]
        );
    }

    /**
     * Generates contact frequency rules form and action.
     *
     * @param   $objectId
     *
     * @return array|JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function contactFrequencyAction($objectId)
    {
        /** @var LeadModel $model */
        $model = $this->getModel('lead');
        $lead  = $model->getEntity($objectId);

        if ($lead === null
            || !$this->get('mautic.security')->hasEntityAccess(
                'lead:leads:editown',
                'lead:leads:editother',
                $lead->getPermissionUser()
            )
        ) {
            return $this->accessDenied();
        }

        $viewParameters = [
            'objectId'     => $lead->getId(),
            'objectAction' => 'view',
        ];

        $form = $this->getFrequencyRuleForm(
            $lead,
            $viewParameters,
            $data,
            false,
            $this->generateUrl('le_contact_action', ['objectAction' => 'contactFrequency', 'objectId' => $lead->getId()])
        );

        if (true === $form) {
            return $this->postActionRedirect(
                [
                    'returnUrl'       => $this->generateUrl('le_contact_action', $viewParameters),
                    'viewParameters'  => $viewParameters,
                    'contentTemplate' => 'MauticLeadBundle:Lead:view',
                    'passthroughVars' => [
                        'closeModal' => 1,
                    ],
                ]
            );
        }

        $tmpl = $this->request->get('tmpl', 'index');

        return $this->delegateView(
            [
                'viewParameters' => array_merge(
                    [
                        'tmpl'         => $tmpl,
                        'form'         => $form->createView(),
                        'currentRoute' => $this->generateUrl(
                            'le_contact_action',
                            [
                                'objectAction' => 'contactFrequency',
                                'objectId'     => $lead->getId(),
                            ]
                        ),
                        'lead' => $lead,
                    ],
                    $viewParameters
                ),
                'contentTemplate' => 'MauticLeadBundle:Lead:frequency.html.php',
                'passthroughVars' => [
                    'route'  => false,
                    'target' => ($tmpl == 'update') ? '.lead-frequency-options' : null,
                ],
            ]
        );
    }

    /**
     * Deletes the entity.
     *
     * @param   $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        $page      = $this->get('session')->get('mautic.lead.page', 1);
        $returnUrl = $this->generateUrl('le_contact_index', ['page' => $page]);
        $flashes   = [];
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            $this->redirectToPricing();
        }

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticLeadBundle:Lead:index',
            'passthroughVars' => [
                'activeLink'    => '#le_contact_index',
                'leContent'     => 'lead',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model  = $this->getModel('lead.lead');
            $entity = $model->getEntity($objectId);

            if ($entity === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'le.lead.lead.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('mautic.security')->hasEntityAccess(
                'lead:leads:deleteown',
                'lead:leads:deleteother',
                $entity->getPermissionUser()
            )
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'lead.lead');
            } else {
                $falshMsg         ='';
                $currentMonth     =date('Y-m');
                $deleteCount      =$this->get('mautic.helper.licenseinfo')->getDeleteCount();
                $totalRecordCount =$this->get('mautic.helper.licenseinfo')->getTotalRecordCount();
                $totalDeleteCount = $deleteCount + 1;
                if (($totalRecordCount * 2) >= $totalDeleteCount || $totalRecordCount == 'UL') {
                    $model->deleteEntity($entity);
                    $this->get('mautic.helper.licenseinfo')->intRecordCount('1', false);
                    $this->get('mautic.helper.licenseinfo')->intDeleteCount('1', true);
                    $this->get('mautic.helper.licenseinfo')->intDeleteMonth($currentMonth);
                    $falshMsg= 'mautic.core.notice.deleted';
                } else {
                    $falshMsg= 'mautic.core.notice.restrict.deleted';
                }

                $primaryidentifier = $this->get('translator')->trans($entity->getPrimaryIdentifier());
                $identifier        = !empty($primaryidentifier) ? $primaryidentifier : $entity->getEmail();
                $flashes[]         = [
                    'type'    => 'notice',
                    'msg'     => $falshMsg,
                    'msgVars' => [
                        '%name%' => $identifier,
                        '%id%'   => $objectId,
                    ],
                ];
            }
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
        );
    }

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        $page      = $this->get('session')->get('mautic.lead.page', 1);
        $returnUrl = $this->generateUrl('le_contact_index', ['page' => $page]);
        $flashes   = [];
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->redirectToPricing();
        }
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticLeadBundle:Lead:index',
            'passthroughVars' => [
                'activeLink'    => '#le_contact_index',
                'leContent'     => 'lead',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model     = $this->getModel('lead');
            $ids       = json_decode($this->request->query->get('ids', '{}'));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'le.lead.lead.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('mautic.security')->hasEntityAccess(
                    'lead:leads:deleteown',
                    'lead:leads:deleteother',
                    $entity->getPermissionUser()
                )
                ) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'lead', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $flashMsg         = '';
                $entities         ='';
                $currentMonth     =date('Y-m');
                $deleteCounts     =count($deleteIds);
                $dbDeleteCount    =$this->get('mautic.helper.licenseinfo')->getDeleteCount();
                $totalRecordCount =$this->get('mautic.helper.licenseinfo')->getTotalRecordCount();
                $pendingDelete    = $dbDeleteCount + $deleteCounts;

                if (($totalRecordCount * 2) >= $pendingDelete || $totalRecordCount == 'UL') {
                    $entities = $model->deleteEntities($deleteIds);
                    $this->get('mautic.helper.licenseinfo')->intRecordCount($deleteCounts, false);
                    $this->get('mautic.helper.licenseinfo')->intDeleteCount($deleteCounts, true);
                    $this->get('mautic.helper.licenseinfo')->intDeleteMonth($currentMonth);
                    $flashMsg= 'le.lead.lead.notice.batch_deleted';
                } else {
                    $flashMsg= 'le.lead.lead.notice.batch_deleted.restrict';
                }

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => $flashMsg,
                    'msgVars' => [
                        '%count%' => count($entities),
                    ],
                ];
            }
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
        );
    }

    /**
     * Add/remove lead from a list.
     *
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function listAction($objectId)
    {
        /** @var \Mautic\LeadBundle\Model\LeadModel $model */
        $model = $this->getModel('lead');
        $lead  = $model->getEntity($objectId);

        if ($lead != null
            && $this->get('mautic.security')->hasEntityAccess(
                'lead:leads:editown',
                'lead:leads:editother',
                $lead->getPermissionUser()
            )
        ) {
            /** @var \Mautic\LeadBundle\Model\ListModel $listModel */
            $listModel = $this->getModel('lead.list');
            $lists     = $listModel->getUserLists();

            // Get a list of lists for the lead
            $leadsLists = $model->getLists($lead, true, true);
        } else {
            $lists = $leadsLists = [];
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'lists'      => $lists,
                    'leadsLists' => $leadsLists,
                    'lead'       => $lead,
                ],
                'contentTemplate' => 'MauticLeadBundle:LeadLists:index.html.php',
            ]
        );
    }

    /**
     * Add/remove lead from a company.
     *
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function companyAction($objectId)
    {
        /** @var \Mautic\LeadBundle\Model\LeadModel $model */
        $model = $this->getModel('lead');
        $lead  = $model->getEntity($objectId);

        if ($lead != null
            && $this->get('mautic.security')->hasEntityAccess(
                'lead:leads:editown',
                'lead:leads:editother',
                $lead->getOwner()
            )
        ) {
            /** @var \Mautic\LeadBundle\Model\CompanyModel $companyModel */
            $companyModel = $this->getModel('lead.company');
            $companies    = $companyModel->getUserCompanies();

            // Get a list of lists for the lead
            $companyLeads = $lead->getCompanies();
            foreach ($companyLeads as $cl) {
                $companyLead[$cl->getId()] = $cl->getId();
            }
        } else {
            $companies = $companyLead = [];
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'companies'   => $companies,
                    'companyLead' => $companyLead,
                    'lead'        => $lead,
                ],
                'contentTemplate' => 'MauticLeadBundle:Lead:company.html.php',
            ]
        );
    }

    /**
     * Add/remove lead from a campaign.
     *
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function campaignAction($objectId)
    {
        $model = $this->getModel('lead');
        $lead  = $model->getEntity($objectId);

        if ($lead != null
            && $this->get('mautic.security')->hasEntityAccess(
                'lead:leads:editown',
                'lead:leads:editother',
                $lead->getPermissionUser()
            )
        ) {
            /** @var \Mautic\CampaignBundle\Model\CampaignModel $campaignModel */
            $campaignModel  = $this->getModel('campaign');
            $campaigns      = $campaignModel->getPublishedCampaigns(true);
            $leadsCampaigns = $campaignModel->getLeadCampaigns($lead, true);

            foreach ($campaigns as $c) {
                $campaigns[$c['id']]['inCampaign'] = (isset($leadsCampaigns[$c['id']])) ? true : false;
            }
        } else {
            $campaigns = [];
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'campaigns' => $campaigns,
                    'lead'      => $lead,
                ],
                'contentTemplate' => 'MauticLeadBundle:LeadCampaigns:index.html.php',
            ]
        );
    }

    /**
     * @param int $objectId
     *
     * @return JsonResponse
     */
    public function emailAction($objectId = 0)
    {
        $valid = $cancelled = false;
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->redirectToPricing();
        }
        /** @var \Mautic\LeadBundle\Model\LeadModel $model */
        $model = $this->getModel('lead');

        /** @var \Mautic\LeadBundle\Entity\Lead $lead */
        $lead                  = $model->getEntity($objectId);
        $isValidEmailCount     = $this->get('mautic.helper.licenseinfo')->isValidEmailCount();
        $isHavingEmailValidity = $this->get('mautic.helper.licenseinfo')->isHavingEmailValidity();
        $accountStatus         = $this->get('mautic.helper.licenseinfo')->getAccountStatus();

        if ($lead === null
            || !$this->get('mautic.security')->hasEntityAccess(
                'lead:leads:viewown',
                'lead:leads:viewother',
                $lead->getPermissionUser()
            )
        ) {
            return $this->modalAccessDenied();
        }

        if (!$this->get('mautic.helper.mailer')->emailstatus(false)) {
            $configurl=$this->factory->getRouter()->generate('le_config_action', ['objectAction' => 'edit']);
            $this->addFlash($this->translator->trans('le.email.config.mailer.status.report', ['%url%'=>$configurl]));

            return $this->postActionRedirect(
                [
                    'passthroughVars' => [
                        'closeModal' => 1,
                        'route'      => false,
                    ],
                ]
            );
        }
        if (!$isHavingEmailValidity) {
            $this->addFlash($this->translator->trans('mautic.email.validity.expired'));

            return $this->postActionRedirect(
                [
                    'passthroughVars' => [
                        'closeModal' => 1,
                        'route'      => false,
                    ],
                ]
            );
        }

        $leadFields       = $lead->getProfileFields();
        $leadFields['id'] = $lead->getId();
        $leadEmail        = $leadFields['email'];
        $leadName         = $leadFields['firstname'].' '.$leadFields['lastname'];

        // Set onwer ID to be the current user ID so it will use his signature
        $leadFields['owner_id'] = $this->get('mautic.helper.user')->getUser()->getId();

        // Check if lead has a bounce status
        /** @var \Mautic\EmailBundle\Model\EmailModel $emailModel */
        $emailModel    = $this->getModel('email');
        $emailRepo     = $emailModel->getRepository();
        $dnc           = $emailModel->getRepository()->checkDoNotEmail($leadEmail);
        $verifiedemail = $emailModel->getVerifiedEmailAddress();

        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator   = $this->get('mautic.configurator');
        $params         = $configurator->getParameters();
        $maileruser     = $params['mailer_user'];
        $mailertransport= $params['mailer_transport'];
        $emailpassword  = $params['mailer_password'];
        if (isset($params['mailer_amazon_region'])) {
            $region                = $params['mailer_amazon_region'];
        } else {
            $region='';
        }
        //$region         = $params['mailer_amazon_region'];

//        $emailValidator = $this->factory->get('mautic.validator.email');
//        if ($mailertransport == 'le.transport.amazon') {
//            $emails = $emailValidator->getVerifiedEmailList($maileruser, $emailpassword, $region);
//            if (!empty($emails)) {
//                $emailModel->upAwsEmailVerificationStatus($emails);
//            }
//        }
        $inList = ($this->request->getMethod() == 'GET')
            ? $this->request->get('list', 0)
            : $this->request->request->get(
                'lead_quickemail[list]',
                0,
                true
            );
        $email  = ['list' => $inList];
        $action = $this->generateUrl('le_contact_action', ['objectAction' => 'email', 'objectId' => $objectId]);
        $form   = $this->get('form.factory')->create('lead_quickemail', $email, ['action' => $action]);

        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $email = $form->getData();
                    //This line is commented because img and single tags are deleted
                    //$bodyCheck = trim(strip_tags($email['body']));
                    $bodyCheck= trim($email['body']);
                    $hasValue = $this->checkEmailBody($bodyCheck);

                    if ($hasValue) { //!empty($bodyCheck)
                        $mailer = $this->get('mautic.helper.mailer')->getMailer();

                        // To lead
                        $mailer->addTo($leadEmail, $leadName);

                        // From user
                        $user = $this->get('mautic.helper.user')->getUser();

                        $mailer->setFrom(
                            $email['from'],
                            empty($email['fromname']) ? null : $email['fromname']
                        );

                        // Set Content
                        $mailer->setBody($email['body']);
                        $mailer->parsePlainText($email['body']);

                        // Set lead
                        $mailer->setLead($leadFields);
                        $mailer->setIdHash();

                        $mailer->setSubject($email['subject']);

                        // Ensure safe emoji for notification
                        $subject = EmojiHelper::toHtml($email['subject']);
                        if (!empty($email['templates'])) {
                            $assets = $emailModel->getRepository()->getEntity($email['templates'])->getAssetAttachments();
                            $mailer->setEmail($emailModel->getRepository()->getEntity($email['templates']));
                            if (!empty($assets) && $assets != null) {
                                foreach ($assets as $asset) {
                                    $mailer->attachAsset($asset);
                                }
                            }
                        }
                        if (!$accountStatus) {
                            if ($isValidEmailCount && $isHavingEmailValidity) {
                                if ($mailer->send(true, false, false)) {
                                    if (!empty($email['templates'])) {
                                        $emailRepo->upCount($email['templates'], 'sent', 1, false);
                                    }
                                    $mailer->createEmailStat();
                                    $this->get('mautic.helper.licenseinfo')->intEmailCount('1');
                                    $this->addFlash(
                                'le.lead.email.notice.sent',
                                [
                                   // '%subject%' => $subject,
                                    '%email%'   => $leadEmail,
                                ]
                            );
                                } else {
                                    $errors = $mailer->getErrors();

                                    // Unset the array of failed email addresses
                                    if (isset($errors['failures'])) {
                                        unset($errors['failures']);
                                    }

                                    $form->addError(
                                new FormError(
                                    $this->get('translator')->trans(
                                        'le.lead.email.error.failed',
                                        [
                                            '%subject%' => $subject,
                                            '%email%'   => $leadEmail,
                                            '%error%'   => (is_array($errors)) ? implode('<br />', $errors) : $errors,
                                        ],
                                        'flashes'
                                    )
                                )
                            );
                                    $valid = false;
                                }
                            } else {
                                if (!$isHavingEmailValidity) {
                                    $this->addFlash('mautic.email.validity.expired');
                                } else {
                                    $configurl     = $this->factory->getRouter()->generate('le_config_action', ['objectAction' => 'edit']);
                                    $this->addFlash('mautic.email.count.exceeds', ['%url%'=>$configurl]);
                                }
                            }
                        } else {
                            $this->addFlash('mautic.account.suspend');
                        }
                    } else {
                        $form['body']->addError(
                            new FormError(
                                $this->get('translator')->trans('le.lead.email.body.required', [], 'validators')
                            )
                        );
                        $valid = false;
                    }
                }
            }
        }

        if (empty($leadEmail) || $valid || $cancelled) {
            if ($inList) {
                $route          = 'le_contact_index';
                $viewParameters = [
                    'page' => $this->get('session')->get('mautic.lead.page', 1),
                ];
                $func = 'index';
            } else {
                $route          = 'le_contact_action';
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $objectId,
                ];
                $func = 'view';
            }

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $this->generateUrl($route, $viewParameters),
                    'viewParameters'  => $viewParameters,
                    'contentTemplate' => 'MauticLeadBundle:Lead:'.$func,
                    'passthroughVars' => [
                        'leContent'     => 'lead',
                        'closeModal'    => 1,
                    ],
                ]
            );
        }

        return $this->ajaxAction(
            [
                'contentTemplate' => 'MauticLeadBundle:Lead:email.html.php',
                'viewParameters'  => [
                    'form'           => $form->createView(),
                    'dnc'            => $dnc,
                    'verifiedemail'  => $verifiedemail,
                    'mailertransport'=> $mailertransport,
                ],
                'passthroughVars' => [
                    'leContent'     => 'leadEmail',
                    'route'         => false,
                ],
            ]
        );
    }

    /**
     * Bulk edit lead lists.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function batchListsAction($objectId = 0)
    {
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->redirectToPricing();
        }
        if ($this->request->getMethod() == 'POST') {
            /** @var \Mautic\LeadBundle\Model\LeadModel $model */
            $model = $this->getModel('lead');
            $data  = $this->request->request->get('lead_batch', [], true);
            $ids   = json_decode($data['ids'], true);

            $entities = [];
            if (is_array($ids)) {
                $entities = $model->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'l.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }

            $count = 0;
            foreach ($entities as $lead) {
                if ($this->get('mautic.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $lead->getPermissionUser())) {
                    ++$count;

                    if (!empty($data['add'])) {
                        $model->addToLists($lead, $data['add']);
                    }

                    if (!empty($data['remove'])) {
                        $model->removeFromLists($lead, $data['remove']);
                    }
                }
            }

            $this->addFlash(
                'le.lead.batch_leads_affected',
                [
                    'pluralCount' => $count,
                    '%count%'     => $count,
                ]
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        } else {
            // Get a list of lists
            /** @var \Mautic\LeadBundle\Model\ListModel $model */
            $model = $this->getModel('lead.list');
            $lists = $model->getUserLists();
            $items = [];
            foreach ($lists as $list) {
                $items[$list['id']] = $list['name'];
            }

            $route = $this->generateUrl(
                'le_contact_action',
                [
                    'objectAction' => 'batchLists',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            'lead_batch',
                            [],
                            [
                                'items'  => $items,
                                'action' => $route,
                                'label'  => 'list',
                            ]
                        )->createView(),
                    ],
                    'contentTemplate' => 'MauticLeadBundle:Batch:form.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#le_contact_index',
                        'leContent'     => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
    }

    /**
     * Bulk edit lead lists.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function batchListOptinAction($objectId = 0)
    {
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->redirectToPricing();
        }

        // Get a list of lists
        /** @var \Mautic\LeadBundle\Model\ListOptInModel $model */
        $model = $this->getModel('lead.listoptin');
        $lists = $model->getListsOptIn();
        $items = [];
        foreach ($lists as $list) {
            $items[$list['id']] = $list['name'];
        }

        $route = $this->generateUrl(
            'le_contact_action',
            [
                'objectAction' => 'batchListOptin',
            ]
        );
        $form = $this->createForm(
            'lead_batch',
            [],
            [
                'items'  => $items,
                'action' => $route,
                'label'  => 'listoptin',
            ]
        );

        if ($this->request->getMethod() == 'POST') {
            /** @var \Mautic\LeadBundle\Model\LeadModel $model */
            $model = $this->getModel('lead');
            $data  = $this->request->request->get('lead_batch', [], true);
            $ids   = json_decode($data['ids'], true);
            if (empty($data['add']) && empty($data['remove'])) {
                $form['add']->addError(
                    new FormError($this->translator->trans('le.lead.batch.listoptin.add.required', [], 'validators'))
                );
                $form['remove']->addError(
                    new FormError($this->translator->trans('le.lead.batch.listoptin.remove.required', [], 'validators'))
                );

                return $this->delegateView(
                    [
                        'viewParameters' => [
                            'form' => $form->createView(),
                        ],
                        'contentTemplate' => 'MauticLeadBundle:Batch:form.html.php',
                        'passthroughVars' => [
                            'activeLink'    => '#le_contact_index',
                            'leContent'     => 'leadBatch',
                            'route'         => $route,
                        ],
                    ]
                );
            }

            $entities = [];
            if (is_array($ids)) {
                $entities = $model->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'l.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }

            $count = 0;
            foreach ($entities as $lead) {
                if ($this->get('mautic.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $lead->getPermissionUser())) {
                    ++$count;

                    if (!empty($data['add'])) {
                        $model->addToListOptIn($lead, $data['add']);
                    }

                    if (!empty($data['remove'])) {
                        $model->removeFromListOptIn($lead, $data['remove']);
                    }
                }
            }

            $this->addFlash(
                'le.lead.batch_leads_affected',
                [
                    'pluralCount' => $count,
                    '%count%'     => $count,
                ]
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        } else {
            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $form->createView(),
                    ],
                    'contentTemplate' => 'MauticLeadBundle:Batch:form.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#le_contact_index',
                        'leContent'     => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
    }

    /**
     * Bulk edit lead lists.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function batchTagsAction($objectId = 0)
    {
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->redirectToPricing();
        }

        // Get a list of lists
        /** @var \Mautic\LeadBundle\Model\TagModel $model */
        $tagModel = $this->getModel('lead.tag');
        $lists    = $tagModel->getTagsList();
        $items    = [];
        foreach ($lists as $list) {
            $items[$list['id']] = $list['name'];
        }

        $route = $this->generateUrl(
            'le_contact_action',
            [
                'objectAction' => 'batchTags',
            ]
        );

        $form = $this->createForm(
            'lead_batch',
            [],
            [
                'items'  => $items,
                'action' => $route,
                'label'  => 'tag',
            ]
        );

        if ($this->request->getMethod() == 'POST') {
            /** @var \Mautic\LeadBundle\Model\LeadModel $model */
            $model    = $this->getModel('lead');
            $tagModel = $this->getModel('lead.tag');
            $data     = $this->request->request->get('lead_batch', [], true);
            $ids      = json_decode($data['ids'], true);

            if (empty($data['add']) && empty($data['remove'])) {
                $form['add']->addError(
                    new FormError($this->translator->trans('le.lead.batch.tag.add.required', [], 'validators'))
                );
                $form['remove']->addError(
                    new FormError($this->translator->trans('le.lead.batch.tag.remove.required', [], 'validators'))
                );

                return $this->delegateView(
                    [
                        'viewParameters' => [
                            'form' => $form->createView(),
                        ],
                        'contentTemplate' => 'MauticLeadBundle:Batch:form.html.php',
                        'passthroughVars' => [
                            'activeLink'    => '#le_contact_index',
                            'leContent'     => 'leadBatch',
                            'route'         => $route,
                        ],
                    ]
                );
            }

            $entities = [];
            if (is_array($ids)) {
                $entities = $model->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'l.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }

            $count = 0;
            foreach ($entities as $lead) {
                if ($this->get('mautic.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $lead->getPermissionUser())) {
                    ++$count;

                    if (!empty($data['add'])) {
                        //$model->addToListOptIn($lead, $data['add']);
                        foreach ($data['add'] as $tagId) {
                            $tag = $tagModel->getEntity($tagId);
                            $lead->addTag($tag);
                        }
                    }

                    if (!empty($data['remove'])) {
                        //$model->removeFromListOptIn($lead, $data['remove']);
                        foreach ($data['remove'] as $tagId) {
                            $tag = $tagModel->getEntity($tagId);
                            $lead->removeTag($tag);
                        }
                    }
                    $model->saveEntity($lead);
                }
            }

            $this->addFlash(
                'le.lead.batch_leads_affected',
                [
                    'pluralCount' => $count,
                    '%count%'     => $count,
                ]
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        } else {
            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $form->createView(),
                    ],
                    'contentTemplate' => 'MauticLeadBundle:Batch:form.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#le_contact_index',
                        'leContent'     => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
    }

    /**
     * Bulk edit lead campaigns.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function batchCampaignsAction($objectId = 0)
    {
        /** @var \Mautic\CampaignBundle\Model\CampaignModel $campaignModel */
        $campaignModel = $this->getModel('campaign');
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->redirectToPricing();
        }
        if ($this->request->getMethod() == 'POST') {
            /** @var \Mautic\LeadBundle\Model\LeadModel $model */
            $model = $this->getModel('lead');
            $data  = $this->request->request->get('lead_batch', [], true);
            $ids   = json_decode($data['ids'], true);

            $entities = [];
            if (is_array($ids)) {
                $entities = $model->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'l.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }

            foreach ($entities as $key => $lead) {
                if (!$this->get('mautic.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $lead->getPermissionUser())) {
                    unset($entities[$key]);
                }
            }

            $add    = (!empty($data['add'])) ? $data['add'] : [];
            $remove = (!empty($data['remove'])) ? $data['remove'] : [];

            if ($count = count($entities)) {
                $campaigns = $campaignModel->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'c.id',
                                    'expr'   => 'in',
                                    'value'  => array_merge($add, $remove),
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );

                if (!empty($add)) {
                    foreach ($add as $cid) {
                        $campaignModel->addLeads($campaigns[$cid], $entities, true);
                    }
                }

                if (!empty($remove)) {
                    foreach ($remove as $cid) {
                        $campaignModel->removeLeads($campaigns[$cid], $entities, true);
                    }
                }
            }

            $this->addFlash(
                'le.lead.batch_leads_affected',
                [
                    'pluralCount' => $count,
                    '%count%'     => $count,
                ]
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        } else {
            // Get a list of campaigns
            $campaigns = $campaignModel->getPublishedCampaigns(true);
            $items     = [];
            foreach ($campaigns as $campaign) {
                $items[$campaign['id']] = $campaign['name'];
            }

            $route = $this->generateUrl(
                'le_contact_action',
                [
                    'objectAction' => 'batchCampaigns',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            'lead_batch',
                            [],
                            [
                                'items'  => $items,
                                'action' => $route,
                                'label'  => 'campaign',
                            ]
                        )->createView(),
                    ],
                    'contentTemplate' => 'MauticLeadBundle:Batch:form.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#le_contact_index',
                        'leContent'     => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
    }

    /**
     * Bulk add leads to the DNC list.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function batchDncAction($objectId = 0)
    {
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->redirectToPricing();
        }

        $route = $this->generateUrl(
            'le_contact_action',
            [
                'objectAction' => 'batchDnc',
            ]
        );
        $form = $this->createForm('lead_batch_dnc', [], ['action' => $route]);

        if ($this->request->getMethod() == 'POST') {
            $valid=false;
            if ($valid = $this->isFormValid($form)) {
                /** @var \Mautic\LeadBundle\Model\LeadModel $model */
                $model = $this->getModel('lead');
                $data  = $this->request->request->get('lead_batch_dnc', [], true);
                $ids   = json_decode($data['ids'], true);

                $entities = [];
                if (is_array($ids)) {
                    $entities = $model->getEntities(
                        [
                            'filter' => [
                                'force' => [
                                    [
                                        'column' => 'l.id',
                                        'expr'   => 'in',
                                        'value'  => $ids,
                                    ],
                                ],
                            ],
                            'ignore_paginator' => true,
                        ]
                    );
                }

                if ($count = count($entities)) {
                    $persistEntities = [];
                    foreach ($entities as $lead) {
                        if ($this->get('mautic.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $lead->getPermissionUser())) {
                            if ($model->addDncForLead($lead, 'email', $data['reason'], DoNotContact::MANUAL)) {
                                $persistEntities[] = $lead;
                            }
                        }
                    }

                    // Save entities
                    $model->saveEntities($persistEntities);
                }

                $this->addFlash(
                    'le.lead.batch_leads_affected',
                    [
                        'pluralCount' => $count,
                        '%count%'     => $count,
                    ]
                );

                return new JsonResponse(
                    [
                        'closeModal' => true,
                        'flashes'    => $this->getFlashContent(),
                    ]
                );
            } else {
                return $this->delegateView(
                    [
                        'viewParameters' => [
                            'form' => $form->createView(),
                        ],
                        'contentTemplate' => 'MauticLeadBundle:Batch:form.html.php',
                        'passthroughVars' => [
                            'activeLink'    => '#le_contact_index',
                            'leContent'     => 'leadBatch',
                            'route'         => $route,
                        ],
                    ]
                );
            }
        } else {
            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $form->createView(),
                    ],
                    'contentTemplate' => 'MauticLeadBundle:Batch:form.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#le_contact_index',
                        'leContent'     => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
    }

    /**
     * Bulk edit lead stages.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function batchStagesAction($objectId = 0)
    {
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->redirectToPricing();
        }
        if ($this->request->getMethod() == 'POST') {
            /** @var \Mautic\LeadBundle\Model\LeadModel $model */
            $model = $this->getModel('lead');
            $data  = $this->request->request->get('lead_batch_stage', [], true);
            $ids   = json_decode($data['ids'], true);

            $entities = [];
            if (is_array($ids)) {
                $entities = $model->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'l.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }

            $count = 0;
            foreach ($entities as $lead) {
                if ($this->get('mautic.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $lead->getPermissionUser())) {
                    ++$count;

                    if (!empty($data['addstage'])) {
                        $stageModel = $this->getModel('stage');

                        $stage = $stageModel->getEntity((int) $data['addstage']);
                        $model->addToStages($lead, $stage);
                    }

                    if (!empty($data['removestage'])) {
                        $stage = $stageModel->getEntity($data['removestage']);
                        $model->removeFromStages($lead, $stage);
                    }
                }
            }
            // Save entities
            $model->saveEntities($entities);
            $this->addFlash(
                'le.lead.batch_leads_affected',
                [
                    'pluralCount' => $count,
                    '%count%'     => $count,
                ]
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        } else {
            // Get a list of lists
            /** @var \Mautic\StageBundle\Model\StageModel $model */
            $model  = $this->getModel('stage');
            $stages = $model->getUserStages();
            $items  = [];
            foreach ($stages as $stage) {
                $items[$stage['id']] = $stage['name'];
            }

            $route = $this->generateUrl(
                'le_contact_action',
                [
                    'objectAction' => 'batchStages',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            'lead_batch_stage',
                            [],
                            [
                                'items'  => $items,
                                'action' => $route,
                            ]
                        )->createView(),
                    ],
                    'contentTemplate' => 'MauticLeadBundle:Batch:form.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#le_contact_index',
                        'leContent'     => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
    }

    /**
     * Bulk edit lead owner.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function batchOwnersAction($objectId = 0)
    {
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->redirectToPricing();
        }
        if ($this->request->getMethod() == 'POST') {
            /** @var \Mautic\LeadBundle\Model\LeadModel $model */
            $model = $this->getModel('lead');
            $data  = $this->request->request->get('lead_batch_owner', [], true);
            $ids   = json_decode($data['ids'], true);

            $entities = [];
            if (is_array($ids)) {
                $entities = $model->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'l.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }
            $count = 0;
            foreach ($entities as $lead) {
                if ($this->get('mautic.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $lead->getPermissionUser())) {
                    ++$count;

                    if (!empty($data['addowner'])) {
                        $userModel = $this->getModel('user');
                        $user      = $userModel->getEntity((int) $data['addowner']);
                        $lead->setOwner($user);
                    }
                }
            }
            // Save entities
            $model->saveEntities($entities);
            $this->addFlash(
                'le.lead.batch_leads_affected',
                [
                    'pluralCount' => $count,
                    '%count%'     => $count,
                ]
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        } else {
            $usermodel  =$this->getModel('user.user');
            $currentuser= $usermodel->getCurrentUserEntity();
            $usermodel->getRepository()->setCurrentUser($currentuser);
            $users = $usermodel->getRepository()->getUserList('', 0, 0, []);
            $items = [];
            foreach ($users as $user) {
                $items[$user['id']] = $user['firstName'].' '.$user['lastName'];
            }

            $route = $this->generateUrl(
                'le_contact_action',
                [
                    'objectAction' => 'batchOwners',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            'lead_batch_owner',
                            [],
                            [
                                'items'  => $items,
                                'action' => $route,
                            ]
                        )->createView(),
                    ],
                    'contentTemplate' => 'MauticLeadBundle:Batch:form.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#le_contact_index',
                        'leContent'     => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
    }

    /**
     * Bulk export contacts.
     *
     * @return array|JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function batchExportAction()
    {
        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'lead:leads:viewown',
                'lead:leads:viewother',
                'lead:leads:create',
                'lead:leads:editown',
                'lead:leads:editother',
                'lead:leads:deleteown',
                'lead:leads:deleteother',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['lead:leads:viewown'] && !$permissions['lead:leads:viewother']) {
            return $this->accessDenied();
        }

        /** @var \Mautic\LeadBundle\Model\LeadModel $model */
        $model       = $this->getModel('lead');
        $leadcount   = count($model->getEntities());
        if ($leadcount == 0) {
            return  $this->redirectToRoute('le_contact_index', ['leadcount' => 'true']);
        }
        $session    = $this->get('session');
        $search     = $session->get('mautic.lead.filter', '');
        $orderBy    = $session->get('mautic.lead.orderby', 'l.last_active');
        $orderByDir = $session->get('mautic.lead.orderbydir', 'DESC');
        $ids        = $this->request->get('ids');

        $filter     = ['string' => $search, 'force' => ''];
        $translator = $this->get('translator');
        $anonymous  = $translator->trans('le.lead.lead.searchcommand.isanonymous');
        $mine       = $translator->trans('mautic.core.searchcommand.ismine');
        $indexMode  = $session->get('mautic.lead.indexmode', 'list');
        $dataType   = $this->request->get('filetype', 'csv');

        if (!empty($ids)) {
            $filter['force'] = [
                [
                    'column' => 'l.id',
                    'expr'   => 'in',
                    'value'  => json_decode($ids, true),
                ],
            ];
        } else {
            if ($indexMode != 'list' || ($indexMode == 'list' && strpos($search, $anonymous) === false)) {
                //remove anonymous leads unless requested to prevent clutter
                $filter['force'] .= " !$anonymous";
            }

            if (!$permissions['lead:leads:viewother']) {
                $filter['force'] .= " $mine";
            }
        }

        $args = [
            'start'          => 0,
            'limit'          => 200,
            'filter'         => $filter,
            'orderBy'        => $orderBy,
            'orderByDir'     => $orderByDir,
            'withTotalCount' => true,
        ];

        $resultsCallback = function ($contact) {
            return $contact->getExportProfileFields();
        };

        $iterator = new IteratorExportDataModel($model, $args, $resultsCallback);

        return $this->exportResultsAs($iterator, $dataType, 'leads');
    }

    public function featuresAndIdeasAction()
    {
        return $this->delegateView(
            [
                'contentTemplate' => 'MauticLeadBundle:FeaturesAndIdeas:features_and_ideas.html.php',
                'passthroughVars' => [
                    'leContent'     => 'featuresandideas',
                ],
            ]
        );
    }

    public function isEmailStatSearch($search)
    {
        $commands = [
            $this->translator->trans('le.lead.lead.searchcommand.drip_scheduled'),
            $this->translator->trans('le.lead.lead.searchcommand.email_sent'),
            $this->translator->trans('le.lead.lead.searchcommand.email_read'),
            $this->translator->trans('le.lead.lead.searchcommand.email_click'),
            $this->translator->trans('le.lead.lead.searchcommand.email_queued'),
            $this->translator->trans('le.lead.lead.searchcommand.email_pending'),
            $this->translator->trans('le.lead.lead.searchcommand.email_failure'),
            $this->translator->trans('le.lead.lead.searchcommand.email_unsubscribe'),
            $this->translator->trans('le.lead.lead.searchcommand.email_bounce'),
            $this->translator->trans('le.lead.lead.searchcommand.email_spam'),
            $this->translator->trans('le.lead.drip.searchcommand.lead'),
            $this->translator->trans('le.lead.drip.searchcommand.sent'),
            $this->translator->trans('le.lead.drip.searchcommand.click'),
            $this->translator->trans('le.lead.drip.searchcommand.read'),
            $this->translator->trans('le.lead.drip.searchcommand.unsubscribe'),
            $this->translator->trans('le.lead.drip.searchcommand.bounce'),
            $this->translator->trans('le.lead.drip.searchcommand.failed'),
            $this->translator->trans('le.lead.lead.searchcommand.email_churn'),
            $this->translator->trans('le.lead.drip.searchcommand.churn'),
        ];
        if (strpos($search, ':') !== false) {
            $searchcont = explode(':', $search);
            if (in_array($searchcont[0], $commands)) {
                return true;
            }
        }

        return false;
    }

    public function checkEmailBody($emailBody)
    {
        $doc                      = new \DOMDocument();
        $doc->strictErrorChecking = false;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$emailBody);
        // Get body tag.
        $body = $doc->getElementsByTagName('body');
        if (!empty($body->item(0)->firstChild)) {
            return true;
        }

        return false;
    }
}
