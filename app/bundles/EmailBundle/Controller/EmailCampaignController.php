<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Controller;

use Mautic\CoreBundle\Controller\BuilderControllerTrait;
use Mautic\CoreBundle\Controller\FormController;
use Mautic\CoreBundle\Controller\FormErrorMessagesTrait;
use Mautic\CoreBundle\Helper\EmojiHelper;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Form\Type\ExampleSendType;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\LeadBundle\Controller\EntityContactsTrait;
use Mautic\LeadBundle\Model\ListModel;
use MauticPlugin\MauticCitrixBundle\Helper\CitrixHelper;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class EmailCampaignController extends FormController
{
    use BuilderControllerTrait;
    use FormErrorMessagesTrait;
    use EntityContactsTrait;

    /**
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = 1)
    {
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }

        $model = $this->getModel('email');

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'email:emails:viewown',
                'email:emails:viewother',
                'email:emails:create',
                'email:emails:editown',
                'email:emails:editother',
                'email:emails:deleteown',
                'email:emails:deleteother',
                'email:emails:publishown',
                'email:emails:publishother',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['email:emails:viewown'] && !$permissions['email:emails:viewother']) {
            return $this->accessDenied();
        }

        $this->setListFilters();

        $session = $this->get('session');

        $listFilters = [
            'filters' => [
                'placeholder' => $this->get('translator')->trans('le.email.filter.segment.category.placeholder'),
                'multiple'    => true,
            ],
        ];

        // Reset available groups
        $listFilters['filters']['groups'] = [];

        //set limits
        $limit = $session->get('mautic.email.limit', $this->coreParametersHelper->getParameter('default_pagelimit'));
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $this->request->get('search', $session->get('mautic.email.filter', ''));
        $session->set('mautic.email.filter', $search);
        $ismobile = InputHelper::isMobile();
        $filter   = [
            'string' => $search,
            'force'  => [
                ['column' => 'e.variantParent', 'expr' => 'isNull'],
                ['column' => 'e.translationParent', 'expr' => 'isNull'],
                ['column' => 'e.emailType', 'expr' => 'eq', 'value' => 'list'],
            ],
        ];
        if (!$permissions['email:emails:viewother']) {
            $filter['force'][] =
                ['column' => 'e.createdBy', 'expr' => 'eq', 'value' => $this->user->getId()];
        }

        //retrieve a list of Lead Lists
        /*$listFilters['filters']['groups']['mautic.core.filter.lists'] = [
            'options' => $this->getModel('lead.list')->getUserLists(),
            'prefix'  => 'list',
        ];*/
        $currentFilters = $session->get('mautic.email.list_filters', []);
        //retrieve a titles of Category
        $listFilters['filters']['groups']['mautic.core.filter.category'] = [
            'options' => $this->getModel('category.category')->getLookupResults('email'),
            'prefix'  => 'category',
            'values'  => (empty($currentFilters) || !isset($currentFilters['category'])) ? [] : array_values($currentFilters['category']),
        ];
        //retrieve a list of themes
        //$listFilters['filters']['groups']['mautic.core.filter.themes'] = [
        //    'options' => $this->factory->getInstalledThemes('email'),
        //    'prefix'  => 'theme',
        //];

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
        $session->set('mautic.email.list_filters', $currentFilters);

        if (!empty($currentFilters)) {
            $listIds = $catIds = $templates = [];
            foreach ($currentFilters as $type => $typeFilters) {
                switch ($type) {
                    case 'list':
                        $key = 'lists';
                        break;
                    case 'category':
                        $key = 'categories';
                        break;
                    case 'theme':
                        $key = 'themes';
                        break;
                }

                $listFilters['filters']['groups']['mautic.core.filter.'.$key]['values'] = $typeFilters;

                foreach ($typeFilters as $fltr) {
                    switch ($type) {
                        case 'list':
                            $listIds[] = (int) $fltr;
                            break;
                        case 'category':
                            $catIds[] = (int) $fltr;
                            break;
                        case 'theme':
                            $templates[] = $fltr;
                            break;
                    }
                }
            }

            if (!empty($listIds)) {
                $filter['force'][] = ['column' => 'l.id', 'expr' => 'in', 'value' => $listIds];
                $ignoreListJoin    = false;
            }

            if (!empty($catIds)) {
                $filter['force'][] = ['column' => 'c.id', 'expr' => 'in', 'value' => $catIds];
            }

            if (!empty($templates)) {
                $filter['force'][] = ['column' => 'e.template', 'expr' => 'in', 'value' => $templates];
            }
        }

        $orderBy    = $session->get('mautic.email.orderby', 'e.subject');
        $orderByDir = $session->get('mautic.email.orderbydir', 'DESC');

        $emails = $model->getEntities(
            [
                'start'          => $start,
                'limit'          => $limit,
                'filter'         => $filter,
                'orderBy'        => $orderBy,
                'orderByDir'     => $orderByDir,
                'ignoreListJoin' => $ignoreListJoin,
            ], true
        );

        $count = count($emails);
        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            if ($count === 1) {
                $lastPage = 1;
            } else {
                $lastPage = (floor($count / $limit)) ?: 1;
            }

            $session->set('mautic.email.page', $lastPage);
            $returnUrl = $this->generateUrl('le_email_campaign_index', ['page' => $lastPage]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $lastPage],
                    'contentTemplate' => 'MauticEmailBundle:EmailCampaign:index',
                    'passthroughVars' => [
                        'activeLink'    => '#le_email_campaign_index',
                        'leContent'     => 'email',
                    ],
                ]
            );
        }
        $session->set('mautic.email.page', $page);
        $emailBlockDetails = $model->getEmailBlocks();

        return $this->delegateView(
            [
                'viewParameters' => [
                    'searchValue'      => $search,
                    'filters'          => $listFilters,
                    'items'            => $emails,
                    'totalItems'       => $count,
                    'page'             => $page,
                    'limit'            => $limit,
                    'tmpl'             => $this->request->get('tmpl', 'index'),
                    'permissions'      => $permissions,
                    'model'            => $model,
                    'actionRoute'      => 'le_email_campaign_action',
                    'indexRoute'       => 'le_email_campaign_index',
                    'headerTitle'      => 'le.lead.emails',
                    'translationBase'  => 'mautic.email.broadcast',
                    'emailBlockDetails'=> $emailBlockDetails,
                    'notificationemail'=> false,
                    'ismobile'         => $ismobile,
                ],
                'contentTemplate' => 'MauticEmailBundle:Email:list.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_email_campaign_index',
                    'leContent'     => 'email',
                    'route'         => $this->generateUrl('le_email_campaign_index', ['page' => $page]),
                ],
            ]
        );
    }

    /**
     * Loads a specific form into the detailed panel.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($objectId)
    {
        /** @var \Mautic\EmailBundle\Model\EmailModel $model */
        $model    = $this->getModel('email');
        $security = $this->get('mautic.security');
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }
        /** @var \Mautic\EmailBundle\Entity\Email $email */
        $email = $model->getEntity($objectId);
        //set the page we came from
        $page = $this->get('session')->get('mautic.email.page', 1);

        // Init the date range filter form
        $dateRangeValues = $this->request->get('daterange', []);
        $action          = $this->generateUrl('le_email_campaign_action', ['objectAction' => 'view', 'objectId' => $objectId]);
        $dateRangeForm   = $this->get('form.factory')->create('daterange', $dateRangeValues, ['action' => $action]);

        if ($email === null) {
            //set the return URL
            $returnUrl = $this->generateUrl('le_email_campaign_index', ['page' => $page]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'MauticEmailBundle:EmailCampaign:index',
                    'passthroughVars' => [
                        'activeLink'    => '#le_email_campaign_index',
                        'leContent'     => 'email',
                    ],
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'le.email.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ]
            );
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'email:emails:viewown',
            'email:emails:viewother',
            $email->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        }

        //get A/B test information
        list($parent, $children) = $email->getVariants();
        $properties              = [];
        $variantError            = false;
        $weight                  = 0;
        if (count($children)) {
            foreach ($children as $c) {
                $variantSettings = $c->getVariantSettings();

                if (is_array($variantSettings) && isset($variantSettings['winnerCriteria'])) {
                    if ($c->isPublished()) {
                        if (!isset($lastCriteria)) {
                            $lastCriteria = $variantSettings['winnerCriteria'];
                        }

                        //make sure all the variants are configured with the same criteria
                        if ($lastCriteria != $variantSettings['winnerCriteria']) {
                            $variantError = true;
                        }

                        $weight += $variantSettings['weight'];
                    }
                } else {
                    $variantSettings['winnerCriteria'] = '';
                    $variantSettings['weight']         = 0;
                }

                $properties[$c->getId()] = $variantSettings;
            }

            $properties[$parent->getId()]['weight']         = 100 - $weight;
            $properties[$parent->getId()]['winnerCriteria'] = '';
        }

        $abTestResults = [];
        $criteria      = $model->getBuilderComponents($email, 'abTestWinnerCriteria');
        if (!empty($lastCriteria) && empty($variantError)) {
            if (isset($criteria['criteria'][$lastCriteria])) {
                $testSettings = $criteria['criteria'][$lastCriteria];

                $args = [
                    'factory'    => $this->factory,
                    'email'      => $email,
                    'parent'     => $parent,
                    'children'   => $children,
                    'properties' => $properties,
                ];

                //execute the callback
                if (is_callable($testSettings['callback'])) {
                    if (is_array($testSettings['callback'])) {
                        $reflection = new \ReflectionMethod($testSettings['callback'][0], $testSettings['callback'][1]);
                    } elseif (strpos($testSettings['callback'], '::') !== false) {
                        $parts      = explode('::', $testSettings['callback']);
                        $reflection = new \ReflectionMethod($parts[0], $parts[1]);
                    } else {
                        $reflection = new \ReflectionMethod(null, $testSettings['callback']);
                    }

                    $pass = [];
                    foreach ($reflection->getParameters() as $param) {
                        if (isset($args[$param->getName()])) {
                            $pass[] = $args[$param->getName()];
                        } else {
                            $pass[] = null;
                        }
                    }
                    $abTestResults = $reflection->invokeArgs($this, $pass);
                }
            }
        }

        //get related translations
        list($translationParent, $translationChildren) = $email->getTranslations();

        // Prepare stats for bargraph
        if ($chartStatsSource = $this->request->query->get('stats', false)) {
            $includeVariants = ('all' == $chartStatsSource);
        } else {
            $includeVariants = (($email->isVariant() && $parent === $email) || ($email->isTranslation() && $translationParent === $email));
        }
        //if ($email->getEmailType() == 'template') {
        $stats = $model->getEmailGeneralStats(
                $email,
                $includeVariants,
                null,
                new \DateTime($dateRangeForm->get('date_from')->getData()),
                new \DateTime($dateRangeForm->get('date_to')->getData())
            );
        /*} else {
            $stats = $model->getEmailListStats(
                $email,
                $includeVariants,
                new \DateTime($dateRangeForm->get('date_from')->getData()),
                new \DateTime($dateRangeForm->get('date_to')->getData())
            );
        }*/

        $statsDevices = $model->getEmailDeviceStats(
            $email,
            $includeVariants,
            new \DateTime($dateRangeForm->get('date_from')->getData()),
            new \DateTime($dateRangeForm->get('date_to')->getData())
        );

        // Audit Log
        $logs = $this->getModel('core.auditLog')->getLogForObject('email', $email->getId(), $email->getDateAdded());

        // Get click through stats
        $trackableLinks         = $model->getEmailClickStats($email->getId());
        $emailStats             = $model->getCustomEmailStats($email->getId());
        $last10openleads        = $model->getLeadsBasedonAction($this->translator->trans('le.lead.lead.searchcommand.email_read').':'.$email->getId());
        $last10clickleads       = $model->getLeadsBasedonAction($this->translator->trans('le.lead.lead.searchcommand.email_click').':'.$email->getId());
        $last10unsubscribeleads = $model->getLeadsBasedonAction($this->translator->trans('le.lead.lead.searchcommand.email_unsubscribe').':'.$email->getId());
        $last10bounceleads      = $model->getLeadsBasedonAction($this->translator->trans('le.lead.lead.searchcommand.email_bounce').':'.$email->getId());
        $last10churns           = $model->getLeadsBasedonAction($this->translator->trans('le.lead.lead.searchcommand.email_churn').':'.$email->getId());
        $last10fails            = $model->getLeadsBasedonAction($this->translator->trans('le.lead.lead.searchcommand.email_failure').':'.$email->getId());

        return $this->delegateView(
            [
                'returnUrl' => $this->generateUrl(
                    'le_email_campaign_action',
                    [
                        'objectAction' => 'view',
                        'objectId'     => $email->getId(),
                    ]
                ),
                'viewParameters' => [
                    'email'        => $email,
                    'stats'        => $stats,
                    'statsDevices' => $statsDevices,
                    'showAllStats' => $includeVariants,
                    'trackables'   => $trackableLinks,
                    'pending'      => $model->getPendingLeads($email, null, true),
                    'logs'         => $logs,
                    'isEmbedded'   => $this->request->get('isEmbedded') ? $this->request->get('isEmbedded') : false,
                    'variants'     => [
                        'parent'     => $parent,
                        'children'   => $children,
                        'properties' => $properties,
                        'criteria'   => $criteria['criteria'],
                    ],
                    'translations' => [
                        'parent'   => $translationParent,
                        'children' => $translationChildren,
                    ],
                    'permissions' => $security->isGranted(
                        [
                            'email:emails:viewown',
                            'email:emails:viewother',
                            'email:emails:create',
                            'email:emails:editown',
                            'email:emails:editother',
                            'email:emails:deleteown',
                            'email:emails:deleteother',
                            'email:emails:publishown',
                            'email:emails:publishother',
                        ],
                        'RETURN_ARRAY'
                    ),
                    'abTestResults' => $abTestResults,
                    'security'      => $security,
                    'previewUrl'    => $this->generateUrl(
                        'le_email_preview',
                        ['objectId' => $email->getId()],
                        true
                    ),
                    'contacts' => $this->forward(
                        'MauticEmailBundle:Email:contacts',
                        [
                            'objectId'   => $email->getId(),
                            'page'       => $this->get('session')->get('mautic.email.contact.page', 1),
                            'ignoreAjax' => true,
                        ]
                    )->getContent(),
                    'dateRangeForm'    => $dateRangeForm->createView(),
                    'actionRoute'      => 'le_email_campaign_action',
                    'indexRoute'       => 'le_email_campaign_index',
                    'notificationemail'=> false,
                    'emailStats'       => $emailStats,
                    'openLeads'        => $last10openleads,
                    'clickLeads'       => $last10clickleads,
                    'unsubscribeLeads' => $last10unsubscribeleads,
                    'bounceLeads'      => $last10bounceleads,
                    'emailChurn'       => $last10churns,
                    'emailfailed'      => $last10fails,
                ],
                'contentTemplate' => 'MauticEmailBundle:Email:details.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_email_campaign_index',
                    'leContent'     => 'email',
                ],
            ]
        );
    }

    /**
     * Generates new form and processes post data.
     *
     * @param \Mautic\EmailBundle\Entity\Email $entity
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction($entity = null, $isClone=false)
    {
        $model = $this->getModel('email');
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }
        if (!($entity instanceof Email)) {
            /** @var \Mautic\EmailBundle\Entity\Email $entity */
            $entity = $model->getEntity();
        }

        $method  = $this->request->getMethod();
        $session = $this->get('session');
        if (!$this->get('mautic.security')->isGranted('email:emails:create')) {
            return $this->accessDenied();
        }
        $unsubscribeFooter = $entity->getUnsubscribeText();
        $PostalAddress     = $entity->getPostalAddress();
        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator= $this->get('mautic.configurator');

        $params          = $configurator->getParameters();
        $maileruser      = $params['mailer_user'];
        $emailpassword   = $params['mailer_password'];
        if (isset($params['mailer_amazon_region'])) {
            $region                = $params['mailer_amazon_region'];
        } else {
            $region='';
        }
        //$region          = $params['mailer_amazon_region'];
//        $fromname     ='';
//        $fromadress   ='';
//        $defaultsender=$model->getDefaultSenderProfile();
//        if (sizeof($defaultsender) > 0) {
//            $fromname  =$defaultsender[0];
//            $fromadress=$defaultsender[1];
//        }
        $fromname         = $params['mailer_from_name'];
        $fromadress       = $params['mailer_from_email'];
        $fromName         = $entity->getFromName();
        $fromAdress       = $entity->getFromAddress();
        $mailertransport  = $params['mailer_transport'];
        $unsubscribeText  = $params['footer_text'];
        $postaladdress    = $params['postal_address'];
        if (empty($fromName)) {
            $entity->setFromName($fromname);
        }
        if (empty($fromAdress)) {
            $entity->setFromAddress($fromadress);
        }
        if (empty($unsubscribeFooter)) {
            $entity->setUnsubscribeText($unsubscribeText);
        }
        if (empty($PostalAddress)) {
            $entity->setPostalAddress($postaladdress);
        }
//        $emailValidator = $this->factory->get('mautic.validator.email');
//        if ($mailertransport == 'le.transport.amazon') {
//            $emails = $emailValidator->getVerifiedEmailList($maileruser, $emailpassword, $region);
//            if (!empty($emails)) {
//                $model->upAwsEmailVerificationStatus($emails);
//            }
//        }
        //set the page we came from
        $page         = $session->get('mautic.email.page', 1);
        $action       = $this->generateUrl('le_email_campaign_action', ['objectAction' => 'new']);
        $updateSelect = ($method == 'POST')
            ? $this->request->request->get('emailform[updateSelect]', false, true)
            : $this->request->get(
                'updateSelect',
                false
            );
        if ($updateSelect) {
            // Force type to template
            $entity->setEmailType('list');
        } elseif (true) {
            $entity->setEmailType('list');
        }

        //create the form
        $form = $model->createForm($entity, $this->get('form.factory'), $action, ['update_select' => $updateSelect, 'isEmailTemplate' => false, 'isDripEmail' => false, 'isShortForm' => false]);

        ///Check for a submitted form and process it
        if ($method == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                $formData = $this->request->request->get('emailform');
                if ($valid = $this->isFormValid($form) && $this->isFormValidForWebinar($formData, $form, $entity)) {
                    //auto value assign to utm tags
                    $currentutmtags=$entity->getUtmTags();
                    $currentsubject=$entity->getSubject();
                    $currentname   =$entity->getName();
                    if ($entity->getBeeJSON() == 'RichTextEditor') {
                        $entity->setTemplate('');
                    }

                    if (!empty($formData['unsubscribe_text'])) {
                        $entity->setUnsubscribeText($formData['unsubscribe_text']);
                    }
                    if ($entity->getGoogletags()) {
                        if (empty($currentutmtags['utmSource'])) {
                            $currentutmtags['utmSource'] = 'AnyFunnels';
                        }
                        if (empty($currentutmtags['utmMedium'])) {
                            $currentutmtags['utmMedium'] = 'email';
                        }
                        if (empty($currentutmtags['utmCampaign'])) {
                            $currentutmtags['utmCampaign'] = $currentname;
                        }
                        if (empty($currentutmtags['utmContent'])) {
                            $currentutmtags['utmContent'] = $currentsubject;
                        }

                        $entity->setUtmTags($currentutmtags);
                    }
                    $assets         = $form['assetAttachments']->getData();
                    $attachmentSize = $this->getModel('asset')->getTotalFilesize($assets);
                    if ($attachmentSize != 0 && $attachmentSize == 'failed') {
                        $delassets=$assets;
                        foreach ($assets as $asset) {
                            $entity->removeAssetAttachment($asset);
                        }
                        $assets = $this->getassets($delassets);
                        foreach ($assets as $asset) {
                            $entity->addAssetAttachment($asset);
                        }
                    }
                    //form is valid so process the data
                    $model->saveEntity($entity);

                    $this->addFlash(
                        'mautic.core.notice.created',
                        [
                            '%name%'      => $entity->getName(),
                            '%menu_link%' => 'le_email_campaign_index',
                            '%url%'       => $this->generateUrl(
                                'le_email_campaign_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ]
                    );

                    if ($form->get('buttons')->get('save')->isClicked()) {
                        $viewParameters = [
                            'objectAction' => 'view',
                            'objectId'     => $entity->getId(),
                        ];
                        $returnUrl = $this->generateUrl('le_email_campaign_action', $viewParameters);
                        $template  = 'MauticEmailBundle:EmailCampaign:view';
                    } elseif ($valid && $form->get('buttons')->get('sendtest')->isClicked()) {
                        //return edit view so that all the session stuff is loaded
                        //return $this->editAction($entity->getId(), true);
                        $id = $entity->getId();

                        return $this->sendAction($id);
                    }
                }
            } else {
                $viewParameters = [
                'page' => $page,
            ];
                $returnUrl      = $this->generateUrl('le_email_campaign_index', $viewParameters);
                $template       = 'MauticEmailBundle:EmailCampaign:index';
                //clear any modified content
                $session->remove('mautic.emailbuilder.'.$entity->getSessionId().'.content');
            }

            $passthrough = [
                'activeLink'    => 'le_email_campaign_index',
                'leContent'     => 'email',
            ];

            // Check to see if this is a popup
            if (isset($form['updateSelect'])) {
                $template    = false;
                $passthrough = array_merge(
                    $passthrough,
                    [
                        'updateSelect' => $form['updateSelect']->getData(),
                        'id'           => $entity->getId(),
                        'name'         => $entity->getName(),
                        'group'        => $entity->getLanguage(),
                    ]
                );
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                        'passthroughVars' => $passthrough,
                    ]
                );
            }
        }

        $slotTypes   = $model->getBuilderComponents($entity, 'slotTypes');
        $sections    = $model->getBuilderComponents($entity, 'sections');
        $sectionForm = $this->get('form.factory')->create('builder_section');
        $routeParams = [
            'objectAction' => 'new',
        ];
        if ($updateSelect) {
            $routeParams['updateSelect'] = $updateSelect;
            $routeParams['contentOnly']  = 1;
        }
        $ismobile = InputHelper::isMobile();
        if ($ismobile) {
            return $this->editDenied($this->generateUrl('le_email_campaign_index'));
        }
        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'page:preference_center:viewown',
                'page:preference_center:viewother',
            ],
            'RETURN_ARRAY'
        );
        $verifiedemail = $model->getVerifiedEmailAddress();
        $groupFilters  = [
            'template_filters' => [
                'multiple'    => false,
                'onchange'    => 'Le.filterBeeTemplates()',
            ],
        ];

        $groupFilters['template_filters']['groups'] = [];

        $groupFilters['template_filters']['groups']['']  = [
            'options' => $model->getEmailTemplateGroupNames(),
        ];
        $formThemes = ['MauticEmailBundle:FormTheme\Email', 'MauticLeadBundle:FormTheme\Filter'];

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'                        => $this->setFormTheme($form, 'MauticEmailBundle:Email:form.html.php', $formThemes),
                    'isVariant'                   => $entity->isVariant(true),
                    'email'                       => $entity,
                    'slots'                       => $this->buildSlotForms($slotTypes),
                    'sections'                    => $this->buildSlotForms($sections),
                    'themes'                      => $this->factory->getInstalledThemes('email', true),
                    'beetemplates'                => $this->factory->getInstalledBeeTemplates('email'),
                    'builderAssets'               => trim(preg_replace('/\s+/', ' ', $this->getAssetsForBuilder())), // strip new lines
                    'sectionForm'                 => $sectionForm->createView(),
                    'updateSelect'                => $updateSelect,
                    'permissions'                 => $permissions,
                    'isClone'                     => $isClone,
                    'isMobile'                    => $ismobile,
                    'verifiedemail'               => $verifiedemail,
                    'mailertransport'             => $mailertransport,
                    'template_filters'            => $groupFilters,
                ],
                'contentTemplate' => 'MauticEmailBundle:Email:form.html.php',
                'passthroughVars' => [
                    'activeLink'      => '#le_email_campaign_index',
                    'leContent'       => 'email',
                    'updateSelect'    => $updateSelect,
                    'route'           => $this->generateUrl('le_email_campaign_action', $routeParams),
                    'validationError' => $this->getFormErrorForBuilder($form),
                ],
            ]
        );
    }

    public function quickaddAction($objectId)
    {
        /**$isStateAlive=$this->get('le.helper.statemachine')->isStateAlive('Customer_Sending_Domain_Not_Configured');
        if ($isStateAlive) {
            $configurl=$this->factory->getRouter()->generate('le_sendingdomain_action');
            $this->addFlash($this->translator->trans('le.email.config.mailer.status.report', ['%url%' => $configurl]));

            return $this->postActionRedirect(
                [
                    'passthroughVars' => [
                        'closeModal' => 1,
                        'route'      => false,
                    ],
                ]
            );
        }*/
        $isBeeEditor = $objectId;
        /** @var EmailModel $model */
        $model        = $this->getModel('email');
        $email        = $model->getEntity();
        $action       = $this->generateUrl('le_email_campaign_action', ['objectAction' => 'quickadd', 'objectId' => $objectId]);
        $templatename = '';
        $beeJson      = 'RichTextEditor';
        if ($isBeeEditor) {
            $templatename = 'blank';
            $beeJson      = '';
        }

        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator    = $this->get('mautic.configurator');
        $params          = $configurator->getParameters();
        $unsubscribeText = $params['footer_text'];
        $postaladdress   = $params['postal_address'];

        //create the form
        $form = $model->createForm($email, $this->get('form.factory'), $action, ['update_select' => false, 'isEmailTemplate' => false, 'isDripEmail' => false, 'isShortForm' => true]);
        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $email->setEmailType('list');
                    $email->setTemplate($templatename);
                    $email->setBeeJSON($beeJson);
                    $email->setUnsubscribeText($unsubscribeText);
                    $email->setPostalAddress($postaladdress);
                    $model->saveEntity($email);
                    $viewParameters = [];
                    $returnUrl      = $this->generateUrl('le_email_campaign_action', ['objectAction' => 'edit', 'objectId' => $email->getId()]);
                    $template       = 'MauticEmailBundle:Email:edit';

                    return $this->delegateRedirect($returnUrl);
                }
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'        => $form->createView(),
                    'email'       => $email,
                ],
                'contentTemplate' => 'MauticEmailBundle:Email:quickadd.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_email_campaign_index',
                    'leContent'     => 'email',
                    'route'         => $this->generateUrl(
                        'le_email_campaign_action',
                        [
                            'objectAction' => 'new',
                        ]
                    ),
                ],
            ]
        );
    }

    public function sendTestAction($id)
    {
        $model         = $this->getModel('email');
        $entity        = $model->getEntity($id);
        $pending       = $model->getPendingLeads($entity, null, true);
        $action        = $this->generateUrl('le_email_campaign_action', ['objectAction' => 'sendTest', 'objectId' => $id]);

        return $this->delegateView(
            [
                'viewParameters' => [
                    'headerTitle'      => 'Send Test',
                    'model'            => $model,
                    'id'               => $id,
                    'pending'          => $pending,
                ],
                'contentTemplate' => 'MauticEmailBundle:Email:send.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_email_campaign_index',
                    'route'         => $action,
                ],
            ]
        );
    }

    /**
     * @param      $objectId
     * @param bool $ignorePost
     * @param bool $forceTypeSelection
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction($objectId, $ignorePost = false, $forceTypeSelection = false)
    {
        /** @var \Mautic\EmailBundle\Model\EmailModel $model */
        $model  = $this->getModel('email');
        $method = $this->request->getMethod();
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }
        $entity     = $model->getEntity($objectId);
        $lastutmtags=$entity->getUtmTags();
        $googletags =$entity->getGoogletags();
        $lastsubject=$entity->getSubject();
        $lastname   =$entity->getName();
        $session    = $this->get('session');
        $page       = $this->get('session')->get('mautic.email.page', 1);
        $fromName   = $entity->getFromName();
        $fromAdress = $entity->getFromAddress();
        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator= $this->get('mautic.configurator');

        $params          = $configurator->getParameters();
        $maileruser      = $params['mailer_user'];
        $emailpassword   = $params['mailer_password'];
        if (isset($params['mailer_amazon_region'])) {
            $region                = $params['mailer_amazon_region'];
        } else {
            $region='';
        }
        //$region          = $params['mailer_amazon_region'];
//        $fromname     ='';
//        $fromadress   ='';
//        $defaultsender=$model->getDefaultSenderProfile();
//        if (sizeof($defaultsender) > 0) {
//            $fromname  =$defaultsender[0];
//            $fromadress=$defaultsender[1];
//        }
        $fromname         = $params['mailer_from_name'];
        $fromadress       = $params['mailer_from_email'];
        $mailertransport  =$params['mailer_transport'];
        if (empty($fromName)) {
            $entity->setFromName($fromname);
        }
        if (empty($fromAdress)) {
            $entity->setFromAddress($fromadress);
        }
//        $emailValidator = $this->factory->get('mautic.validator.email');
//        if ($mailertransport == 'le.transport.amazon') {
//            $emails = $emailValidator->getVerifiedEmailList($maileruser, $emailpassword, $region);
//            if (!empty($emails)) {
//                $model->upAwsEmailVerificationStatus($emails);
//            }
//        }
        //set the return URL
        $returnUrl = $this->generateUrl('le_email_campaign_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticEmailBundle:EmailCampaign:index',
            'passthroughVars' => [
                'activeLink'    => 'le_email_campaign_index',
                'leContent'     => 'email',
            ],
        ];

        //not found
        if ($entity === null) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'le.email.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'email:emails:editown',
            'email:emails:editother',
            $entity->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        } elseif ($model->isLocked($entity)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $entity, 'email');
        }

        //Create the form
        $action = $this->generateUrl('le_email_campaign_action', ['objectAction' => 'edit', 'objectId' => $objectId]);

        $updateSelect = ($method == 'POST')
            ? $this->request->request->get('emailform[updateSelect]', false, true)
            : $this->request->get(
                'updateSelect',
                false
            );
        if ($updateSelect) {
            // Force type to template
            $entity->setEmailType('list');
        }
        $entity->setGoogleTags(false);
        /** @var Form $form */
        $form = $model->createForm($entity, $this->get('form.factory'), $action, ['update_select' => $updateSelect, 'isEmailTemplate' => false, 'isDripEmail' => false, 'isShortForm' => false]);

        ///Check for a submitted form and process it
        if (!$ignorePost && $method == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                $formData = $this->request->request->get('emailform');
                if ($valid = $this->isFormValid($form) && $this->isFormValidForWebinar($formData, $form, $entity) && $this->checkDMARKinDefaultSendingDomain($form)) {
                    //auto value assign to utm tags
                    $currentutmtags=$entity->getUtmTags();
                    $currentsubject=$entity->getSubject();
                    $currentname   =$entity->getName();
                    if ($entity->getBeeJSON() == 'RichTextEditor') {
                        $entity->setTemplate('');
                    }
                    if (!empty($formData['unsubscribe_text'])) {
                        $entity->setUnsubscribeText($formData['unsubscribe_text']);
                    }
                    if ($entity->getGoogletags()) {
                        if (empty($currentutmtags['utmSource'])) {
                            $currentutmtags['utmSource'] = 'AnyFunnels';
                        }
                        if (empty($currentutmtags['utmMedium'])) {
                            $currentutmtags['utmMedium'] = 'email';
                        }
                        if (empty($currentutmtags['utmCampaign'])) {
                            $currentutmtags['utmCampaign'] = $currentname;
                        } elseif ($currentname != $lastname && $currentutmtags['utmCampaign'] == $lastname) {
                            $currentutmtags['utmCampaign'] = $currentname;
                        }
                        if (empty($currentutmtags['utmContent'])) {
                            $currentutmtags['utmContent'] = $currentsubject;
                        } elseif ($currentsubject != $lastsubject && $currentutmtags['utmContent'] == $lastsubject) {
                            $currentutmtags['utmContent'] = $currentsubject;
                        }
                    } else {
                        $currentutmtags['utmSource']  = null;
                        $currentutmtags['utmMedium']  = null;
                        $currentutmtags['utmCampaign']=null;
                        $currentutmtags['utmContent'] =null;
                    }
                    $entity->setUtmTags($currentutmtags);
                    $assets         = $form['assetAttachments']->getData();
                    $attachmentSize = $this->getModel('asset')->getTotalFilesize($assets);
                    if ($attachmentSize != 0 && $attachmentSize == 'failed') {
                        $tmpassets=$assets;
                        foreach ($assets as $asset) {
                            $entity->removeAssetAttachment($asset);
                        }
                        $assets = $this->getassets($tmpassets);
                        foreach ($assets as $asset) {
                            $entity->addAssetAttachment($asset);
                        }
                    }
                    //$isStateAlive       =$this->get('le.helper.statemachine')->isStateAlive('Customer_Sending_Domain_Not_Configured');
                    $isUpdateFlashNeeded=true;
                    $sendBtnClicked     =$form->get('buttons')->get('sendtest')->isClicked();
                    if (!$sendBtnClicked) {
                        /**if ($entity->isPublished() && $isStateAlive) {
                            $isUpdateFlashNeeded=false;
                            $configurl          =$this->factory->getRouter()->generate('le_sendingdomain_action');
                            $entity->setIsPublished(false);
                            $this->addFlash($this->translator->trans('le.email.config.mailer.publish.status.report', ['%url%' => $configurl, '%module%' => 'broadcast']));
                        }*/
                    } else {
                        $isUpdateFlashNeeded=false;
                    }

                    //form is valid so process the data
                    $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());
                    if ($isUpdateFlashNeeded) {
                        $this->addFlash(
                            'mautic.core.notice.updated',
                            [
                                '%name%'      => $entity->getName(),
                                '%menu_link%' => 'le_email_campaign_index',
                                '%url%'       => $this->generateUrl(
                                    'le_email_campaign_action',
                                    [
                                        'objectAction' => 'edit',
                                        'objectId'     => $entity->getId(),
                                    ]
                                ),
                            ],
                            'warning'
                        );
                    }
                }
            } else {
                //clear any modified content
                $session->remove('mautic.emailbuilder.'.$objectId.'.content');
                //unlock the entity
                $model->unlockEntity($entity);
            }

            $template    = 'MauticEmailBundle:EmailCampaign:view';
            $passthrough = [
                'activeLink'    => 'le_email_campaign_index',
                'leContent'     => 'email',
            ];

            // Check to see if this is a popup
            if (isset($form['updateSelect'])) {
                $template    = false;
                $passthrough = array_merge(
                    $passthrough,
                    [
                        'updateSelect' => $form['updateSelect']->getData(),
                        'id'           => $entity->getId(),
                        'name'         => $entity->getName(),
                        'group'        => $entity->getLanguage(),
                    ]
                );
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $entity->getId(),
                ];

                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars,
                        [
                            'returnUrl'       => $this->generateUrl('le_email_campaign_action', $viewParameters),
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => $template,
                            'passthroughVars' => $passthrough,
                        ]
                    )
                );
            } elseif ($valid && $form->get('buttons')->get('sendtest')->isClicked()) {
                // Rebuild the form in the case apply is clicked so that DEC content is properly populated if all were removed
                //$form = $model->createForm($entity, $this->get('form.factory'), $action, ['update_select' => $updateSelect, 'isEmailTemplate' => false, 'isDripEmail' => false, 'isShortForm' => false]);
                $id = $entity->getId();

                return $this->sendAction($id);
            }
        } else {
            //lock the entity
            $model->lockEntity($entity);

            //clear any modified content
            $session->remove('mautic.emailbuilder.'.$objectId.'.content');

            // Set to view content
            $template = $entity->getTemplate();
            if (empty($template)) {
                $content = $entity->getCustomHtml();
                $form['customHtml']->setData($content);
            }
        }

        $assets         = $form['assetAttachments']->getData();
        $attachmentSize = $this->getModel('asset')->getTotalFilesize($assets);

        $slotTypes   = $model->getBuilderComponents($entity, 'slotTypes');
        //  file_put_contents("/var/www/mautic/app/cache/response.txt","Types:".print_r($slotTypes )."\n",FILE_APPEND);
        $sections    = $model->getBuilderComponents($entity, 'sections');
        //  file_put_contents("/var/www/mautic/app/cache/response.txt","Sections:".print_r($sections )."\n",FILE_APPEND);
        $sectionForm = $this->get('form.factory')->create('builder_section');
        $routeParams = [
            'objectAction' => 'edit',
            'objectId'     => $entity->getId(),
        ];
        if ($updateSelect) {
            $routeParams['updateSelect'] = $updateSelect;
            $routeParams['contentOnly']  = 1;
        }
        $ismobile      = InputHelper::isMobile();
        if ($entity->getBeeJSON() != 'RichTextEditor' && $ismobile) {
            return $this->editDenied($this->generateUrl(
                'le_email_campaign_action',
                [
                    'objectAction' => 'view',
                    'objectId'     => $entity->getId(),
                ]
            ));
        }

        $verifiedemail = $model->getVerifiedEmailAddress();
        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'page:preference_center:viewown',
                'page:preference_center:viewother',
            ],
            'RETURN_ARRAY'
        );
        $groupFilters = [
            'template_filters' => [
                'multiple'    => false,
                'onchange'    => 'Le.filterBeeTemplates()',
            ],
        ];

        $groupFilters['template_filters']['groups'] = [];

        $groupFilters['template_filters']['groups']['']  = [
            'options' => $model->getEmailTemplateGroupNames(),
        ];
        $formThemes = ['MauticEmailBundle:FormTheme\Email', 'MauticLeadBundle:FormTheme\Filter'];

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'                         => $this->setFormTheme($form, 'MauticEmailBundle:Email:form.html.php', $formThemes),
                    'isVariant'                    => $entity->isVariant(true),
                    'slots'                        => $this->buildSlotForms($slotTypes),
                    'sections'                     => $this->buildSlotForms($sections),
                    'themes'                       => $this->factory->getInstalledThemes('email', true),
                    'beetemplates'                 => $this->factory->getInstalledBeeTemplates('email'),
                    'email'                        => $entity,
                    'forceTypeSelection'           => $forceTypeSelection,
                    'attachmentSize'               => $attachmentSize,
                    'builderAssets'                => trim(preg_replace('/\s+/', ' ', $this->getAssetsForBuilder())), // strip new lines
                    'sectionForm'                  => $sectionForm->createView(),
                    'permissions'                  => $permissions,
                    'isMobile'                     => $ismobile,
                    'verifiedemail'                => $verifiedemail,
                    'mailertransport'              => $mailertransport,
                    'template_filters'             => $groupFilters,
                    'google_tags'                  => $googletags,
                ],
                'contentTemplate' => 'MauticEmailBundle:Email:form.html.php',
                'passthroughVars' => [
                    'activeLink'      => '#le_email_campaign_index',
                    'leContent'       => 'email',
                    'updateSelect'    => InputHelper::clean($this->request->query->get('updateSelect')),
                    'route'           => $this->generateUrl('le_email_campaign_action', $routeParams),
                    'validationError' => $this->getFormErrorForBuilder($form),
                ],
            ]
        );
    }

    /**
     * Clone an entity.
     *
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function cloneAction($objectId)
    {
        $model = $this->getModel('email');
        /** @var Email $entity */
        $entity = $model->getEntity($objectId);
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }
        /**$isStateAlive=$this->get('le.helper.statemachine')->isStateAlive('Customer_Sending_Domain_Not_Configured');
        if ($isStateAlive) {
            $configurl=$this->factory->getRouter()->generate('le_sendingdomain_action');
            $this->addFlash($this->translator->trans('le.email.config.mailer.status.report', ['%url%' => $configurl]));

            return $this->postActionRedirect(
                [
                    'passthroughVars' => [
                        'closeModal' => 1,
                        'route'      => false,
                    ],
                ]
            );
        }*/
        if ($entity != null) {
            if (!$this->get('mautic.security')->isGranted('email:emails:create')
                || !$this->get('mautic.security')->hasEntityAccess(
                    'email:emails:viewown',
                    'email:emails:viewother',
                    $entity->getCreatedBy()
                )
            ) {
                return $this->accessDenied();
            }

            $entity      = clone $entity;
            $entity->setIsPublished(true);
            $session     = $this->get('session');
            $contentName = 'mautic.emailbuilder.'.$entity->getSessionId().'.content';

            $session->set($contentName, $entity->getCustomHtml());
        }

        return $this->newAction($entity, true);
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
        $page      = $this->get('session')->get('mautic.email.page', 1);
        $returnUrl = $this->generateUrl('le_email_campaign_index', ['page' => $page]);
        $flashes   = [];
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticEmailBundle:EmailCampaign:index',
            'passthroughVars' => [
                'activeLink'    => 'le_email_campaign_index',
                'leContent'     => 'email',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model  = $this->getModel('email');
            $entity = $model->getEntity($objectId);

            if ($entity === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'le.email.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('mautic.security')->hasEntityAccess(
                'email:emails:deleteown',
                'email:emails:deleteother',
                $entity->getCreatedBy()
            )
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'email');
            }
            /** @var LeadEventLogRepository $leadEventLog */
            $leadEventLog  = $this->get('mautic.email.repository.leadEventLog');
            $leadEvents    = $leadEventLog->getEntities(
                [
                    'filter'           => [
                        'force' => [
                            [
                                'column' => 'dle.email',
                                'expr'   => 'eq',
                                'value'  => $entity,
                            ],
                        ],
                    ],
                    'ignore_paginator' => true,
                ]
            );
            foreach ($leadEvents as $event) {
                $leadEventLog->deleteEntity($event);
            }

            $model->deleteEntity($entity);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'mautic.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $entity->getName(),
                    '%id%'   => $objectId,
                ],
            ];
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
     * Activate the builder.
     *
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @throws \Exception
     * @throws \Mautic\CoreBundle\Exception\FileNotFoundException
     */
    public function builderAction($objectId)
    {
        /** @var \Mautic\EmailBundle\Model\EmailModel $model */
        $model = $this->getModel('email');

        //permission check
        if (strpos($objectId, 'new') !== false) {
            $isNew = true;
            if (!$this->get('mautic.security')->isGranted('email:emails:create')) {
                return $this->accessDenied();
            }
            $entity = $model->getEntity();
            $entity->setSessionId($objectId);
        } else {
            $isNew  = false;
            $entity = $model->getEntity($objectId);
            if ($entity == null
                || !$this->get('mautic.security')->hasEntityAccess(
                    'email:emails:viewown',
                    'email:emails:viewother',
                    $entity->getCreatedBy()
                )
            ) {
                return $this->accessDenied();
            }
        }
        $isBeeTemplate = $this->request->get('beetemplate', false);
        if ($isBeeTemplate) {
            $template = InputHelper::clean($this->request->query->get('beetemplate'));

            return new JsonResponse($this->factory->getBeeTemplateJSONByName($template));
        } else {
            $isBeeHTMLTemplate = $this->request->get('beehtmltemplate', false);
            if ($isBeeHTMLTemplate) {
                $template = InputHelper::clean($this->request->query->get('beehtmltemplate'));

                return new JsonResponse($this->factory->getBeeTemplateHTMLByName($template));
            }
            $template = InputHelper::clean($this->request->query->get('template'));

            $slots    = $this->factory->getTheme($template)->getSlots('email');

            //merge any existing changes
            $newContent = $this->get('session')->get('mautic.emailbuilder.'.$objectId.'.content', []);
            $content    = $entity->getContent();

            if (is_array($newContent)) {
                $content = array_merge($content, $newContent);
                // Update the content for processSlots
                $entity->setContent($content);
            }

            // Replace short codes to emoji
            $content = EmojiHelper::toEmoji($content, 'short');

            $this->processSlots($slots, $entity);

            $logicalName = $this->factory->getHelper('theme')->checkForTwigTemplate(':'.$template.':email.html.php');

            return $this->render(
                $logicalName,
                [
                    'isNew'    => $isNew,
                    'slots'    => $slots,
                    'content'  => $content,
                    'email'    => $entity,
                    'template' => $template,
                    'basePath' => $this->request->getBasePath(),
                ]
            );
        }
    }

    /**
     * Create an AB test.
     *
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function abtestAction($objectId)
    {
        $model  = $this->getModel('email');
        $entity = $model->getEntity($objectId);

        if ($entity != null) {
            $parent = $entity->getVariantParent();

            if ($parent || !$this->get('mautic.security')->isGranted('email:emails:create')
                || !$this->get('mautic.security')->hasEntityAccess(
                    'email:emails:viewown',
                    'email:emails:viewother',
                    $entity->getCreatedBy()
                )
            ) {
                return $this->accessDenied();
            }

            // Note this since it's cleared on __clone()
            $emailType = $entity->getEmailType();

            $clone = clone $entity;

            //reset
            $clone->clearStats();
            $clone->setSentCount(0);
            $clone->setRevision(0);
            $clone->setVariantSentCount(0);
            $clone->setVariantStartDate(null);
            $clone->setIsPublished(false);
            $clone->setEmailType($emailType);
            $clone->setVariantParent($entity);
        }

        return $this->newAction($clone, true);
    }

    /**
     * Make the variant the main.
     *
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function winnerAction($objectId)
    {
        //todo - add confirmation to button click
        $page      = $this->get('session')->get('mautic.email', 1);
        $returnUrl = $this->generateUrl('le_email_campaign_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticEmailBundle:Page:index',
            'passthroughVars' => [
                'activeLink'    => 'le_email_campaign_index',
                'leContent'     => 'page',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model  = $this->getModel('email');
            $entity = $model->getEntity($objectId);

            if ($entity === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'le.email.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('mautic.security')->hasEntityAccess(
                'email:emails:editown',
                'email:emails:editother',
                $entity->getCreatedBy()
            )
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'email');
            }

            $model->convertVariant($entity);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'le.email.notice.activated',
                'msgVars' => [
                    '%name%' => $entity->getName(),
                    '%id%'   => $objectId,
                ],
            ];

            $postActionVars['viewParameters'] = [
                'objectAction' => 'view',
                'objectId'     => $objectId,
            ];
            $postActionVars['returnUrl']       = $this->generateUrl('le_page_action', $postActionVars['viewParameters']);
            $postActionVars['contentTemplate'] = 'MauticEmailBundle:Page:view';
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
     * Manually sends emails.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sendAction($objectId)
    {
        /** @var \Mautic\EmailBundle\Model\EmailModel $model */
        $model                 = $this->getModel('email');
        $entity                = $model->getEntity($objectId);
        $session               = $this->get('session');
        $page                  = $session->get('mautic.email.page', 1);
        $totalEmailCount       = $this->get('mautic.helper.licenseinfo')->getTotalEmailCount();
        $actualEmailCount      = $this->get('mautic.helper.licenseinfo')->getActualEmailCount();
        $isHavingEmailValidity = $this->get('mautic.helper.licenseinfo')->isHavingEmailValidity();
        $accountStatus         = $this->get('mautic.helper.licenseinfo')->getAccountStatus();
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }
        //set the return URL
        $returnUrl = $this->generateUrl('le_email_campaign_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticEmailBundle:EmailCampaign:index',
            'passthroughVars' => [
                'activeLink'    => 'le_email_campaign_index',
                'leContent'     => 'email',
            ],
        ];
        $smHelper           = $this->get('le.helper.statemachine');
        //$isStateAlive       =$smHelper->isStateAlive('Customer_Sending_Domain_Not_Configured');
        if ($smHelper->isStateAlive('Trial_Unverified_Email')) {
            $this->addFlash($this->translator->trans('le.email.unverified.error'));

            return $this->viewAction($objectId);
        }
        if (empty($entity->getCustomHtml())) {
            /* $configurl=$this->factory->getRouter()->generate('le_sendingdomain_action');
            if ($isStateAlive) {
                $this->addFlash($this->translator->trans('le.email.config.mailer.status.report', ['%url%' => $configurl]));
            }*/
            $this->addFlash($this->translator->trans('le.email.content.empty'));

            return $this->viewAction($objectId);
        }
        //not found
        if ($entity === null) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'le.email.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        } elseif ($entity->getEmailType() == 'template'
            || !$this->get('mautic.security')->hasEntityAccess(
                'email:emails:viewown',
                'email:emails:viewother',
                $entity->getCreatedBy()
            )
        ) {
            return $this->accessDenied();
        }

        // Check that the parent is getting sent
        if ($variantParent = $entity->getVariantParent()) {
            return $this->redirect($this->generateUrl('le_email_campaign_action',
                [
                    'objectAction' => 'send',
                    'objectId'     => $variantParent->getId(),
                ]
            ));
        }
        if ($translationParent = $entity->getTranslationParent()) {
            return $this->redirect($this->generateUrl('le_email_campaign_action',
                [
                    'objectAction' => 'send',
                    'objectId'     => $translationParent->getId(),
                ]
            ));
        }

        // Make sure email and category are published
        $category     = $entity->getCategory();
        $catPublished = (!empty($category)) ? $category->isPublished() : true;
        $published    = $entity->isPublished();
        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator     = $this->factory->get('mautic.configurator');
        $params           = $configurator->getParameters();
        $fromadress       = $entity->getFromAddress();
        $emailuser        = $params['mailer_user'];
        if (isset($params['mailer_amazon_region'])) {
            $region                = $params['mailer_amazon_region'];
        } else {
            $region='';
        }
        //$region           = $params['mailer_amazon_region'];
        $emailpassword    = $params['mailer_password'];
        $transport        = $params['mailer_transport'];
        $emailValidator   = $this->factory->get('mautic.validator.email');

        if ($transport == 'le.transport.amazon') {
            $isValidEmail   = $emailValidator->getVerifiedEmailAddressDetails($emailuser, $emailpassword, $region, $fromadress);
            if (!$isValidEmail) {
                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars,
                        [
                            'flashes' => [
                                [
                                    'type'    => 'error',
                                    'msg'     => 'le.email.error.send.aws.verification',
                                ],
                            ],
                        ]
                    )
                );
            }
        }

        if (!$catPublished || !$published) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'le.email.error.send.unpublished',
                               // 'msgVars' => ['%name%' => $entity->getName()],
                            ],
                        ],
                    ]
                )
            );
        }

        $action             = $this->generateUrl('le_email_campaign_action', ['objectAction' => 'send', 'objectId' => $objectId]);
        $pending            = $model->getPendingLeads($entity, null, true);
        $form               = $this->get('form.factory')->create('batch_send', [], ['action' => $action]);
        $complete           = $this->request->request->get('complete', false);
        $remainingCount     = $pending + $actualEmailCount;
        $licenseinfohelper  =  $this->get('mautic.helper.licenseinfo');
        $paymentrepository  =$this->get('le.subscription.repository.payment');
        $lastpayment        = $paymentrepository->getLastPayment();
        if ($lastpayment != null) {
            $isvalid = $licenseinfohelper->isValidMaxLimit($pending, 'max_email_limit', 50000, 'le.lead.max.email.count.exceeds');
            if ($isvalid) {
                $this->addFlash($isvalid);

                return $this->postActionRedirect(
                    [
                        'returnUrl'=> $this->generateUrl('le_email_campaign_index'),
                    ]
                );
            }
        }

        if (!$accountStatus) {
            $cancelState         = $smHelper->isStateAlive('Customer_Inactive_Exit_Cancel');
            $domainNotConfigured = $smHelper->isStateAlive('Customer_Sending_Domain_Not_Configured');

            if (($totalEmailCount >= $remainingCount) || ($totalEmailCount == 'UL') || ($lastpayment != null && !$cancelState && !$domainNotConfigured)) {   //&& $isHavingEmailValidity
                if ($this->request->getMethod() == 'POST' && $this->isFormValid($form)) {//($complete || $this->isFormValid($form))) {
                    /*if (!$complete) {
                        $progress = [0, (int) $pending];
                        $session->set('mautic.email.send.progress', $progress);

                        $stats = ['sent' => 0, 'failed' => 0, 'failedRecipients' => []];
                        $session->set('le.email.send.stats', $stats);

                        $status     = 'inprogress';
                        $batchlimit = $form['batchlimit']->getData();

                        $session->set('mautic.email.send.active', false);
                    } else {
                        $stats      = $session->get('le.email.send.stats');
                        $progress   = $session->get('mautic.email.send.progress');
                        $batchlimit = 100;
                        $status     = (!empty($stats['failed'])) ? 'with_errors' : 'success';
                    }

                    $contentTemplate = 'MauticEmailBundle:Send:progress.html.php';
                    $viewParameters  = [
                        'progress'   => $progress,
                        'stats'      => $stats,
                        'status'     => $status,
                        'email'      => $entity,
                        'batchlimit' => $batchlimit,
                    ];*/

                    /**$pending                   = $model->getPendingLeads($entity, null, true);
                    $message                   = '';
                    $flashType                 = '';
                    if ($licenseinfohelper->isLeadsEngageEmailExpired($pending)) {
                        $message   = 'le.email.broadcast.usage.error';
                        $flashType = 'notice';
                    } else { */
                    $entity->setIsScheduled(true);
                    $model->saveEntity($entity);
                    $message   ='le.email.broadcast.send';
                    $flashType = 'sweetalert';
//                    }
                    $postActionVars = [
                        'returnUrl'       => $this->generateUrl('le_email_campaign_action', ['objectAction' => 'view', 'objectId' => $objectId]),
                        'viewParameters'  => ['objectAction' => 'view', 'objectId' => $objectId],
                        'contentTemplate' => 'MauticEmailBundle:EmailCampaign:view',
                        'passthroughVars' => [
                            'activeLink'    => 'le_email_campaign_index',
                            'leContent'     => 'email',
                        ],
                    ];

                    return $this->postActionRedirect(
                        array_merge(
                            $postActionVars,
                            [
                                'flashes' => [
                                    [
                                        'type'    => $flashType,
                                        'msg'     => $message,
                                    ],
                                ],
                            ]
                        )
                    );

                // return $this->viewAction($objectId);
                } else {
                    //process and send
                    $contentTemplate = 'MauticEmailBundle:Send:form.html.php';
                    $viewParameters  = [
                        'form'       => $form->createView(),
                        'email'      => $entity,
                        'pending'    => $pending,
                        'tmpl'       => 'index',
                        'actionRoute'=> 'le_email_campaign_action',
                        'indexRoute' => 'le_email_campaign_index',
                    ];
                }

                return $this->delegateView(
                    [
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $contentTemplate,
                        'passthroughVars' => [
                            'leContent'     => 'emailSend',
                            'route'         => $action,
                        ],
                    ]
                );
            } else {
                $configurl     = $this->factory->getRouter()->generate('le_config_action', ['objectAction' => 'edit']);
                $this->addFlash('mautic.email.count.exceeds', ['%url%'=>$configurl, '%actual_email%' => $actualEmailCount, '%total_email%' => $totalEmailCount]);

                return $this->postActionRedirect(
                [
                    'returnUrl'=> $this->generateUrl('le_email_campaign_index'),
                ]
            );
            }
        } else {
            $this->addFlash('mautic.account.suspend');

            return $this->viewAction($objectId);
        }
    }

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        $page           = $this->get('session')->get('mautic.email.page', 1);
        $returnUrl      = $this->generateUrl('le_email_campaign_index', ['page' => $page]);
        $flashes        = [];
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticEmailBundle:EmailCampaign:index',
            'passthroughVars' => [
                'activeLink'    => '#le_email_campaign_index',
                'leContent'     => 'email',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model = $this->getModel('email');
            $ids   = json_decode($this->request->query->get('ids', '{}'));

            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'le.email.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('mautic.security')->hasEntityAccess(
                    'email:emails:viewown',
                    'email:emails:viewother',
                    $entity->getCreatedBy()
                )
                ) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'email', true);
                } else {
                    $deleteIds[] = $objectId;
                    /** @var LeadEventLogRepository $leadEventLog */
                    $leadEventLog  = $this->get('mautic.email.repository.leadEventLog');
                    $leadEvents    = $leadEventLog->getEntities(
                        [
                            'filter'           => [
                                'force' => [
                                    [
                                        'column' => 'dle.email',
                                        'expr'   => 'eq',
                                        'value'  => $entity,
                                    ],
                                ],
                            ],
                            'ignore_paginator' => true,
                        ]
                    );
                    foreach ($leadEvents as $event) {
                        $leadEventLog->deleteEntity($event);
                    }
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'le.email.notice.batch_deleted',
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
     * Generating the modal box content for
     * the send multiple example email option.
     */
    public function sendExampleAction($objectId)
    {
        $model  = $this->getModel('email');
        $entity = $model->getEntity($objectId);
        //not found or not allowed
        if ($entity === null
            || (!$this->get('mautic.security')->hasEntityAccess(
                'email:emails:viewown',
                'email:emails:viewother',
                $entity->getCreatedBy()
            ))
        ) {
            return $this->postActionRedirect(
                [
                    'passthroughVars' => [
                        'closeModal' => 1,
                        'route'      => false,
                    ],
                ]
            );
        }
        //$isStateAlive=$this->get('le.helper.statemachine')->isStateAlive('Customer_Sending_Domain_Not_Configured');
        if (empty($entity->getCustomHtml())) {
            /**$configurl=$this->factory->getRouter()->generate('le_sendingdomain_action');
            if ($isStateAlive) {
                $this->addFlash($this->translator->trans('le.email.config.mailer.status.report', ['%url%' => $configurl]));
            }*/
            $this->addFlash($this->translator->trans('le.email.content.empty'));

            return $this->postActionRedirect(
                [
                    'passthroughVars' => [
                        'closeModal' => 1,
                        'route'      => false,
                    ],
                ]
            );
            //return $this->viewAction($objectId);
        }
        // Get the quick add form
        $action = $this->generateUrl('le_email_campaign_action', ['objectAction' => 'sendExample', 'objectId' => $objectId]);
        $user   = $this->get('mautic.helper.user')->getUser();

        $form = $this->createForm(ExampleSendType::class, ['emails' => ['list' => [$user->getEmail()]]], ['action' => $action]);
        /* @var \Mautic\EmailBundle\Model\EmailModel $model */

        if ($this->request->getMethod() == 'POST') {
            $isCancelled = $this->isFormCancelled($form);
            $isValid     = $this->isFormValid($form);
            $lists       =$form['emails']->getData()['list'];
            $emails      =[];
            foreach ($lists as $list) {
                if (!empty($list) && !is_int($list)) {
                    $emails[]=$list;
                }
            }
            $count=sizeof($emails);
            if (!$isCancelled && $isValid) {
                if ($count != 0) {
                    //$emails = $form['emails']->getData()['list'];

                    // Prepare a fake lead
                    /** @var \Mautic\LeadBundle\Model\FieldModel $fieldModel */
                    $fieldModel = $this->getModel('lead.field');
                    $fields     = $fieldModel->getFieldList(false, false);
                    array_walk(
                        $fields,
                        function (&$field) {
                            $field = "[$field]";
                        }
                    );
                    $fields['id'] = 0;

                    $errors  = [];
                    foreach ($emails as $email) {
                        if (!empty($email)) {
                            $users   = [
                                [
                                    // Setting the id, firstname and lastname to null as this is a unknown user
                                    'id'        => '',
                                    'firstname' => '',
                                    'lastname'  => '',
                                    'email'     => $email,
                                ],
                            ];

                            // Send to current user
                            $error = $model->sendSampleEmailToUser($entity, $users, $fields, [], [], false);
                            if (count($error)) {
                                array_push($errors, $error[0]);
                            }
                        }
                    }

                    if (count($errors) != 0) {
                        $this->addFlash(implode('; ', $errors));
                    } else {
                        $this->addFlash('le.email.notice.test_sent_multiple.success');
                    }
                } elseif ($count == 0) {
                    $this->addFlash('le.email.notice.test_sent.address.required');
                }
            }

            if ($isValid || $isCancelled) {
                return $this->postActionRedirect(
                    [
                        'passthroughVars' => [
                            'closeModal' => 1,
                            'route'      => false,
                        ],
                    ]
                );
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form' => $form->createView(),
                ],
                'contentTemplate' => 'MauticEmailBundle:Email:recipients.html.php',
            ]
        );
    }

    /**
     * Send example email to current user.
     *
     * @deprecated 2.5 to be removed in 3.0
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function exampleAction($objectId)
    {
        /** @var \Mautic\EmailBundle\Model\EmailModel $model */
        $model  = $this->getModel('email');
        $entity = $model->getEntity($objectId);

        //not found or not allowed
        if ($entity === null
            || (!$this->get('mautic.security')->hasEntityAccess(
                'email:emails:viewown',
                'email:emails:viewother',
                $entity->getCreatedBy()
            ))
        ) {
            return $this->viewAction($objectId);
        }

        // Prepare a fake lead
        /** @var \Mautic\LeadBundle\Model\FieldModel $fieldModel */
        $fieldModel = $this->getModel('lead.field');
        $fields     = $fieldModel->getFieldList(false, false);
        array_walk(
            $fields,
            function (&$field) {
                $field = "[$field]";
            }
        );
        $fields['id'] = 0;

        // Send to current user
        $user  = $this->user;
        $users = [
            [
                'id'        => $user->getId(),
                'firstname' => $user->getFirstName(),
                'lastname'  => $user->getLastName(),
                'email'     => $user->getEmail(),
            ],
        ];

        // Send to current user
        $errors = $model->sendSampleEmailToUser($entity, $users, $fields, [], [], false);
        if (count($errors)) {
            $this->addFlash(implode('; ', $errors));
        } else {
            $this->addFlash('le.email.notice.test_sent.success');
        }

        return $this->viewAction($objectId);
    }

    /**
     * PreProcess page slots for public view.
     *
     * @param array $slots
     * @param Email $entity
     */
    private function processSlots($slots, $entity)
    {
        /** @var \Mautic\CoreBundle\Templating\Helper\SlotsHelper $slotsHelper */
        $slotsHelper = $this->get('templating.helper.slots');
        $content     = $entity->getContent();

        //Set the slots
        foreach ($slots as $slot => $slotConfig) {
            //support previous format where email slots are not defined with config array
            if (is_numeric($slot)) {
                $slot       = $slotConfig;
                $slotConfig = [];
            }

            $value = isset($content[$slot]) ? $content[$slot] : '';
            $slotsHelper->set($slot, "<div data-slot=\"text\" id=\"slot-{$slot}\">{$value}</div>");
        }

        //add builder toolbar
        $slotsHelper->start('builder'); ?>
        <input type="hidden" id="builder_entity_id" value="<?php echo $entity->getSessionId(); ?>"/>
        <?php
        $slotsHelper->stop();
    }

    /**
     * Checks the form data for webinar tokens and validates that the segment has webinar filters.
     *
     * @param array $data
     * @param Form  $form
     * @param Email $email
     *
     * @return int
     */
    protected function isFormValidForWebinar(array $data, Form &$form, Email $email)
    {
        if (!CitrixHelper::isAuthorized('Gotowebinar')) {
            return true;
        }

        // search for webinar filters in the email segments
        if (!array_key_exists('lists', $data) || 0 === count($data['lists'])) {
            return true;
        }

        // search for token in content
        $html         = $email->getCustomHtml();
        $isTokenFound = preg_match('/\{webinar_button\}/', $html);
        if (!$isTokenFound) {
            return true;
        }

        $isWebinarFilterPresent = false;
        $webinarFiltersCount    = 0;
        $lists                  = $data['lists'];
        /** @var ListModel $model */
        $model = $this->getModel('lead.list');
        foreach ($lists as $listId) {
            $list    = $model->getEntity($listId);
            $filters = $list->getFilters();
            foreach ($filters as $filter) {
                if ('webinar-registration' == $filter['field'] && 'in' == $filter['operator']) {
                    $isWebinarFilterPresent = true;
                    ++$webinarFiltersCount;
                }
            }
        }
        // make sure that each list has a webinar-registration filter
        if (count($lists) !== $webinarFiltersCount) {
            $isWebinarFilterPresent = false;
        }
        if (!$isWebinarFilterPresent) {
            $error = $this->get('translator')->trans('plugin.citrix.webinar.token_error');
            $form->addError(new FormError($error));

            return false;
        }

        // everything is ok
        return true;
    }

    /**
     * @param     $objectId
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function contactsAction($objectId, $page = 1)
    {
        return $this->generateContactsGrid(
            $objectId,
            $page,
            ['email:emails:viewown', 'email:emails:viewother'],
            'email',
            'email_stats',
            'email',
            'email_id'
        );
    }

    public function getassets($assets)
    {
        $last = sizeof($assets);
        for ($i = 0; $i < $last - 1; ++$i) {
            $correctassets[] = $assets[$i];
        }
        $attachmentSize = $this->getModel('asset')->getTotalFilesize($correctassets);
        if ($attachmentSize == 'failed') {
            $correctassets = $this->getassets($correctassets);
        }

        return $correctassets;
    }

    public function templatePreviewAction($template)
    {
        $content = $this->factory->getBeeTemplateHTMLByName($template);

        return $this->render('MauticEmailBundle:DripEmail:preview.html.php', ['content' => $content]);
    }

    public function checkDMARKinDefaultSendingDomain($form)
    {
        $isValidForm = true;
        $formData    = $this->request->request->get('emailform');
        $isvalid     = $this->get('mautic.helper.licenseinfo')->checkDMARKinDefaultSendingDomain($formData['fromAddress']);
        if (!$isvalid) {
            $isValidForm = false;
            $form['fromAddress']->addError(new FormError($this->translator->trans('le.lead.email.from.dmark.error', [], 'validators')));
        }

        return $isValidForm;
    }
}
