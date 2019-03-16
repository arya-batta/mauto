<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Controller\Api;

use FOS\RestBundle\Util\Codes;
use JMS\Serializer\SerializationContext;
use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\LeadBundle\Controller\FrequencyRuleTrait;
use Mautic\LeadBundle\Controller\LeadDetailsTrait;
use Mautic\LeadBundle\Entity\CustomFieldEntityTrait;
use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class LeadApiController.
 *
 * @property LeadModel $model
 */
class LeadApiController extends CommonApiController
{
    use CustomFieldsApiControllerTrait;
    use FrequencyRuleTrait;
    use LeadDetailsTrait;
    use CustomFieldEntityTrait;

    const MODEL_ID = 'lead.lead';

    /**
     * @param FilterControllerEvent $event
     */
    public function initialize(FilterControllerEvent $event)
    {
        $this->model            = $this->getModel(self::MODEL_ID);
        $this->entityClass      = Lead::class;
        $this->entityNameOne    = 'lead'; //previous it was contact
        $this->entityNameMulti  = 'leads'; //previous it was contacts
        $this->serializerGroups = ['leadDetails', 'leadListDetails', 'frequencyRulesList', 'doNotContactList', 'userList', 'stageList', 'publishDetails', 'ipAddress', 'tagList', 'utmtagsList', 'leadList'];

        parent::initialize($event);
    }

    /**
     * Get existing duplicated contact based on unique fields and the request data.
     *
     * @param array $parameters
     * @param null  $id
     *
     * @return null|Lead
     *
     * @deprecated since 2.12.2, to be removed in 3.0.0. Use $model->checkForDuplicateContact directly instead
     */
    protected function getExistingLead(array $parameters, $id = null)
    {
        $model   = $this->getModel(self::MODEL_ID);
        $contact = $id ? $model->getEntity($id) : null;

        return $model->checkForDuplicateContact($parameters, $contact);
    }

    /**
     * Obtains a list of users for lead owner edits.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getOwnersAction()
    {
        if (!$this->get('mautic.security')->isGranted(
            ['lead:leads:create', 'lead:leads:editown', 'lead:leads:editother'],
            'MATCH_ONE'
        )
        ) {
            return $this->accessDenied();
        }

        $filter  = $this->request->query->get('filter', null);
        $limit   = $this->request->query->get('limit', null);
        $start   = $this->request->query->get('start', null);
        $users   = $this->model->getLookupResults('user', $filter, $limit, $start);
        $view    = $this->view($users, Codes::HTTP_OK);
        $context = SerializationContext::create()->setGroups(['userList']);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Obtains a list of custom fields.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getFieldsAction()
    {
        if (!$this->get('mautic.security')->isGranted(['lead:leads:editown', 'lead:leads:editother'], 'MATCH_ONE')) {
            return $this->accessDenied();
        }

        $fields = $this->getModel('lead.field')->getEntities(
            [
                'filter' => [
                    'force' => [
                        [
                            'column' => 'f.isPublished',
                            'expr'   => 'eq',
                            'value'  => true,
                            'object' => 'lead',
                        ],
                    ],
                ],
            ]
        );

        $view    = $this->view($fields, Codes::HTTP_OK);
        $context = SerializationContext::create()->setGroups(['leadFieldList']);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Obtains a list of notes on a specific lead.
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getNotesAction($id)
    {
        $entity = $this->model->getEntity($id);

        if ($entity === null) {
            return $this->notFound();
        }

        if (!$this->get('mautic.security')->hasEntityAccess('lead:leads:viewown', 'lead:leads:viewother', $entity->getPermissionUser())) {
            return $this->accessDenied();
        }

        $results = $this->getModel('lead.note')->getEntities(
            [
                'start'  => $this->request->query->get('start', 0),
                'limit'  => $this->request->query->get('limit', $this->coreParametersHelper->getParameter('default_pagelimit')),
                'filter' => [
                    'string' => $this->request->query->get('search', ''),
                    'force'  => [
                        [
                            'column' => 'n.lead',
                            'expr'   => 'eq',
                            'value'  => $entity,
                        ],
                    ],
                ],
                'orderBy'    => $this->addAliasIfNotPresent($this->request->query->get('orderBy', 'n.dateAdded'), 'n'),
                'orderByDir' => $this->request->query->get('orderByDir', 'DESC'),
            ]
        );

        list($notes, $count) = $this->prepareEntitiesForView($results);

        $view = $this->view(
            [
                'total' => $count,
                'notes' => $notes,
            ],
            Codes::HTTP_OK
        );

        $context = SerializationContext::create()->setGroups(['leadNoteDetails']);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Obtains a list of devices on a specific lead.
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDevicesAction($id)
    {
        $entity = $this->model->getEntity($id);

        if ($entity === null) {
            return $this->notFound();
        }

        if (!$this->get('mautic.security')->hasEntityAccess('lead:leads:viewown', 'lead:leads:viewother', $entity->getPermissionUser())) {
            return $this->accessDenied();
        }

        $results = $this->getModel('lead.device')->getEntities(
            [
                'start'  => $this->request->query->get('start', 0),
                'limit'  => $this->request->query->get('limit', $this->coreParametersHelper->getParameter('default_pagelimit')),
                'filter' => [
                    'string' => $this->request->query->get('search', ''),
                    'force'  => [
                        [
                            'column' => 'd.lead',
                            'expr'   => 'eq',
                            'value'  => $entity,
                        ],
                    ],
                ],
                'orderBy'    => $this->request->query->get('orderBy', 'd.dateAdded'),
                'orderByDir' => $this->request->query->get('orderByDir', 'DESC'),
            ]
        );

        list($devices, $count) = $this->prepareEntitiesForView($results);

        $view = $this->view(
            [
                'total'   => $count,
                'devices' => $devices,
            ],
            Codes::HTTP_OK
        );

        $context = SerializationContext::create()->setGroups(['leadDeviceDetails']);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Obtains a list of contact segments the contact is in.
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getListsAction()
    {
        $email = $this->request->get('email');

        $result = $this->model->findEmail($email);

        if (!count($result) > 0) {
            return $this->notFound('le.core.contact.error.notfound');
        }
        $id     = $result[0]->getId();
        $entity = $this->model->getEntity($id);
        if ($entity !== null) {
            if (!$this->get('mautic.security')->hasEntityAccess('lead:leads:viewown', 'lead:leads:viewother', $entity->getPermissionUser())) {
                return $this->accessDenied();
            }

            $lists = $this->model->getLists($entity, true, true);

            foreach ($lists as &$l) {
                unset($l['leads'][0]['leadlist_id']);
                unset($l['leads'][0]['lead_id']);
                unset($l['alias']);

                $l = array_merge($l, $l['leads'][0]);

                unset($l['leads']);
            }

            $view = $this->view(
                [
                    'total'    => count($lists),
                    'segments' => $lists,
                ],
                Codes::HTTP_OK
            );

            return $this->handleView($view);
        }

        return $this->notFound();
    }

    /**
     * Obtains a list of contact companies the contact is in.
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCompaniesAction($id)
    {
        $entity = $this->model->getEntity($id);

        if ($entity === null) {
            return $this->notFound();
        }

        if (!$this->get('mautic.security')->hasEntityAccess('lead:leads:viewown', 'lead:leads:viewother', $entity->getPermissionUser())) {
            return $this->accessDenied();
        }

        $companies = $this->model->getCompanies($entity);

        $view = $this->view(
            [
                'total'     => count($companies),
                'companies' => $companies,
            ],
            Codes::HTTP_OK
        );

        return $this->handleView($view);
    }

    /**
     * Obtains a list of campaigns the lead is part of.
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCampaignsAction()
    {
        $email = $this->request->get('email');

        $result = $this->model->findEmail($email);

        if (!count($result) > 0) {
            return $this->notFound('le.core.contact.error.notfound');
        }
        $id     = $result[0]->getId();
        $entity = $this->model->getEntity($id);
        if ($entity !== null) {
            if (!$this->get('mautic.security')->hasEntityAccess('lead:leads:viewown', 'lead:leads:viewother', $entity->getPermissionUser())) {
                return $this->accessDenied();
            }

            /** @var \Mautic\CampaignBundle\Model\CampaignModel $campaignModel */
            $campaignModel = $this->getModel('campaign');
            $campaigns     = $campaignModel->getLeadCampaigns($entity, true);

            foreach ($campaigns as &$c) {
                if (!empty($c['lists'])) {
                    $c['listMembership'] = array_keys($c['lists']);
                    unset($c['lists']);
                }

                unset($c['leads'][0]['campaign_id']);
                unset($c['leads'][0]['lead_id']);

                $c = array_merge($c, $c['leads'][0]);

                unset($c['leads']);
            }

            $view = $this->view(
                [
                    'total'     => count($campaigns),
                    'workflows' => $campaigns,
                ],
                Codes::HTTP_OK
            );

            return $this->handleView($view);
        }

        return $this->notFound();
    }

    /**
     * Obtains a list of contact events.
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getActivityAction($id)
    {
        $entity = $this->model->getEntity($id);

        if ($entity === null) {
            return $this->notFound();
        }

        if (!$this->checkEntityAccess($entity, 'view')) {
            return $this->accessDenied();
        }

        return $this->getAllActivityAction($entity);
    }

    /**
     * Obtains a list of contact events.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAllActivityAction($lead = null)
    {
        $canViewOwn    = $this->security->isGranted('lead:leads:viewown');
        $canViewOthers = $this->security->isGranted('lead:leads:viewother');

        if (!$canViewOthers && !$canViewOwn) {
            return $this->accessDenied();
        }

        $filters = $this->sanitizeEventFilter(InputHelper::clean($this->request->get('filters', [])));
        $limit   = (int) $this->request->get('limit', 25);
        $page    = (int) $this->request->get('page', 1);
        $order   = InputHelper::clean($this->request->get('order', ['timestamp', 'DESC']));

        list($events, $serializerGroups) = $this->model->getEngagements($lead, $filters, $order, $page, $limit, false);

        $view    = $this->view($events);
        $context = SerializationContext::create()->setGroups($serializerGroups);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Adds a DNC to the contact.
     *
     * @param $id
     * @param $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addDncAction($channel='email', $entity = null, $dncchannelId = null, $dncreason = 3, $dnccomments = null)
    {
        if (!$this->inBatchMode) {
            $email = $this->request->get('email');

            $result = $this->model->findEmail($email);

            if (!count($result) > 0) {
                return $this->notFound('le.core.contact.error.notfound');
            }

            $entity = $this->model->getEntity($result[0]->getId());

            if (!$this->checkEntityAccess($entity, 'edit')) {
                return $this->accessDenied();
            }
        }
        $channelId = $this->inBatchMode ? (int) $dncchannelId : (int) $this->request->request->get('channelId');
        if ($channelId) {
            $channeldata[$channel] = $channelId;
            $channel               = $channeldata;
        }
        $reason   = $this->inBatchMode ? (int) $dncreason : (int) $this->request->request->get('reason', 3);
        $comments = InputHelper::clean($this->inBatchMode ? $dnccomments : $this->request->request->get('comments'));

        $this->model->addDncForLead($entity, $channel, $comments, $reason);

        if ($this->inBatchMode) {
            $this->inBatchMode = false;

            return;
        }

        $view = $this->view([$this->entityNameOne => $entity]);

        return  $this->handleView($this->view(['success' => 1], Codes::HTTP_OK)); //previous it was $this->handleView($view);
    }

    /**
     * Removes a DNC from the contact.
     *
     * @param $id
     * @param $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeDncAction($channel)
    {
        $email = $this->request->get('email');

        $result = $this->model->findEmail($email);

        if (!count($result) > 0) {
            return $this->notFound('le.core.contact.error.notfound');
        }

        $entity = $this->model->getEntity($result[0]->getId());

        if (!$this->checkEntityAccess($entity, 'edit')) {
            return $this->accessDenied();
        }

        $result = $this->model->removeDncForLead($entity, $channel);
        $view   = $this->view(
            [
                'recordFound'        => $result,
                $this->entityNameOne => $entity,
            ]
        );

        return $this->handleView($this->view(['success' => 1], Codes::HTTP_OK)); //previous it was $this->handleView($view);
    }

    /**
     *  Adds a DNC to the Batch Of Leads.
     *
     * @return array|Response
     */
    public function addBatchDncAction()
    {
        $parameters = $this->request->request->all();

        $valid = $this->validateBatchPayload($parameters);
        if ($valid instanceof Response) {
            return $valid;
        }

        $this->inBatchMode = true;
        $entities          = [];
        $errors            = [];
        $statusCodes       = [];
        foreach ($parameters as $key => $params) {
            $result = $this->getModel('lead')->findEmail($params['email']);

            if (!count($result) > 0) {
                $this->setBatchError($key, 'le.core.contact.error.notfound', Codes::HTTP_NOT_FOUND, $errors, $entities);
                $statusCodes[$key] = $key.':Failed'; //Codes::HTTP_NOT_FOUND;
                continue;
            }

            $leadentity          = $this->getModel('lead')->getEntity($result[0]->getId());
            $leadId              = $leadentity->getId();

            $contact = $this->checkLeadAccess($leadId, 'edit');
            if ($contact instanceof Response) {
                $this->setBatchError($key, 'mautic.core.error.accessdenied', Codes::HTTP_FORBIDDEN, $errors, $entities, $leadentity);
                $statusCodes[$key] = $key.':Failed'; //Codes::HTTP_FORBIDDEN;
                continue;
            }

            $this->inBatchMode = true;
            $this->addDncAction('email', $leadentity);
            $statusCodes[$key] = $key.':Success'; //Codes::HTTP_OK;
            $this->getDoctrine()->getManager()->detach($leadentity);
        }

        $payload = [
            'status'          => $statusCodes,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        $view = $this->view($payload, Codes::HTTP_CREATED);
        $this->setSerializationContext($view);

        return $this->handleView($view);
    }

    /**
     * Add/Remove a UTM Tagset to/from the contact.
     *
     * @param int       $id
     * @param string    $method
     * @param array/int $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function applyUtmTagsAction($id, $method, $data)
    {
        $entity = $this->model->getEntity((int) $id);

        if ($entity === null) {
            return $this->notFound();
        }

        if (!$this->checkEntityAccess($entity, 'edit')) {
            return $this->accessDenied();
        }

        // calls add/remove method as appropriate
        $result = $this->model->$method($entity, $data);

        if ($result === false) {
            return $this->badRequest();
        }

        if ('removeUtmTags' == $method) {
            $view = $this->view(
                [
                    'recordFound'        => $result,
                    $this->entityNameOne => $entity,
                ]
            );
        } else {
            $view = $this->view([$this->entityNameOne => $entity]);
        }

        return $this->handleView($view);
    }

    /**
     * Adds a UTM Tagset to the contact.
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addUtmTagsAction($id)
    {
        return $this->applyUtmTagsAction($id, 'addUTMTags', $this->request->request->all());
    }

    /**
     * Remove a UTM Tagset for the contact.
     *
     * @param int $id
     * @param int $utmid
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeUtmTagsAction($id, $utmid)
    {
        return $this->applyUtmTagsAction($id, 'removeUtmTags', (int) $utmid);
    }

    /**
     * Obtains a list of contact events.
     *
     * @deprecated 2.10.0 to be removed in 3.0
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEventsAction($id)
    {
        $entity = $this->model->getEntity($id);

        if ($entity === null) {
            return $this->notFound();
        }

        if (!$this->checkEntityAccess($entity, 'view')) {
            return $this->accessDenied();
        }

        $filters = $this->sanitizeEventFilter(InputHelper::clean($this->request->get('filters', [])));
        $order   = InputHelper::clean($this->request->get('order', ['timestamp', 'DESC']));
        $page    = (int) $this->request->get('page', 1);
        $events  = $this->model->getEngagements($entity, $filters, $order, $page);

        return $this->handleView($this->view($events));
    }

    /**
     * Creates new entity from provided params.
     *
     * @param array $params
     *
     * @return object
     */
    public function getNewEntity(array $params)
    {
        return $this->model->checkForDuplicateContact($params);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareParametersForBinding($parameters, $entity, $action)
    {
        // Unset the tags from params to avoid a validation error
        if (isset($parameters['tags'])) {
            unset($parameters['tags']);
        }

        if (count($entity->getTags()) > 0) {
            foreach ($entity->getTags() as $tag) {
                $parameters['tags'][] = $tag->getId();
            }
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     *
     * @param Lead   $entity
     * @param array  $parameters
     * @param        $form
     * @param string $action
     */
    protected function preSaveEntity(&$entity, $form, $parameters, $action = 'edit')
    {
        if ('edit' === $action) {
            // Merge existing duplicate contact based on unique fields if exist
            // new endpoints will leverage getNewEntity in order to return the correct status codes
            $entity = $this->model->checkForDuplicateContact($this->entityRequestParameters, $entity);
        }

        if (isset($parameters['companies'])) {
            $this->model->modifyCompanies($entity, $parameters['companies']);
            unset($parameters['companies']);
        }

        if (isset($parameters['owner'])) {
            $owner = $this->getModel('user.user')->getEntity((int) $parameters['owner']);
            $entity->setOwner($owner);
            unset($parameters['owner']);
        }

        if (isset($parameters['stage'])) {
            $stage = $this->getModel('stage.stage')->getEntity((int) $parameters['stage']);
            $entity->setStage($stage);
            unset($parameters['stage']);
        }

        if (isset($this->entityRequestParameters['tags'])) {
            $this->model->modifyTags($entity, $this->entityRequestParameters['tags'], null, false);
        }

        //Since the request can be from 3rd party, check for an IP address if included
        if (isset($this->entityRequestParameters['ipAddress'])) {
            $ipAddress = $this->get('mautic.helper.ip_lookup')->getIpAddress($this->entityRequestParameters['ipAddress']);

            if (!$entity->getIpAddresses()->contains($ipAddress)) {
                $entity->addIpAddress($ipAddress);
            }

            unset($this->entityRequestParameters['ipAddress']);
        }

        // Check for lastActive date
        if (isset($this->entityRequestParameters['lastActive'])) {
            $lastActive = new DateTimeHelper($this->entityRequestParameters['lastActive']);
            $entity->setLastActive($lastActive->getDateTime());
            unset($this->entityRequestParameters['lastActive']);
        }

        if (!empty($parameters['doNotContact']) && is_array($parameters['doNotContact'])) {
            foreach ($parameters['doNotContact'] as $dnc) {
                $channel  = !empty($dnc['channel']) ? $dnc['channel'] : 'email';
                $comments = !empty($dnc['comments']) ? $dnc['comments'] : '';
                $reason   = !empty($dnc['reason']) ? $dnc['reason'] : DoNotContact::MANUAL;
                $this->model->addDncForLead($entity, $channel, $comments, $reason, false);
            }
            unset($parameters['doNotContact']);
        }

        if (!empty($parameters['frequencyRules'])) {
            $viewParameters = [];
            $data           = $this->getFrequencyRuleFormData($entity, null, null, false, $parameters['frequencyRules']);

            if (!$frequencyForm = $this->getFrequencyRuleForm($entity, $viewParameters, $data)) {
                $formErrors = $this->getFormErrorMessages($frequencyForm);
                $msg        = $this->getFormErrorMessage($formErrors);

                if (!$msg) {
                    $msg = $this->translator->trans('mautic.core.error.badrequest', [], 'flashes');
                }

                return $this->returnError($msg, Codes::HTTP_BAD_REQUEST, $formErrors);
            }

            unset($parameters['frequencyRules']);
        }

        $this->setCustomFieldValues($entity, $form, $parameters, 'POST' === $this->request->getMethod());
    }

    /**
     * Helper method to be used in FrequencyRuleTrait.
     *
     * @param Form $form
     *
     * @return bool
     */
    protected function isFormCancelled($form = null)
    {
        return false;
    }

    /**
     * Helper method to be used in FrequencyRuleTrait.
     *
     * @param Form  $form
     * @param array $data
     *
     * @return bool
     */
    protected function isFormValid(Form $form, array $data = null)
    {
        $form->submit($data, 'PATCH' !== $this->request->getMethod());

        return $form->isValid();
    }

    /**
     * Obtains a list of entities as defined by the API URL.
     *
     * @return Response
     */
    public function getEntitiesAction()
    {
        $parameters = $this->request->request->all();
        $repo       = $this->model->getRepository();
        $tableAlias = $repo->getTableAlias();
        //$publishedOnly = $this->request->get('published', 0);
        //$minimal       = $this->request->get('minimal', 0);
        $publishedOnly = isset($parameters['published']) ? $parameters['published'] : 0;
        $minimal       = isset($parameters['minimal']) ? $parameters['minimal'] : 0;

        try {
            if (!$this->security->isGranted($this->permissionBase.':view')) {
                return $this->accessDenied();
            }
        } catch (PermissionException $e) {
            return $this->accessDenied($e->getMessage());
        }

        if ($this->security->checkPermissionExists($this->permissionBase.':viewother')
            && !$this->security->isGranted($this->permissionBase.':viewother')
        ) {
            $this->listFilters = [
                'column' => $tableAlias.'.createdBy',
                'expr'   => 'eq',
                'value'  => $this->user->getId(),
            ];
        }
        if ($this->entityNameOne == 'user') {
            $this->listFilters[] = [
                'column' => $tableAlias.'.email',
                'expr'   => 'neq',
                'value'  => 'sadmin@leadsengage.com',
            ];
        }
        if ($publishedOnly) {
            $this->listFilters[] = [
                'column' => $tableAlias.'.isPublished',
                'expr'   => 'eq',
                'value'  => true,
            ];
        }
        if ($minimal) {
            if (isset($this->serializerGroups[0])) {
                $this->serializerGroups[0] = str_replace('Details', 'List', $this->serializerGroups[0]);
            }
        }

        $args = array_merge(
            [
                'start'  => isset($parameters['start']) ? $parameters['start'] : 0, //$this->request->query->get('start', 0),
                'limit'  => isset($parameters['limit']) ? $parameters['limit'] : $this->coreParametersHelper->getParameter('default_pagelimit'), //$this->request->query->get('limit', $this->coreParametersHelper->getParameter('default_pagelimit')),
                'filter' => [
                    'string' => isset($parameters['search']) ? $parameters['search'] : '', //$this->request->query->get('search', ''),
                    'force'  => $this->listFilters,
                ],
                'orderBy'        => $this->addAliasIfNotPresent(isset($parameters['orderBy']) ? strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $parameters['orderBy'])) : '', $tableAlias), //$this->addAliasIfNotPresent($this->request->query->get('orderBy', ''), $tableAlias),
                'orderByDir'     => isset($parameters['orderByDir']) ? $parameters['orderByDir'] : 'ASC', //$this->request->query->get('orderByDir', 'ASC'),
                'withTotalCount' => true, //for repositories that break free of Paginator
            ],
            $this->extraGetEntitiesArguments
        );
        $selectdata = isset($parameters['select']) ? $parameters['select'] : [];
        if ($select = InputHelper::cleanArray($selectdata)) {
            $args['select']              = $select;
            $this->customSelectRequested = true;
        }

        if ($where = $this->getWhereFromRequest()) {
            $args['filter']['where'] = $where;
        }

        if ($order = $this->getOrderFromRequest()) {
            $args['filter']['order'] = $order;
        }

        $results = $this->model->getEntities($args);

        $fielddata = [];
        foreach ($results['results'] as $key => $entityvalue) {
            $fielddata[$key] = $entityvalue->getProfileFields();
        }

        list($entities, $totalCount) = $this->prepareEntitiesForView($results);

        $modifiedentities = [];
        $modifieddata     = [];

        foreach ($entities as $entity) {
            $leadId = $entity->getId();
            //$profilefields[] = $entity->getProfileFields();
            $listoptinmodel      = $this->factory->getModel('lead.listoptin');
            $listoptinrepository = $this->factory->getModel('lead.listoptin')->getListLeadRepository();
            $listId              = $listoptinrepository->getListIDbyLeads($leadId);
            $listdatas           = [];
            foreach ($listId as $key => $detail) {
                foreach ($detail as $name => $value) {
                    $listentity            = $listoptinmodel->getEntity($value);
                    $listdata['id']        = $listentity->getId();
                    $listdata['name']      = $listentity->getName();
                    $listdata['listtype']  = $listentity->getListType();
                    $listdata['dateAdded'] = $listentity->getDateAdded();
                }
                $listdatas[] = $listdata;
            }
            $modifieddata[] = [$entity->getId() => $entity, 'all' => $fielddata[$entity->getId()], 'listoptin' => $listdatas];
        }
        $modifiedentities[] = $modifieddata;

        $view = $this->view(
            [
                'total'                => $totalCount,
                $this->entityNameMulti => $modifiedentities,
            ],
            Codes::HTTP_OK
        );
        $this->setSerializationContext($view);

        return $this->handleView($view);
    }

    /**
     * Obtains a specific entity as defined by the API URL.
     *
     * @param int $id Entity ID
     *
     * @return Response
     */
    public function getEntityAction($id)
    {
        $args = [];
        if ($select = InputHelper::cleanArray($this->request->get('select', []))) {
            $args['select']              = $select;
            $this->customSelectRequested = true;
        }

        if (!empty($args)) {
            $args['id'] = $id;
            $entity     = $this->model->getEntity($args);
        } else {
            $entity = $this->model->getEntity($id);
        }

        if (!$entity instanceof $this->entityClass) {
            return $this->returnError('le.core.error.id.notfound', Codes::HTTP_NOT_FOUND, [], ['%id%'=> $id]); // Previous it was
        }

        if (!$this->checkEntityAccess($entity, 'view')) {
            return $this->accessDenied();
        }
        $leadId              = $entity->getId();
        $listoptinmodel      = $this->factory->getModel('lead.listoptin');
        $listoptinrepository = $this->factory->getModel('lead.listoptin')->getListLeadRepository();
        $listId              = $listoptinrepository->getListIDbyLeads($leadId);
        $listdatas           = [];
        foreach ($listId as $key => $detail) {
            foreach ($detail as $name => $value) {
                $listentity            = $listoptinmodel->getEntity($value);
                $listdata['id']        = $listentity->getId();
                $listdata['name']      = $listentity->getName();
                $listdata['listtype']  = $listentity->getListType();
                $listdata['dateAdded'] = $listentity->getDateAdded();
            }
            $listdatas[] = $listdata;
        }
        $modifieddata[$entity->getId()] = $entity;
        $modifieddata['all']            = $entity->getProfileFields();
        $modifieddata['listoptin']      = $listdatas;
        $this->preSerializeEntity($modifieddata);
        $view = $this->view([$this->entityNameOne => $modifieddata], Codes::HTTP_OK);
        $this->setSerializationContext($view);

        return $this->handleView($view);
    }

    /**
     * Obtains a specific entity as defined by the API URL.
     *
     * @return Response
     */
    public function getLeadEntityAction()
    {
        $email = $this->request->get('email');

        $result = $this->model->findEmail($email);

        if (!count($result) > 0) {
            return $this->notFound('le.core.contact.error.notfound');
        }

        $entity = $this->model->getEntity($result[0]->getId());

        if (!$this->checkEntityAccess($entity, 'view')) {
            return $this->accessDenied();
        }

        $leadId              = $entity->getId();
        $listoptinmodel      = $this->getModel('lead.listoptin');
        $listoptinrepository = $this->getModel('lead.listoptin')->getListLeadRepository();
        $listId              = $listoptinrepository->getListIDbyLeads($leadId);
        $listdatas           =   [];
        $listdata            = [];
        foreach ($listId as $key => $detail) {
            foreach ($detail as $name => $value) {
                $listentity            = $listoptinmodel->getEntity($value);
                $listdata['id']        = $listentity->getId();
                $listdata['name']      = $listentity->getName();
                $listdata['listtype']  = $listentity->getListType();
                $listdata['dateAdded'] = $listentity->getDateAdded();
            }
            $listdatas[] = $listdata;
        }
        $modifieddata[$entity->getId()] = $entity;
        $modifieddata['all']            = $entity->getProfileFields();
        $modifieddata['listoptin']      = $listdatas;

        $this->preSerializeEntity($modifieddata);
        $view = $this->view([$this->entityNameOne => $modifieddata], Codes::HTTP_OK);
        $this->setSerializationContext($view);

        return $this->handleView($view);
    }

    /**
     * Deletes an entity.
     *
     * @param int $id Entity ID
     *
     * @return Response
     */
    public function deleteLeadEntityAction()
    {
        $email = $this->request->get('email');

        $result = $this->model->findEmail($email);

        if (!count($result) > 0) {
            return $this->notFound('le.core.contact.error.notfound');
        }

        $entity = $this->model->getEntity($result[0]->getId());

        $currentMonth     =date('Y-m');
        if (null !== $entity) {
            if (!$this->checkEntityAccess($entity, 'delete')) {
                return $this->accessDenied();
            }

            $this->model->deleteEntity($entity);

            $this->get('mautic.helper.licenseinfo')->intRecordCount('1', false);
            $this->get('mautic.helper.licenseinfo')->intDeleteCount('1', true);
            $this->get('mautic.helper.licenseinfo')->intDeleteMonth($currentMonth);

            $this->preSerializeEntity($entity);
            $view = $this->view([$this->entityNameOne => $entity], Codes::HTTP_OK);
            $this->setSerializationContext($view);

            return  $this->handleView($this->view(['success' => 1], Codes::HTTP_OK)); //previous it was $this->handleView($view);
        }

        return $this->notFound();
    }

    /**
     * Edits an existing entity or creates one on PUT if it doesn't exist.
     *
     * @param int $id Entity ID
     *
     * @return Response
     */
    public function editEntityAction($id)
    {
        $entity     = $this->model->getEntity($id);
        $parameters = $this->request->request->all();
        $method     = $this->request->getMethod();

        if ($entity === null || !$entity->getId()) {
            if ($method === 'PATCH') {
                //PATCH requires that an entity exists
                return $this->notFound();
            }
            if (isset($parameters['email'])) {
                $result = $this->model->getRepository()->findBy([
                        'email' => $parameters['email'],
                    ]);

                if (count($result) > 0) {
                    $entity = $this->model->getEntity($result[0]->getId());
                    if (!$this->checkEntityAccess($entity, 'create')) {
                        return $this->accessDenied();
                    }

                    return $this->processForm($entity, $parameters, $method);
                }
            }
            //PUT can create a new entity if it doesn't exist
            $entity = $this->model->getEntity();
            if (!$this->checkEntityAccess($entity, 'create')) {
                return $this->accessDenied();
            }
        }

        if (!$this->checkEntityAccess($entity, 'edit')) {
            return $this->accessDenied();
        }

        return $this->processForm($entity, $parameters, $method);
    }

    /**
     * Obtains a list of tags member of a specific lead.
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getLeadTagsAction()
    {
        $email = $this->request->get('email');

        $result = $this->model->findEmail($email);

        if (!count($result) > 0) {
            return $this->notFound('le.core.contact.error.notfound');
        }
        $id     = $result[0]->getId();
        $entity = $this->model->getEntity($id);

        if ($entity !== null) {
            if (!$this->get('mautic.security')->hasEntityAccess('lead:leads:viewown', 'lead:leads:viewother', $entity->getPermissionUser())) {
                return $this->accessDenied();
            }

            $tags = $entity->getTags();

            $view = $this->view(
                [
                    'total'    => count($tags),
                    'tags'     => $tags,
                ],
                Codes::HTTP_OK
            );

            return $this->handleView($view);
        }

        return $this->notFound();
    }

    /**
     * Obtains a list of lists member of a specific lead.
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getLeadListOptinAction()
    {
        $email = $this->request->get('email');

        $result = $this->model->findEmail($email);

        if (!count($result) > 0) {
            return $this->notFound('le.core.contact.error.notfound');
        }
        $id     = $result[0]->getId();
        $entity = $this->model->getEntity($id);

        if ($entity !== null) {
            if (!$this->get('mautic.security')->hasEntityAccess('lead:leads:viewown', 'lead:leads:viewother', $entity->getPermissionUser())) {
                return $this->accessDenied();
            }

            $listoptin    = $this->getModel('lead.listoptin')->getListOptinByLead($id);
            $listentities = [];
            foreach ($listoptin as $key => $value) {
                $listentity     = $this->getModel('lead.listoptin')->getEntity($value['leadlist_id']);
                $listentities[] = $listentity;
            }

            $view = $this->view(
                [
                    'total'     => count($listentities),
                    'lists'     => $listentities,
                ],
                Codes::HTTP_OK
            );

            return $this->handleView($view);
        }

        return $this->notFound();
    }

    /**
     * Obtains a list of drips member of a specific lead.
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getLeadDripAction()
    {
        $email = $this->request->get('email');

        $result = $this->model->findEmail($email);

        if (!count($result) > 0) {
            return $this->notFound('le.core.contact.error.notfound');
        }
        $id     = $result[0]->getId();
        $entity = $this->model->getEntity($id);

        if ($entity !== null) {
            if (!$this->get('mautic.security')->hasEntityAccess('lead:leads:viewown', 'lead:leads:viewother', $entity->getPermissionUser())) {
                return $this->accessDenied();
            }

            $driplist = $this->getModel('email.dripemail')->getDripByLead($id);

            $dripentities = [];
            foreach ($driplist as $key => $value) {
                $dripentity     = $this->getModel('email.dripemail')->getEntity($value['dripId']);
                $dripentities[] = $dripentity;
            }

            $view = $this->view(
                [
                    'total'     => count($dripentities),
                    'drips'     => $dripentities,
                ],
                Codes::HTTP_OK
            );

            return $this->handleView($view);
        }

        return $this->notFound();
    }
}
