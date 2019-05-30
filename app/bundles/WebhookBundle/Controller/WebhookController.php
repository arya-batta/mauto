<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\WebhookBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;

/**
 * Class WebhookController.
 */
class WebhookController extends FormController
{
    public function __construct()
    {
        $this->setStandardParameters(
            'webhook.webhook', // model name
            'webhook:webhooks', // permission base
            'le_webhook', // route base
            'mautic_webhook', // session base
            'mautic.webhook', // lang string base
            'MauticWebhookBundle:Webhook', // template base
            'le_webhook', // activeLink
            'Webhook' // leContent
        );
    }

    /**
     * @param int $page
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction($page = 1)
    {
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }

        return parent::indexStandard($page);
    }

    /**
     * Generates new form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function newAction()
    {
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }

        return parent::newStandard();
    }

    /**
     * Generates edit form and processes post data.
     *
     * @param int  $objectId
     * @param bool $ignorePost
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function editAction($objectId, $ignorePost = false)
    {
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }

        return parent::editStandard($objectId, $ignorePost);
    }

    /**
     * Displays details on a Focus.
     *
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewAction($objectId)
    {
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }

        return parent::viewStandard($objectId, 'webhook', 'webhook');
    }

    /**
     * Clone an entity.
     *
     * @param int $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
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
}
