<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\Security\Permissions;

use Mautic\CoreBundle\Security\Permissions\AbstractPermissions;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PluginPermissions.
 */
class PluginPermissions extends AbstractPermissions
{
    /**
     * {@inheritdoc}
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->addManagePermission('plugins');
        $this->addExtendedPermissions('slack', false);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'plugin';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface &$builder, array $options, array $data)
    {
        $this->addManageFormFields('plugin', 'plugins', $builder, $data);
        $builder->add('plugin:slack', 'permissionlist', [
            'choices' => [
                'viewown'      => 'mautic.core.permissions.viewown',
                'viewother'    => 'mautic.core.permissions.viewother',
                'editown'      => 'mautic.core.permissions.editown',
                'editother'    => 'mautic.core.permissions.editother',
                'create'       => 'mautic.core.permissions.create',
                'deleteown'    => 'mautic.core.permissions.deleteown',
                'deleteother'  => 'mautic.core.permissions.deleteother',
                'full'         => 'mautic.core.permissions.full',
            ],
            'label'  => 'le.slack.permissions.slack',
            'data'   => (!empty($data['slack']) ? $data['slack'] : []),
            'bundle' => 'plugin',
            'level'  => 'slack',
        ]);
    }
}
