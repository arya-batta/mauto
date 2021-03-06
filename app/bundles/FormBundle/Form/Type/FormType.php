<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\FormBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class FormType.
 */
class FormType extends AbstractType
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    /**
     * @var \Mautic\CoreBundle\Security\Permissions\CorePermissions
     */
    private $security;

    private $factory;
    private $leadfieldChoices       = [];

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->translator           = $factory->getTranslator();
        $this->security             = $factory->getSecurity();
        $this->factory              = $factory;
        $leadfields                 =$this->factory->getModel('lead.field')->getFieldListWithProperties('lead');
        $this->leadfieldChoices[''] = '';
        foreach ($leadfields as $leadfield) {
            $this->leadfieldChoices[$leadfield['alias']] = $leadfield['label'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('form.form', $options));
        $style = 'display:none;';
        if ($this->factory->getUser()->isAdmin()) {
            $style = 'display:block;';
        }
        //details
        $builder->add('name', 'text', [
            'label'      => 'mautic.core.name',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control le-input'],
        ]);

        $builder->add('description', 'textarea', [
            'label'      => 'mautic.core.description',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control editor le-input'],
            'required'   => false,
        ]);

        //add category
        $builder->add('category', 'category', [
            'bundle' => 'form',
        ]);

        $builder->add('isPublished', 'yesno_button_group',[
            'no_label'   => 'mautic.core.form.unpublished',
            'yes_label'  => 'mautic.core.form.published',
            ]);

        $builder->add('template', 'theme_list', [
            'feature'     => 'form',
            'empty_value' => ' ',
            'attr'        => [
                'class'   => 'form-control le-input',
                'tooltip' => 'mautic.form.form.template.help',
            ],
        ]);

        if (!empty($options['data']) && $options['data']->getId()) {
            $readonly = !$this->security->hasEntityAccess(
                'form:forms:publishown',
                'form:forms:publishother',
                $options['data']->getCreatedBy()
            );

            $data = $options['data']->isPublished(false);
        } elseif (!$this->security->isGranted('form:forms:publishown')) {
            $readonly = true;
            $data     = false;
        } else {
            $readonly = false;
            $data     = true;
        }

        $builder->add('isPublished', 'yesno_button_group', [
            'read_only' => $readonly,
            'data'      => $data,
            'no_label'   => 'mautic.core.form.unpublished',
            'yes_label'  => 'mautic.core.form.published',
            ]);

        $builder->add('inKioskMode', 'yesno_button_group', [
            'label' => 'mautic.form.form.kioskmode',
            'attr'  => [
                'tooltip' => 'mautic.form.form.kioskmode.tooltip',
            ],
        ]);

        $builder->add('isGDPRPublished', 'yesno_button_group', [
            'label' => 'le.form.form.isGDPRpublished',
            'attr'  => [
                'class'    => 'gdpr-checkbox',
                'onchange' => "Le.toggleGDPRButtonClass(mQuery(this).attr('id'))",
            ],
        ]);

        // Render style for new form by default
        if ($options['data']->getId() === null) {
            $options['data']->setRenderStyle(true);
        }

        $builder->add('renderStyle', 'yesno_button_group', [
            'label'      => 'mautic.form.form.renderstyle',
            'data'       => ($options['data']->getRenderStyle() === null) ? true : $options['data']->getRenderStyle(),
            'empty_data' => true,
            'attr'       => [
                'tooltip' => 'mautic.form.form.renderstyle.tooltip',
            ],
        ]);

        $builder->add('publishUp', 'datetime', [
            'widget'     => 'single_text',
            'label'      => 'mautic.core.form.publishup',
            'label_attr' => ['class' => 'control-label', 'style' => $style],
            'attr'       => [
                'class'       => 'form-control le-input',
                'data-toggle' => 'datetime',
                'style'       => $style,
            ],
            'format'   => 'yyyy-MM-dd HH:mm',
            'required' => false,
        ]);

        $builder->add('publishDown', 'datetime', [
            'widget'     => 'single_text',
            'label'      => 'mautic.core.form.publishdown',
            'label_attr' => [
                'class' => 'control-label',
                'style' => $style,
            ],
            'attr'       => [
                'class'       => 'form-control le-input',
                'data-toggle' => 'datetime',
                'style'       => $style,
            ],
            'format'   => 'yyyy-MM-dd HH:mm',
            'required' => false,
        ]);

        $builder->add('postAction', 'choice', [
            'choices' => [
                'return'   => 'mautic.form.form.postaction.return',
                'redirect' => 'mautic.form.form.postaction.redirect',
                'message'  => 'mautic.form.form.postaction.message',
            ],
            'label'      => 'mautic.form.form.postaction',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'    => 'form-control',
                'onchange' => 'Le.onPostSubmitActionChange(this.value);',
            ],
            'required'    => false,
            'empty_value' => false,
        ]);

        $postAction = (isset($options['data'])) ? $options['data']->getPostAction() : '';
        $required   = (in_array($postAction, ['redirect', 'message'])) ? true : false;
        $fieldtype  = 'text';
        if ($postAction == 'redirect') {
            $fieldtype = 'url';
        }
        $builder->add('postActionProperty', $fieldtype, [
            'label'      => 'mautic.form.form.postactionproperty',
            'label_attr' => ['class' => 'control-label check_required'],
            'attr'       => [
                'class'        => 'form-control le-input',
                'data-hide-on' => '{"leform_postAction":["return"]}',
                'onkeyup'      => 'Le.onKeyupMaxLength(this.value);',
            ],
            'required'   => $required,
        ]);

        $builder->add('sessionId', 'hidden', [
            'mapped' => false,
        ]);

        $builder->add('buttons', 'form_buttons', [
            'apply_text' => false,
            'save_icon'  => false,
        ]);
        $builder->add('formType', 'hidden', ['empty_data' => 'standalone']);

        $builder->add('formurl', 'text', [
            'label'      => 'le.smart.form.url',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control le-input',
                'placeholder'        => 'le.smart.form.scan.url.placeholder',
                'tooltip'            => 'le.smart.form.scan.url.tooltip',
                'autocomplete'       => 'off', ],
            'required'   => true,
        ]);
        $builder->add(
            $builder->create(
                'smartfields',
                'collection',
                [
                    'type'    => 'smart_form_fields',
                    'options' => [
                        'label'                       => false,
                        'leadfieldchoices'            => $this->leadfieldChoices,
                    ],
                    'error_bubbling' => false,
                    'mapped'         => true,
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'label'          => false,
                ]
            )
        );

        $builder->add('smartformname', 'hidden');
        $builder->add('smartformid', 'hidden');

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => 'Mautic\FormBundle\Entity\Form',
            'validation_groups' => [
                'Mautic\FormBundle\Entity\Form',
                'determineValidationGroups',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'leform';
    }
}
