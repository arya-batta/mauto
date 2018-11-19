<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\leFocusBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FocusController.
 */
class FocusController extends FormController
{
    public function __construct()
    {
        $this->setStandardParameters(
            'focus',
            'plugin:focus:items',
            'le_focus',
            'mautic_focus',
            'mautic.focus',
            'leFocusBundle:Focus',
            null,
            'focus'
        );
    }

    /**
     * @param int $page
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function indexAction($page = 1)
    {
        return parent::indexStandard($page);
    }

    /**
     * Generates new form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function newAction($objectid = '')
    {
        return parent::newStandard($objectid);
    }

    /**
     * Generates edit form and processes post data.
     *
     * @param int  $objectId
     * @param bool $ignorePost
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function editAction($objectId, $ignorePost = false)
    {
        return parent::editStandard($objectId, $ignorePost);
    }

    /**
     * Displays details on a Focus.
     *
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function viewAction($objectId)
    {
        return parent::viewStandard($objectId, 'focus', 'focus');
    }

    /**
     * Clone an entity.
     *
     * @param int $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function cloneAction($objectId)
    {
        return parent::cloneStandard($objectId);
    }

    /**
     * Deletes the entity.
     *
     * @param int $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        return parent::deleteStandard($objectId);
    }

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        return parent::batchDeleteStandard();
    }

    /**
     * @param $args
     * @param $view
     */
    public function customizeViewArguments($args, $view)
    {
        if ($view == 'view') {
            /** @var \MauticPlugin\leFocusBundle\Entity\Focus $item */
            $item = $args['viewParameters']['item'];

            // For line graphs in the view
            $dateRangeValues = $this->request->get('daterange', []);
            $dateRangeForm   = $this->get('form.factory')->create(
                'daterange',
                $dateRangeValues,
                [
                    'action' => $this->generateUrl(
                        'le_focus_action',
                        [
                            'objectAction' => 'view',
                            'objectId'     => $item->getId(),
                        ]
                    ),
                ]
            );

            /** @var \MauticPlugin\leFocusBundle\Model\FocusModel $model */
            $model = $this->getModel('focus');
            $stats = $model->getStats(
                $item,
                null,
                new \DateTime($dateRangeForm->get('date_from')->getData()),
                new \DateTime($dateRangeForm->get('date_to')->getData())
            );

            $args['viewParameters']['stats']         = $stats;
            $args['viewParameters']['dateRangeForm'] = $dateRangeForm->createView();

            if ('link' == $item->getType()) {
                $args['viewParameters']['trackables'] = $this->getModel('page.trackable')->getTrackableList('focus', $item->getId());
            }
        }

        return $args;
    }

    /**
     * @param array $args
     * @param       $action
     *
     * @return array
     */
    protected function getPostActionRedirectArguments(array $args, $action)
    {
        $updateSelect = ($this->request->getMethod() == 'POST')
            ? $this->request->request->get('focus[updateSelect]', false, true)
            : $this->request->get(
                'updateSelect',
                false
            );
        if ($updateSelect) {
            switch ($action) {
                case 'new':
                case 'edit':
                    $passthrough = $args['passthroughVars'];
                    $passthrough = array_merge(
                        $passthrough,
                        [
                            'updateSelect' => $updateSelect,
                            'id'           => $args['entity']->getId(),
                            'name'         => $args['entity']->getName(),
                        ]
                    );
                    $args['passthroughVars'] = $passthrough;
                    break;
            }
        }

        return $args;
    }

    /**
     * @return array
     */
    protected function getEntityFormOptions()
    {
        $updateSelect = ($this->request->getMethod() == 'POST')
            ? $this->request->request->get('focus[updateSelect]', false, true)
            : $this->request->get(
                'updateSelect',
                false
            );
        if ($updateSelect) {
            return ['update_select' => $updateSelect];
        }
    }

    /**
     * Return array of options update select response.
     *
     * @param string $updateSelect HTML id of the select
     * @param object $entity
     * @param string $nameMethod   name of the entity method holding the name
     * @param string $groupMethod  name of the entity method holding the select group
     *
     * @return array
     */
    protected function getUpdateSelectParams($updateSelect, $entity, $nameMethod = 'getName', $groupMethod = 'getLanguage')
    {
        $options = [
            'updateSelect' => $updateSelect,
            'id'           => $entity->getId(),
            'name'         => $entity->$nameMethod(),
        ];

        return $options;
    }

    /**
     * @param int $objectId
     */
    protected function cloneTemplateAction($objectId)
    {
        return parent::clonePopupsFromTemplateAction($objectId);
    }

    /**
     * @param array $args
     * @param       $action
     *
     * @return array
     */
    public function getViewArguments(array $args, $action)
    {
        $listFilters = [
            'filters' => [
                'placeholder' => $this->get('translator')->trans('le.category.filter.placeholder'),
                'multiple'    => true,
            ],
        ];

        // Reset available groups
        $listFilters['filters']['groups'] = [];

        $listFilters['filters']['groups']['mautic.core.filter.category'] = [
            'options' => $this->getModel('category.category')->getLookupResults('plugin:focus'),
            'prefix'  => 'category',
        ];

        switch ($action) {
            case 'index':
                $args['viewParameters']['filters']              = $listFilters;
                $args['viewParameters']['focusBlockDetails']    = $this->getModel('focus')->getFocusDisplayBlocks();
                break;
            case 'view':
                $args = $this->customizeViewArguments($args, $action);
                break;
        }

        return $args;
    }

    /**
     * @param       $start
     * @param       $limit
     * @param       $filter
     * @param       $orderBy
     * @param       $orderByDir
     * @param array $args
     */
    protected function getIndexItems($start, $limit, $filter, $orderBy, $orderByDir, array $args = [])
    {
        $updatedFilters = $this->request->get('filters', false);

        if ($updatedFilters) {
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

        $this->get('session')->set('mautic.form.filter', []);
        if (!empty($currentFilters)) {
            $catIds = [];
            foreach ($currentFilters as $type => $typeFilters) {
                switch ($type) {
                    case 'category':
                        $key = 'categories';
                        break;
                }

                $listFilters['filters']['groups']['mautic.core.filter.'.$key]['values'] = $typeFilters;

                foreach ($typeFilters as $fltr) {
                    switch (true) {
                        case 'category':
                            $catIds[] = (int) $fltr;
                            break;
                    }
                }
            }

            if (!empty($catIds)) {
                $filter['force'][] = ['column' => 'c.id', 'expr' => 'in', 'value' => $catIds];
            }
        }

        return parent::getIndexItems(
            $start,
            $limit,
            $filter,
            $orderBy,
            $orderByDir,
            $args
        );
    }
}
