<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Controller\Api;

use FOS\RestBundle\Util\Codes;
use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\LeadBundle\Controller\LeadAccessTrait;
use Mautic\LeadBundle\Entity\Lead;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class EmailApiController.
 */
class EmailApiController extends CommonApiController
{
    use LeadAccessTrait;

    public function initialize(FilterControllerEvent $event)
    {
        $this->model            = $this->getModel('email');
        $this->entityClass      = 'Mautic\EmailBundle\Entity\Email';
        $this->entityNameOne    = 'email';
        $this->entityNameMulti  = 'emails';
        $this->serializerGroups = ['emailDetails', 'categoryBasicList', 'publishDetails', 'assetList', 'formList', 'leadListList'];
        $this->dataInputMasks   = [
            'customHtml'     => 'html',
            'dynamicContent' => [
                'content' => 'html',
                'filters' => [
                    'content' => 'html',
                ],
            ],
        ];

        parent::initialize($event);
    }

    /**
     * Obtains a list of emails.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEntitiesAction()
    {
        //get parent level only
        $this->listFilters[] = [
            'column' => 'e.variantParent',
            'expr'   => 'isNull',
        ];

        return parent::getEntitiesAction();
    }

    /**
     * Sends the email to it's assigned lists.
     *
     * @param int $id Email ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function sendAction($id)
    {
        $entity = $this->model->getEntity($id);

        if (null !== $entity || !$entity->isPublished()) {
            return $this->notFound();
        }

        if (!$this->checkEntityAccess($entity, 'view')) {
            return $this->accessDenied();
        }

        $lists = $this->request->request->get('lists', null);
        $limit = $this->request->request->get('limit', null);

        list($count, $failed) = $this->model->sendEmailToLists($entity, $lists, $limit);

        $view = $this->view(
            [
                'success'          => 1,
                'sentCount'        => $count,
                'failedRecipients' => $failed,
            ],
            Codes::HTTP_OK
        );

        return $this->handleView($view);
    }

    /**
     * Sends the email to a specific lead.
     *
     * @param int $id     Email ID
     * @param int $leadId Lead ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function sendLeadAction($id, $leadId)
    {
        $entity = $this->model->getEntity($id);
        if (null !== $entity) {
            if (!$this->checkEntityAccess($entity, 'view')) {
                return $this->accessDenied();
            }

            /** @var Lead $lead */
            $lead = $this->checkLeadAccess($leadId, 'edit');
            if ($lead instanceof Response) {
                return $lead;
            }

            $post     = $this->request->request->all();
            $tokens   = (!empty($post['tokens'])) ? $post['tokens'] : [];
            $response = ['success' => false];

            $cleanTokens = [];

            foreach ($tokens as $token => $value) {
                $value = InputHelper::clean($value);
                if (!preg_match('/^{.*?}$/', $token)) {
                    $token = '{'.$token.'}';
                }

                $cleanTokens[$token] = $value;
            }

            $leadFields = array_merge(['id' => $leadId], $lead->getProfileFields());

            $result = $this->model->sendEmail(
                $entity,
                $leadFields,
                [
                    'source'        => ['api', 0],
                    'tokens'        => $cleanTokens,
                    'return_errors' => true,
                ]
            );

            if (is_bool($result)) {
                $response['success'] = $result;
            } else {
                $response['failed'] = $result;
            }

            $view = $this->view($response, Codes::HTTP_OK);

            return $this->handleView($view);
        }

        return $this->notFound();
    }

    public function getOneoffListAction()
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

        $this->listFilters[] = [
            'column' => 'e.variantParent',
            'expr'   => 'isNull',
        ];
        $this->listFilters[] = [
            'column'    => $tableAlias.'.emailType',
                'expr'  => 'eq',
                'value' => 'list',
        ];

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
        if ($publishedOnly != '' && !is_bool($publishedOnly)) {
            return $this->returnError('le.core.error.input.invalid', Codes::HTTP_BAD_REQUEST, [], ['%field%' => 'published']);
        }

        if ($orderBy != '') {
            $validOrderByFields = ['id', 'dateAdded', 'dateModified', 'createdBy', 'createdByUser', 'modifiedBy', 'modifiedByUser', 'points', 'city', 'zipcode', 'country', 'company_new', 'lastActive', 'fromAddress', 'fromName', 'replyToAddress', 'bccAddress', 'listtype', 'webhookUrl'];

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

        $payload = ['start' => $args['start'], 'limit' => $args['limit'], 'total' => $totalCount];

        $view = $this->view(
            [
                'payload'              => $payload,
                'broadcasts'           => $entities,
            ],
            Codes::HTTP_OK
        );
        $this->setSerializationContext($view);

        return $this->handleView($view);
    }

    public function getOneoffAction($id)
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
            return $this->returnError('le.core.error.id.notfound', Codes::HTTP_NOT_FOUND, [], ['%id%'=> $id]); // Previous it was return $this->notFound();
        }

        if ($entity->getEmailType() !== 'list') {
            return $this->returnError('le.core.error.id.notfound', Codes::HTTP_NOT_FOUND, [], ['%id%'=> $id]); // Previous it was return $this->notFound();
        }

        if (!$this->checkEntityAccess($entity, 'view')) {
            return $this->accessDenied();
        }

        $this->preSerializeEntity($entity);
        $view = $this->view(['broadcast' => $entity], Codes::HTTP_OK);
        $this->setSerializationContext($view);

        return $this->handleView($view);
    }
}
