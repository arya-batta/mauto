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
use Mautic\LeadBundle\Entity\LeadNote;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class NoteApiController.
 */
class NoteApiController extends CommonApiController
{
    use LeadAccessTrait;

    public function initialize(FilterControllerEvent $event)
    {
        $this->model            = $this->getModel('lead.note');
        $this->entityClass      = LeadNote::class;
        $this->entityNameOne    = 'note';
        $this->entityNameMulti  = 'notes';
        $this->serializerGroups = ['leadNoteDetails'];

        parent::initialize($event);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Mautic\LeadBundle\Entity\Lead &$entity
     * @param                                $parameters
     * @param                                $form
     * @param string                         $action
     */
    protected function preSaveEntity(&$entity, $form, $parameters, $action = 'edit')
    {
        if (!empty($parameters['email'])) {
            if ($action == 'new') {
                $action ='view';
            }
            $result = $this->getModel('lead')->findEmail($parameters['email']);

            if (!count($result) > 0) {
                return $this->notFound('le.core.contact.error.notfound');
            }
            $parameters['lead'] = $result[0]->getId();
            $lead               = $this->checkLeadAccess($parameters['lead'], $action);

            if ($lead instanceof Response) {
                return $lead;
            }

            $entity->setLead($lead);
            unset($parameters['email']);
        } elseif ($action === 'new') {
            return $this->returnError('lead ID is mandatory', Codes::HTTP_BAD_REQUEST);
        }
    }
}
