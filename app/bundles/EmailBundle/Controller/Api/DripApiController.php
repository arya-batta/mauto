<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 7/3/19
 * Time: 3:15 PM.
 */

namespace Mautic\EmailBundle\Controller\Api;

use FOS\RestBundle\Util\Codes;
use Mautic\ApiBundle\Controller\CommonApiController;
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
        if (strpos($this->request->getRequestUri(), '/active') !== false) {
            $this->fliterCommands = 'drip_lead:'.$id;
        } elseif (strpos($this->request->getRequestUri(), '/completed') !== false) {
            $this->fliterCommands = 'drip_sent:'.$id;
        }

        return parent::getEntityByStatusAction($id);
    }
}
