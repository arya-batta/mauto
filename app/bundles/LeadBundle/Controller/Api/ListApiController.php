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
use Mautic\LeadBundle\Controller\LeadAccessTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class ListApiController.
 */
class ListApiController extends CommonApiController
{
    use LeadAccessTrait;

    public function initialize(FilterControllerEvent $event)
    {
        $this->model            = $this->getModel('lead.list');
        $this->entityClass      = 'Mautic\LeadBundle\Entity\LeadList';
        $this->entityNameOne    = 'list';
        $this->entityNameMulti  = 'lists';
        $this->serializerGroups = ['leadListDetails', 'userList', 'publishDetails', 'ipAddress', 'leadDetails', 'tagList'];

        parent::initialize($event);
    }

    /**
     * Obtains a list of smart lists for the user.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getListsAction()
    {
        $lists   = $this->getModel('lead.list')->getUserLists();
        $view    = $this->view($lists, Codes::HTTP_OK);
        $context = SerializationContext::create()->setGroups(['leadListList']);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Adds a lead to a list.
     *
     * @param int $id     List ID
     * @param int $leadId Lead ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function addLeadAction($id, $leadId =null)
    {
        $entity = $this->model->getEntity($id);

        if (!$this->inBatchMode) {
            $email = $this->request->get('email');

            if (null === $entity) {
                return $this->notFound('le.lead.list.notfound.error');
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

            // Does the user have access to the list
            $lists = $this->model->getUserLists();
            if (!isset($lists[$id])) {
                return $this->accessDenied();
            }
        }

        $this->getModel('lead')->addToLists($leadId, $entity);

        if ($this->inBatchMode) {
            $this->inBatchMode = false;

            return;
        }

        $view = $this->view(['success' => 1], Codes::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Removes given contact from a list.
     *
     * @param int $id     List ID
     * @param int $leadId Lead ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function removeLeadAction($id, $leadId =null)
    {
        $entity = $this->model->getEntity($id);

        if (!$this->inBatchMode) {
            $email = $this->request->get('email');

            if (null === $entity) {
                return $this->notFound('le.lead.list.notfound.error');
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

            // Does the user have access to the list
            $lists = $this->model->getUserLists();
            if (!isset($lists[$id])) {
                return $this->accessDenied();
            }
        }

        $this->getModel('lead')->removeFromLists($leadId, $entity);

        if ($this->inBatchMode) {
            $this->inBatchMode = false;

            return;
        }

        $view = $this->view(['success' => 1], Codes::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Add Or Remove Batch of Leads to Segments.
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
        $errors            = [];
        $statusCodes       = [];
        foreach ($parameters as $key => $params) {
            $entity = $this->model->getEntity($params['segmentId']);
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

            // Does the user have access to the list
            $lists = $this->model->getUserLists();
            if (!isset($lists[$params['segmentId']])) {
                $this->setBatchError($key, 'mautic.core.error.accessdenied', Codes::HTTP_FORBIDDEN, $errors, $entities, $leadentity);
                $statusCodes[$key] = $key.':Failed'; //Codes::HTTP_FORBIDDEN;
                continue;
            }

            $this->inBatchMode = true;
            if (strpos($this->request->getRequestUri(), '/add') !== false) {
                $this->addLeadAction($params['segmentId'], $leadId);
            } else {
                $this->removeLeadAction($params['segmentId'], $leadId);
            }
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
     * Checks if user has permission to access retrieved entity.
     *
     * @param mixed  $entity
     * @param string $action view|create|edit|publish|delete
     *
     * @return bool
     */
    protected function checkEntityAccess($entity, $action = 'view')
    {
        if ($action == 'create' || $action == 'edit' || $action == 'view') {
            return $this->security->isGranted('lead:leads:viewown');
        } elseif ($action == 'delete') {
            return $this->factory->getSecurity()->hasEntityAccess(
                true, 'lead:lists:deleteother', $entity->getCreatedBy()
            );
        }

        return parent::checkEntityAccess($entity, $action);
    }

    /**
     * Obtains a list of Leads member of a specific list.
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getLeadsByListAction($id)
    {
        $entity     = $this->model->getEntity($id);

        if ($entity === null || !$entity->getId()) {
            return $this->notFound();
        }

        if (!$this->checkEntityAccess($entity, 'view')) {
            return $this->accessDenied();
        }
        $list     = ['id' => $id];
        $leads    = $this->model->getLeadsByList($list, true);
        $entities = [];
        foreach ($leads[$id] as $key => $val) {
            $leadentity = $this->getModel('lead')->getEntity($val);
            $entities[] =  $leadentity;
        }

        $totalCount = count($entities);
        $view       = $this->view(
            [
                'total' => $totalCount,
                'leads' => $entities,
            ],
            Codes::HTTP_OK
        );
        $this->setSerializationContext($view);

        return $this->handleView($view);
    }
}
