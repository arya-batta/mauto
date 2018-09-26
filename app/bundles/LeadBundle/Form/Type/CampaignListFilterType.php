<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Form\Type;

use DeviceDetector\Parser\Device\DeviceParserAbstract as DeviceParser;
use DeviceDetector\Parser\OperatingSystem;
use Mautic\CategoryBundle\Model\CategoryModel;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Mautic\CoreBundle\Helper\UserHelper;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\LeadBundle\Form\DataTransformer\FieldFilterTransformer;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Model\ListModel;
use Mautic\PageBundle\Model\PageModel;
use Mautic\UserBundle\Model\UserModel;
use Mautic\StageBundle\Model\StageModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class CampaignListFilterType.
 */
class CampaignListFilterType extends AbstractType
{
    private $translator;
    private $fieldChoices        = [];
    private $timezoneChoices     = [];
    private $countryChoices      = [];
    private $regionChoices       = [];
    private $listChoices         = [];
    private $emailChoices        = [];
    private $deviceTypesChoices  = [];
    private $deviceBrandsChoices = [];
    private $deviceOsChoices     = [];
    private $tagChoices          = [];
    private $stageChoices        = [];
    private $localeChoices       = [];
    private $categoriesChoices   = [];
    private $landingpageChoices  = [];
    private $userchoices         = [];

    /**
     * ListType constructor.
     *
     * @param TranslatorInterface $translator
     * @param ListModel           $listModel
     * @param EmailModel          $emailModel
     * @param CorePermissions     $security
     * @param LeadModel           $leadModel
     * @param StageModel          $stageModel
     * @param CategoryModel       $categoryModel
     * @param UserHelper          $userHelper
     * @param PageModel           $pageModel
     * @param UserModel           $userModel
     */
    public function __construct(TranslatorInterface $translator, ListModel $listModel, EmailModel $emailModel, CorePermissions $security, LeadModel $leadModel, StageModel $stageModel, CategoryModel $categoryModel, UserHelper $userHelper, PageModel $pageModel,UserModel $userModel)
    {
        $this->translator = $translator;

        $this->fieldChoices = $listModel->getChoiceFields();

        // Locales
        $this->timezoneChoices = FormFieldHelper::getCustomTimezones();
        $this->countryChoices  = FormFieldHelper::getCountryChoices();
        $this->regionChoices   = FormFieldHelper::getRegionChoices();
        $this->localeChoices   = FormFieldHelper::getLocaleChoices();

        // Segments
        $lists = $listModel->getUserLists();
        foreach ($lists as $list) {
            $this->listChoices[$list['id']] = $list['name'];
        }

        $viewOther   = $security->isGranted('email:emails:viewother');
        $currentUser = $userHelper->getUser();
        $emailRepo   = $emailModel->getRepository();

        $emailRepo->setCurrentUser($currentUser);

        $emails = $emailRepo->getEmailList('', 0, 0, $viewOther, true);

        foreach ($emails as $email) {
            $this->emailChoices[$email['language']][$email['id']] = $email['name'];
        }
        ksort($this->emailChoices);

        $pageRepo   = $pageModel->getRepository();

        $pageList = $pageRepo->getPageList('', 0, 0, $viewOther, true);

        foreach ($pageList as $page) {
            $this->landingpageChoices[$page['language']][$page['id']] = $page['title'];
        }
        ksort($this->landingpageChoices);

        $isadmin=$userModel->getCurrentUserEntity()->isAdmin();
        $filterarray= [
            'force' => [
                [
                    'column' => 'u.isPublished',
                    'expr'   => 'eq',
                    'value'  => true,
                ],
                [
                    'column' => 'u.id',
                    'expr'   => 'neq',
                    'value'  => '1',
                ],
            ],
        ];
        if($isadmin){
            $filterarray= [
                'force' => [
                    [
                        'column' => 'u.isPublished',
                        'expr'   => 'eq',
                        'value'  => true,
                    ],
                ],
            ];
        }
        $choices = $userModel->getRepository()->getEntities(
            [
                'filter' => $filterarray,
            ]
        );

        foreach ($choices as $choice) {
            $this->userchoices[$choice->getId()] = $choice->getName(true);
        }

        //sort by language
        ksort($this->userchoices);
        $tags = $leadModel->getTagList();
        foreach ($tags as $tag) {
            $this->tagChoices[$tag['value']] = $tag['label'];
        }

        $stages = $stageModel->getRepository()->getSimpleList();
        foreach ($stages as $stage) {
            $this->stageChoices[$stage['value']] = $stage['label'];
        }

        $categories = $categoryModel->getLookupResults('global');

        foreach ($categories as $category) {
            $this->categoriesChoices[$category['id']] = $category['title'];
        }
        $this->deviceTypesChoices  = array_combine((DeviceParser::getAvailableDeviceTypeNames()), (DeviceParser::getAvailableDeviceTypeNames()));
        $this->deviceBrandsChoices = DeviceParser::$deviceBrands;
        $this->deviceOsChoices     = array_combine((array_keys(OperatingSystem::getAvailableOperatingSystemFamilies())), array_keys(OperatingSystem::getAvailableOperatingSystemFamilies()));
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        // $builder->addEventSubscriber(new FormExitSubscriber('lead.list', $options));

//        $builder->add(
//            'name',
//            'text',
//            [
//                'label'      => 'mautic.core.name',
//                'label_attr' => ['class' => 'control-label'],
//                'attr'       => ['class' => 'form-control'],
//            ]
//        );

//        $builder->add(
//            'alias',
//            'text',
//            [
//                'label'      => 'mautic.core.alias',
//                'label_attr' => ['class' => 'control-label'],
//                'attr'       => [
//                    'class'   => 'form-control',
//                    'length'  => 25,
//                    'tooltip' => 'mautic.lead.list.help.alias',
//                ],
//                'required' => false,
//            ]
//        );

//        $builder->add(
//            'description',
//            'textarea',
//            [
//                'label'      => 'mautic.core.description',
//                'label_attr' => ['class' => 'control-label'],
//                'attr'       => ['class' => 'form-control editor'],
//                'required'   => false,
//            ]
//        );

//        $builder->add(
//            'isGlobal',
//            'yesno_button_group',
//            [
//                'label' => 'mautic.lead.list.form.isglobal',
//            ]
//        );
//
//        $builder->add('isPublished', 'yesno_button_group');

        $filterModalTransformer = new FieldFilterTransformer($this->translator);
        $builder->add(
            $builder->create(
                'filters',
                'collection',
                [
                    'type'    => 'leadlist_filter',
                    'options' => [
                        'label'          => false,
                        'timezones'      => $this->timezoneChoices,
                        'countries'      => $this->countryChoices,
                        'regions'        => $this->regionChoices,
                        'fields'         => $this->fieldChoices,
                        'lists'          => $this->listChoices,
                        'emails'         => $this->emailChoices,
                        'deviceTypes'    => $this->deviceTypesChoices,
                        'deviceBrands'   => $this->deviceBrandsChoices,
                        'deviceOs'       => $this->deviceOsChoices,
                        'tags'           => $this->tagChoices,
                        'stage'          => $this->stageChoices,
                        'locales'        => $this->localeChoices,
                        'globalcategory' => $this->categoriesChoices,
                        'users'          => $this->userchoices,
                        'required'         => true,
                        'landingpage_list' => $this->landingpageChoices,
                    ],
                    'required'    => true,
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'mautic.core.value.required',
                            ]
                        ),
                    ],
                    'error_bubbling' => true,
                    'mapped'         => true,
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'label'          => false,
                ]
            )->addModelTransformer($filterModalTransformer)
        );

//        $builder->add('buttons', 'form_buttons');
//
//        if (!empty($options['action'])) {
//            $builder->setAction($options['action']);
//        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['fields']           = $this->fieldChoices;
        $view->vars['countries']        = $this->countryChoices;
        $view->vars['regions']          = $this->regionChoices;
        $view->vars['timezones']        = $this->timezoneChoices;
        $view->vars['lists']            = $this->listChoices;
        $view->vars['emails']           = $this->emailChoices;
        $view->vars['deviceTypes']      = $this->deviceTypesChoices;
        $view->vars['deviceBrands']     = $this->deviceBrandsChoices;
        $view->vars['deviceOs']         = $this->deviceOsChoices;
        $view->vars['tags']             = $this->tagChoices;
        $view->vars['stage']            = $this->stageChoices;
        $view->vars['locales']          = $this->localeChoices;
        $view->vars['globalcategory']   = $this->categoriesChoices;
        $view->vars['landingpage_list'] = $this->landingpageChoices;
        $view->vars['users']          = $this->userchoices;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'campaignlistfilter';
    }
}