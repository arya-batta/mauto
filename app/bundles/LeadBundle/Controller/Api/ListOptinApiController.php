<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 6/3/19
 * Time: 4:15 PM.
 */

namespace Mautic\LeadBundle\Controller\Api;

use FOS\RestBundle\Util\Codes;
use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\LeadBundle\Controller\LeadAccessTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ListOptinApiController extends CommonApiController
{
    use LeadAccessTrait;

    public function initialize(FilterControllerEvent $event)
    {
        $this->model            = $this->getModel('lead.listoptin');
        $this->entityClass      = 'Mautic\LeadBundle\Entity\LeadListOptIn';
        $this->entityNameOne    = 'listoptin';
        $this->entityNameMulti  = 'listoptins';
        $this->serializerGroups = ['leadListDetails', 'userList', 'publishDetails', 'ipAddress', 'leadBasicList', 'tagList'];

        parent::initialize($event);
    }

    /**
     * Add Or Remove specific Lead to Listoptions.
     *
     * @return array|Response
     */
    public function addOrRemoveLeadAction($id, $lead = null)
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
        $list = $this->model->getEntity($id);

        if (strpos($this->request->getRequestUri(), '/add') !== false) {
            $this->model->addLead($lead, $list, true);
        } else {
            $this->model->removeLead($lead, $list, true);
        }
        if ($this->inBatchMode) {
            $this->inBatchMode = false;

            return;
        }
        $view = $this->view(['success' => 1], Codes::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Add Or Remove Batch of Leads to Listoptions.
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
            $entity = $this->model->getEntity($params['listId']);
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
            $this->addOrRemoveLeadAction($params['listId'], $leadentity);
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
     * Get an entity by a specific status.
     *
     * @param $id
     *
     * @return Response
     */
    public function getEntityByStatusAction($id)
    {
        if (strpos($this->request->getRequestUri(), '/confirmed') !== false) {
            $this->fliterCommands = 'confirm_list:'.$id;
        } elseif (strpos($this->request->getRequestUri(), '/pending') !== false) {
            $this->fliterCommands = 'unconfirm_list:'.$id;
        } elseif (strpos($this->request->getRequestUri(), '/unsubscribed') !== false) {
            $this->fliterCommands = 'unsubscribe_list:'.$id;
        }

        return parent::getEntityByStatusAction($id);
    }
}
