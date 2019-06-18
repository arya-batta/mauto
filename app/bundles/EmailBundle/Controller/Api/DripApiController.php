<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 7/3/19
 * Time: 3:15 PM.
 */

namespace Mautic\EmailBundle\Controller\Api;

use FOS\RestBundle\Util\Codes;
use JMS\Serializer\SerializationContext;
use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\LeadBundle\Controller\LeadAccessTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class DripApiController extends CommonApiController
{
    use LeadAccessTrait;

    public function initialize(FilterControllerEvent $event)
    {
        $this->model            = $this->getModel('email.dripemail');
        $this->entityClass      = 'Mautic\EmailBundle\Entity\DripEmail';
        $this->entityNameOne    = 'drip';
        $this->entityNameMulti  = 'drips';
        $this->serializerGroups = ['leadBasicList', 'emailDetails', 'dripemailDetails', 'categoryBasicList', 'publishDetails', 'assetList', 'formList', 'leadListList'];

        parent::initialize($event);
    }

    /**
     * Add Or Remove a lead to a Drip.
     *
     * @param int $id     Drip ID
     * @param int $leadId Lead ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function addOrRemoveLeadAction($id, $leadId = null)
    {
        $entity = $this->model->getEntity($id);

        if (!$this->inBatchMode) {
            $email = $this->request->get('email');

            if (null === $entity) {
                return $this->notFound();
            }

            $leadModel = $this->getModel('lead');
            $result    = $leadModel->findEmail($email);

            if (!count($result) > 0) {
                return $this->notFound('le.core.contact.error.notfound');
            }

            $lead   = $result[0];
            $leadId = $lead->getId();

            $contact = $this->checkLeadAccess($leadId, 'edit');
            if ($contact instanceof Response) {
                return $contact;
            }
        }

        $leadentity = $this->getModel('lead')->getEntity($leadId);

        if (strpos($this->request->getRequestUri(), '/add') !== false) {
            $this->model->addLeadToDrip($entity, $leadentity);
        } else {
            $this->model->removeLead($entity->getId(), $leadentity->getId());
        }

        if ($this->inBatchMode) {
            $this->inBatchMode = false;

            return;
        }

        $view = $this->view(['success' => 1], Codes::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Add Or Remove Batch of Leads to Drip.
     *
     * @return array|Response
     */
    public function addOrRemoveBatchLeadsAction()
    {
        $parameters = $this->request->request->all();

        $valid = $this->validateBatchPayload($parameters);
        if ($valid instanceof Response) {
            return $valid;
        }

        $this->inBatchMode = true;
        $entities          = [];
        $entity            = [];
        $errors            = [];
        $statusCodes       = [];
        foreach ($parameters as $key => $params) {
            if (isset($params['dripId']) && !is_numeric($params['dripId'])) {
                $this->setBatchError($key, 'le.core.error.input.invalid', Codes::HTTP_BAD_REQUEST, $errors, $entities, $entity, ['%field%' => 'dripId']);
                $statusCodes[$key] = Codes::HTTP_BAD_REQUEST;
                continue;
            }

            $entity = $this->model->getEntity($params['dripId']);
            $result = $this->getModel('lead')->findEmail($params['email']);

            if (null === $entity) {
                $this->setBatchError($key, 'mautic.core.error.notfound', Codes::HTTP_NOT_FOUND, $errors, $entities, $entity);
                $statusCodes[$key] = $key.':Failed'; //Codes::HTTP_NOT_FOUND;
                continue;
            }

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
            $this->addOrRemoveLeadAction($params['dripId'], $leadId);
            $statusCodes[$key] = $key.':Success'; //Codes::HTTP_OK;
            $this->getDoctrine()->getManager()->detach($entity);
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
     * Get Specific Leads by provided Drip Id.
     *
     * @param int $id Drip ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getLeadsByDripAction($id)
    {
        $entity     = $this->model->getEntity($id);

        if ($entity === null || !$entity->getId()) {
            return $this->notFound();
        }

        if (!$this->checkEntityAccess($entity, 'view')) {
            return $this->accessDenied();
        }

        $leads = $this->model->getLeadIdsByDrip($entity);

        $entities = [];

        foreach ($leads as $key => $value) {
            $leadentity = $this->getModel('lead')->getEntity($value['lead_id']);
            $entities[] = $leadentity;
        }

        $totalCount = count($entities);
        $view       = $this->view(
            [
                'total' => $totalCount,
                'leads' => $entities,
            ],
            Codes::HTTP_OK
        );

        $context = SerializationContext::create()->setGroups(['leadBasicApiDetails']);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Get an entity by a specific status.
     *
     * @param $id
     *
     * @return Response
     */
    public function getEntityByStatusAction($id)
    {
        if (strpos($this->request->getRequestUri(), '/active') !== false) {
            $this->fliterCommands = 'drip_lead:'.$id;
        } elseif (strpos($this->request->getRequestUri(), '/completed') !== false) {
            $this->fliterCommands = 'drip_sent:'.$id;
        }

        return parent::getEntityByStatusAction($id);
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
        $args          = [];
        $repo          = $this->model->getRepository();
        $tableAlias    = $repo->getTableAlias();
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
            return $this->returnError('le.core.error.id.notfound', Codes::HTTP_NOT_FOUND, [], ['%id%'=> $id]); // Previous it was return $this->notFound();
        }

        if (!$this->checkEntityAccess($entity, 'view')) {
            return $this->accessDenied();
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

        $sheduledate = $entity->getScheduleDate();
        $value       = $this->factory->getHelper('template.date')->toCustDate($sheduledate, '', 'H:i:s P'); //'H:i:s e P' shows "11:00:00 UTC +00:00"
        $entity->setScheduleDate($value);

        $summary                     = [];
        $summaryData                 = $this->model->getDripEmailStats($id, false);
        $summary['sentCount']        = $summaryData['sentcount'];
        $summary['openCount']        = $summaryData['readcount'];
        $summary['clickCount']       = $summaryData['clickcount'];
        $summary['unsubscribeCount'] = $summaryData['unsubscribe'];
        $entityData[$id]             = [$entity, $summary];
        $this->preSerializeEntity($entityData);
        $view = $this->view([$this->entityNameOne => $entityData], Codes::HTTP_OK);
        $this->setSerializationContext($view);

        return $this->handleView($view);
    }

    /**
     * Obtains a list of entities as defined by the API URL.
     *
     * @return Response
     */
    public function getEntitiesAction()
    {
        $parameters    = $this->request->request->all();
        $repo          = $this->model->getRepository();
        $tableAlias    = $repo->getTableAlias();
        //$publishedOnly = $this->request->get('published', 0);
        //$minimal       = $this->request->get('minimal', 0);
        $publishedOnly = isset($parameters['published']) ? $parameters['published'] : 0;
        $minimal       = isset($parameters['minimal']) ? $parameters['minimal'] : 0;
        $orderBy       = isset($parameters['orderBy']) ? $parameters['orderBy'] : '';
        $orderByDir    = isset($parameters['orderByDir']) ? $parameters['orderByDir'] : 'ASC';

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

        if (isset($parameters['start']) && $parameters['start'] != '' && !is_numeric($parameters['start'])) {
            return $this->returnError('le.core.error.input.invalid', Codes::HTTP_BAD_REQUEST, [], ['%field%' => 'start']);
        }
        if (isset($parameters['limit']) && $parameters['limit'] != '' && !is_numeric($parameters['limit'])) {
            return $this->returnError('le.core.error.input.invalid', Codes::HTTP_BAD_REQUEST, [], ['%field%' => 'limit']);
        }
        if ($publishedOnly != '' && $publishedOnly != 'true' && $publishedOnly != 'false' && $publishedOnly != 1 && $publishedOnly != 0 && !is_bool($publishedOnly)) {
            return $this->returnError('le.core.error.input.invalid', Codes::HTTP_BAD_REQUEST, [], ['%field%' => 'published']);
        }
        if ($orderBy != '') {
            $validOrderByFields = ['id', 'dateAdded', 'dateModified', 'createdBy', 'createdByUser', 'modifiedBy', 'modifiedByUser', 'points', 'city', 'zipcode', 'country', 'company_name', 'lastActive', 'fromAddress', 'fromName', 'replyToAddress', 'bccAddress', 'listtype', 'webhookUrl'];

            if (!in_array($orderBy, $validOrderByFields)) {
                return $this->returnError('le.core.error.input.invalid', Codes::HTTP_BAD_REQUEST, [], ['%field%' => 'orderBy']);
            }
        }
        if ($orderByDir != '') {
            $validOrderByDirValues = ['asc', 'desc', 'ASC', 'DESC'];

            if (!in_array($orderByDir, $validOrderByDirValues)) {
                return $this->returnError('le.core.error.input.invalid', Codes::HTTP_BAD_REQUEST, [], ['%field%' => 'orderByDir']);
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
                'orderBy'        => $this->addAliasIfNotPresent($orderBy, $tableAlias), //$this->addAliasIfNotPresent($this->request->query->get('orderBy', ''), $tableAlias),
                'orderByDir'     => $orderByDir, //$this->request->query->get('orderByDir', 'ASC'),
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

        list($entities, $totalCount) = $this->prepareEntitiesForView($results);

        $modifieddata=[];
        foreach ($entities as $entity) {
            $sheduledate = $entity->getScheduleDate();
            $value       = $this->factory->getHelper('template.date')->toCustDate($sheduledate, '', 'H:i:sP'); //'H:i:s e P' shows "11:00:00 UTC +00:00"
            $entity->setScheduleDate($value);

            $dripId                      = $entity->getId();
            $summaryData                 = $this->model->getDripEmailStats($dripId, false);
            $summary['sentCount']        = $summaryData['sentcount'];
            $summary['openCount']        = $summaryData['readcount'];
            $summary['clickCount']       = $summaryData['clickcount'];
            $summary['unsubscribeCount'] = $summaryData['unsubscribe'];
            $modifieddata[$dripId]       = [$entity, $summary];
        }

        $payload = ['start' => $args['start'], 'limit' => $args['limit'], 'total' => $totalCount];
        $view    = $this->view(
            [
                'payload'              => $payload,
                $this->entityNameMulti => $modifieddata,
            ],
            Codes::HTTP_OK
        );
        $this->setSerializationContext($view);

        return $this->handleView($view);
    }
}
