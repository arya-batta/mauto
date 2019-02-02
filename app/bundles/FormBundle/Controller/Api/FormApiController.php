<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\FormBundle\Controller\Api;

use FOS\RestBundle\Util\Codes;
use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\CoreBundle\Helper\InputHelper;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class FormApiController.
 */
class FormApiController extends CommonApiController
{
    /**
     * {@inheritdoc}
     */
    public function initialize(FilterControllerEvent $event)
    {
        $this->model            = $this->getModel('form');
        $this->entityClass      = 'Mautic\FormBundle\Entity\Form';
        $this->entityNameOne    = 'form';
        $this->entityNameMulti  = 'forms';
        $this->serializerGroups = ['formDetails', 'categoryList', 'publishDetails'];

        parent::initialize($event);
    }

    /**
     * {@inheritdoc}
     */
    protected function preSerializeEntity(&$entity, $action = 'view')
    {
        $entity->automaticJs = '<script type="text/javascript" src="'.$this->generateUrl('le_form_generateform', ['id' => $entity->getId()], true).'"></script>';
    }

    /**
     * Delete fields from a form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteFieldsAction($formId)
    {
        if (!$this->security->isGranted(['form:forms:editown', 'form:forms:editother'], 'MATCH_ONE')) {
            return $this->accessDenied();
        }

        $entity = $this->model->getEntity($formId);

        if ($entity === null) {
            return $this->notFound();
        }

        $fieldsToDelete = $this->request->get('fields');

        if (!is_array($fieldsToDelete)) {
            return $this->badRequest('The fields attribute must be array.');
        }

        $this->model->deleteFields($entity, $fieldsToDelete);

        $view = $this->view([$this->entityNameOne => $entity]);

        return $this->handleView($view);
    }

    /**
     * Delete fields from a form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteActionsAction($formId)
    {
        if (!$this->security->isGranted(['form:forms:editown', 'form:forms:editother'], 'MATCH_ONE')) {
            return $this->accessDenied();
        }

        $entity = $this->model->getEntity($formId);

        if ($entity === null) {
            return $this->notFound();
        }

        $actionsToDelete = $this->request->get('actions');

        if (!is_array($actionsToDelete)) {
            return $this->badRequest('The actions attribute must be array.');
        }

        $currentActions = $entity->getActions();

        foreach ($currentActions as $currentAction) {
            if (in_array($currentAction->getId(), $actionsToDelete)) {
                $entity->removeAction($currentAction);
            }
        }

        $this->model->saveEntity($entity);

        $view = $this->view([$this->entityNameOne => $entity]);

        return $this->handleView($view);
    }

    /**
     * {@inheritdoc}
     */
    protected function preSaveEntity(&$entity, $form, $parameters, $action = 'edit')
    {
        $method      = $this->request->getMethod();
        $fieldModel  = $this->getModel('form.field');
        $actionModel = $this->getModel('form.action');
        $isNew       = false;

        // Set clean alias to prevent SQL errors
        $alias = $this->model->cleanAlias($entity->getName(), '', 10);
        $entity->setAlias($alias);

        // Set timestamps
        $this->model->setTimestamps($entity, true, false);

        if (!$entity->getId()) {
            $isNew = true;

            // Save the form first to get the form ID.
            // Using the repository function to not trigger the listeners twice.
            $this->model->getRepository()->saveEntity($entity);
        }

        $formId           = $entity->getId();
        $requestFieldIds  = [];
        $requestActionIds = [];
        $currentFields    = $entity->getFields();
        $currentActions   = $entity->getActions();

        // Add fields from the request
        if (!empty($parameters['fields']) && is_array($parameters['fields'])) {
            $aliases = $entity->getFieldAliases();

            foreach ($parameters['fields'] as &$fieldParams) {
                if (empty($fieldParams['id'])) {
                    // Create an unique ID if not set - the following code requires one
                    $fieldParams['id'] = 'new'.hash('sha1', uniqid(mt_rand()));
                    $fieldEntity       = $fieldModel->getEntity();
                } else {
                    $fieldEntity       = $fieldModel->getEntity($fieldParams['id']);
                    $requestFieldIds[] = $fieldParams['id'];
                }

                if (is_null($fieldEntity)) {
                    $msg = $this->translator->trans(
                        'mautic.core.error.entity.not.found',
                        [
                            '%entity%' => $this->translator->trans('mautic.form.field'),
                            '%id%'     => $fieldParams['id'],
                        ],
                        'flashes'
                    );

                    return $this->returnError($msg, Codes::HTTP_NOT_FOUND);
                }

                $fieldEntityArray           = $fieldEntity->convertToArray();
                $fieldEntityArray['formId'] = $formId;

                if (!empty($fieldParams['alias'])) {
                    $fieldParams['alias'] = InputHelper::filename($fieldParams['alias']);

                    if (!in_array($fieldParams['alias'], $aliases)) {
                        $fieldEntityArray['alias'] = $fieldParams['alias'];
                    }
                }

                if (empty($fieldEntityArray['alias'])) {
                    $fieldEntityArray['alias'] = $fieldParams['alias'] = $fieldModel->generateAlias($fieldEntityArray['label'], $aliases);
                }

                $fieldForm = $this->createFieldEntityForm($fieldEntityArray);
                $fieldForm->submit($fieldParams, 'PATCH' !== $method);

                if (!$fieldForm->isValid()) {
                    $formErrors = $this->getFormErrorMessages($fieldForm);
                    $msg        = $this->getFormErrorMessage($formErrors);

                    return $this->returnError($msg, Codes::HTTP_BAD_REQUEST);
                }
            }

            $this->model->setFields($entity, $parameters['fields']);
        }

        // Remove fields which weren't in the PUT request
        if (!$isNew && $method === 'PUT') {
            $fieldsToDelete = [];

            foreach ($currentFields as $currentField) {
                if (!in_array($currentField->getId(), $requestFieldIds)) {
                    $fieldsToDelete[] = $currentField->getId();
                }
            }

            if ($fieldsToDelete) {
                $this->model->deleteFields($entity, $fieldsToDelete);
            }
        }

        // Add actions from the request
        if (!empty($parameters['actions']) && is_array($parameters['actions'])) {
            foreach ($parameters['actions'] as &$actionParams) {
                if (empty($actionParams['id'])) {
                    $actionParams['id'] = 'new'.hash('sha1', uniqid(mt_rand()));
                    $actionEntity       = $actionModel->getEntity();
                } else {
                    $actionEntity       = $actionModel->getEntity($actionParams['id']);
                    $requestActionIds[] = $actionParams['id'];
                }

                $actionEntity->setForm($entity);

                $actionForm = $this->createActionEntityForm($actionEntity);
                $actionForm->submit($actionParams, 'PATCH' !== $method);

                if (!$actionForm->isValid()) {
                    $formErrors = $this->getFormErrorMessages($actionForm);
                    $msg        = $this->getFormErrorMessage($formErrors);

                    return $this->returnError($msg, Codes::HTTP_BAD_REQUEST);
                }
            }

            // Save the form first and new actions so that new fields are available to actions.
            // Using the repository function to not trigger the listeners twice.
            $this->model->getRepository()->saveEntity($entity);
            $this->model->setActions($entity, $parameters['actions']);
        }

        // Remove actions which weren't in the PUT request
        if (!$isNew && $method === 'PUT') {
            foreach ($currentActions as $currentAction) {
                if (!in_array($currentAction->getId(), $requestActionIds)) {
                    $entity->removeAction($currentAction);
                }
            }
        }
    }

    /**
     * Creates the form instance.
     *
     * @param $entity
     *
     * @return Form
     */
    protected function createActionEntityForm($entity)
    {
        return $this->getModel('form.action')->createForm(
            $entity,
            $this->get('form.factory'),
            null,
            [
                'csrf_protection'    => false,
                'allow_extra_fields' => true,
            ]
        );
    }

    /**
     * Creates the form instance.
     *
     * @param $entity
     *
     * @return Form
     */
    protected function createFieldEntityForm($entity)
    {
        return $this->getModel('form.field')->createForm(
            $entity,
            $this->get('form.factory'),
            null,
            [
                'csrf_protection'    => false,
                'allow_extra_fields' => true,
            ]
        );
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
                'orderBy'        => $this->addAliasIfNotPresent(isset($parameters['orderBy']) ? $parameters['orderBy'] : '', $tableAlias), //$this->addAliasIfNotPresent($this->request->query->get('orderBy', ''), $tableAlias),
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

        list($entities, $totalCount) = $this->prepareEntitiesForView($results);
        $specificvalues              = [];
        foreach ($entities as $entity) {
            $specificvalue['id']            = $entity->getId();
            $specificvalue['name']          = $entity->getName();
            $specificvalue['alias']         = $entity->getAlias();
            $specificvalue['isPublished']   = $entity->getIsPublished();
            $specificvalue['dateAdded']     = $entity->getDateAdded();
            $specificvalues[]               = $specificvalue;
        }

        $view = $this->view(
            [
                'total'                => $totalCount,
                $this->entityNameMulti => $specificvalues,
            ],
            Codes::HTTP_OK
        );
        $this->setSerializationContext($view);

        return $this->handleView($view);
    }
}
