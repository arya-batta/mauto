<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\Model;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Model\AjaxLookupModelInterface;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\PluginBundle\Entity\Slack;
use Mautic\PluginBundle\Event\SlackEvent;
use Mautic\PluginBundle\SlackEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class SlackModel
 * {@inheritdoc}
 */
class SlackModel extends FormModel implements AjaxLookupModelInterface
{
    /**
     * @var MauticFactory
     */
    protected $factory;

    /**
     * SlackModel constructor.
     *
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Mautic\PluginBundle\Entity\SlackRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticPluginBundle:Slack');
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        return 'sms:smses';
    }

    /**
     * Save an array of entities.
     *
     * @param  $entities
     * @param  $unlock
     *
     * @return array
     */
    public function saveEntities($entities, $unlock = true)
    {
        //iterate over the results so the events are dispatched on each delete
        $batchSize = 20;
        foreach ($entities as $k => $entity) {
            $isNew = ($entity->getId()) ? false : true;

            //set some defaults
            $this->setTimestamps($entity, $isNew, $unlock);

            if ($dispatchEvent = $entity instanceof Sms) {
                $event = $this->dispatchEvent('pre_save', $entity, $isNew);
            }

            $this->getRepository()->saveEntity($entity, false);

            if ($dispatchEvent) {
                $this->dispatchEvent('post_save', $entity, $isNew, $event);
            }

            if ((($k + 1) % $batchSize) === 0) {
                $this->em->flush();
            }
        }
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     *
     * @param       $entity
     * @param       $formFactory
     * @param null  $action
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws MethodNotAllowedHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof Slack) {
            throw new MethodNotAllowedHttpException(['slack']);
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('slack', $entity, $options);
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return null|Slack
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            $entity = new Slack();
        } else {
            $entity = parent::getEntity($id);
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     *
     * @param $action
     * @param $event
     * @param $entity
     * @param $isNew
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof Slack) {
            throw new MethodNotAllowedHttpException(['Slack']);
        }

        switch ($action) {
            case 'pre_save':
                $name = SlackEvents::SLACK_PRE_SAVE;
                break;
            case 'post_save':
                $name = SlackEvents::SLACK_POST_SAVE;
                break;
            case 'pre_delete':
                $name = SlackEvents::SLACK_PRE_DELETE;
                break;
            case 'post_delete':
                $name = SlackEvents::SLACK_POST_DELETE;
                break;
            default:
                return;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new SlackEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        } else {
            return;
        }
    }

    /**
     * @param        $type
     * @param string $filter
     * @param int    $limit
     * @param int    $start
     * @param array  $options
     *
     * @return array
     */
    public function getLookupResults($type, $filter = '', $limit = 10, $start = 0, $options = [])
    {
        $results = [];
        switch ($type) {
            case 'slack':
                $entities = $this->getRepository()->getSlackList(
                    $filter,
                    $limit,
                    $start
                );

                foreach ($entities as $entity) {
                    $results[$entity['id']] = $entity['name'];
                }

                //sort by language
                ksort($results);

                break;
        }

        return $results;
    }
}
