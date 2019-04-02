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
use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\LeadBundle\Controller\LeadAccessTrait;
use Mautic\LeadBundle\Entity\Tag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class TagApiController extends CommonApiController
{
    use LeadAccessTrait;

    public function initialize(FilterControllerEvent $event)
    {
        $this->model            = $this->getModel('lead.tag');
        $this->entityClass      = Tag::class;
        $this->entityNameOne    = 'tag';
        $this->entityNameMulti  = 'tags';
        $this->serializerGroups = ['publishDetails', 'leadBasicList', 'tagList'];
        parent::initialize($event);
    }

    /**
     * Creates new entity from provided params.
     *
     * @param array $params
     *
     * @return object
     *
     * @throws \InvalidArgumentException
     */
    public function getNewEntity(array $params)
    {
        if (empty($params[$this->entityNameOne])) {
            throw new \InvalidArgumentException(
                $this->get('translator')->trans('le.lead.api.tag.required', [], 'validators')
            );
        }

        return $this->model->getRepository()->getTagByNameOrCreateNewOne($params[$this->entityNameOne]);
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

            //PUT can create a new entity if it doesn't exist
            $entity = $this->model->getEntity();
            if (isset($parameters['tag'])) {
                $alias=str_replace(' ', '_', $parameters['tag']);
                $entity->setAlias($alias);
            }
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
     * Obtains a list of leads contains a specific tag.
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getLeadsByTagAction($id)
    {
        $entity     = $this->model->getEntity($id);

        if ($entity === null || !$entity->getId()) {
            return $this->notFound();
        }

        if (!$this->checkEntityAccess($entity, 'view')) {
            return $this->accessDenied();
        }

        $leads = $this->model->getLeadsBytag($id);

        $entities = [];
        foreach ($leads as $key => $val) {
            foreach ($val as $key1 => $val1) {
                $leadentity = $this->getModel('lead.lead')->getEntity($val1);
                $entities[] = $leadentity;
            }
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
     * Add or remove a tag to a lead.
     *
     * @param int $id     Tag ID
     * @param int $leadId Lead ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function addOrRemoveTagAction($id, $leadId =null)
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
        $tag =  $this->model->getEntity($id);

        $leadEntity = $this->getModel('lead')->getEntity($leadId);

        if (strpos($this->request->getRequestUri(), '/add') !== false) {
            $leadEntity->addTag($tag);
        } else {
            $leadEntity->removeTag($tag);
        }

        $this->getModel('lead')->saveEntity($leadEntity);

        if ($this->inBatchMode) {
            $this->inBatchMode = false;

            return;
        }

        $view = $this->view(['success' => 1], Codes::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * Add Or Remove Batch of Tags to Leads.
     *
     * @return array|Response
     */
    public function addOrRemoveBatchTagsAction()
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
            if (isset($params['tagId']) && !is_numeric($params['tagId'])) {
                $this->setBatchError($key, 'le.core.error.input.invalid', Codes::HTTP_BAD_REQUEST, $errors, $entities, $entity, ['%field%' => 'tagId']);
                $statusCodes[$key] = Codes::HTTP_BAD_REQUEST;
                continue;
            }

            $entity = $this->model->getEntity($params['tagId']);
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
            $this->addOrRemoveTagAction($params['tagId'], $leadId);
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
}
