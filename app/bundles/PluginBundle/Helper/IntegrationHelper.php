<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\Helper;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Helper\BundleHelper;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\CoreBundle\Helper\PathsHelper;
use Mautic\CoreBundle\Helper\TemplatingHelper;
use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Entity\IntegrationFieldMapping;
use Mautic\PluginBundle\Entity\IntegrationPayLoadHistory;
use Mautic\PluginBundle\Entity\Plugin;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use Mautic\PluginBundle\Model\PluginModel;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class IntegrationHelper.
 */
class IntegrationHelper
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PathsHelper
     */
    protected $pathsHelper;

    /**
     * @var BundleHelper
     */
    protected $bundleHelper;

    /**
     * @var CoreParametersHelper
     */
    protected $coreParametersHelper;

    /**
     * @var TemplatingHelper
     */
    protected $templatingHelper;

    /**
     * @var PluginModel
     */
    protected $pluginModel;

    /**
     * @deprecated 2.8.2 To be removed in 3.0
     *
     * @var MauticFactory
     */
    protected $factory;

    private $integrations = [];

    private $available = [];

    private $byFeatureList = [];

    private $byPlugin = [];

    /**
     * IntegrationHelper constructor.
     *
     * @param Kernel               $kernel
     * @param EntityManager        $em
     * @param PathsHelper          $pathsHelper
     * @param BundleHelper         $bundleHelper
     * @param CoreParametersHelper $coreParametersHelper
     * @param TemplatingHelper     $templatingHelper
     * @param PluginModel          $pluginModel
     */
    public function __construct(Kernel $kernel, EntityManager $em, PathsHelper $pathsHelper, BundleHelper $bundleHelper, CoreParametersHelper $coreParametersHelper, TemplatingHelper $templatingHelper, PluginModel $pluginModel)
    {
        $this->container            = $kernel->getContainer();
        $this->em                   = $em;
        $this->pathsHelper          = $pathsHelper;
        $this->bundleHelper         = $bundleHelper;
        $this->pluginModel          = $pluginModel;
        $this->coreParametersHelper = $coreParametersHelper;
        $this->templatingHelper     = $templatingHelper;
        $this->factory              = $this->container->get('mautic.factory');
    }

    /**
     * Get a list of integration helper classes.
     *
     * @param array|string $specificIntegrations
     * @param array        $withFeatures
     * @param bool         $alphabetical
     * @param null|int     $pluginFilter
     * @param bool|false   $publishedOnly
     *
     * @return mixed
     */
    public function getIntegrationObjects($specificIntegrations = null, $withFeatures = null, $alphabetical = false, $pluginFilter = null, $publishedOnly = false)
    {
        // Build the service classes
        if (empty($this->available)) {
            $this->available = [];

            // Get currently installed integrations
            $integrationSettings = $this->getIntegrationSettings();

            // And we'll be scanning the addon bundles for additional classes, so have that data on standby
            $plugins = $this->bundleHelper->getPluginBundles();

            // Get a list of already installed integrations
            $integrationRepo = $this->em->getRepository('MauticPluginBundle:Integration');
            //get a list of plugins for filter
            $installedPlugins = $this->pluginModel->getEntities(
                [
                    'hydration_mode' => 'hydrate_array',
                    'index'          => 'bundle',
                ]
            );

            $newIntegrations = [];

            // Scan the plugins for integration classes
            foreach ($plugins as $plugin) {
                // Do not list the integration if the bundle has not been "installed"
                if (!isset($plugin['bundle']) || !isset($installedPlugins[$plugin['bundle']])) {
                    continue;
                }

                if (is_dir($plugin['directory'].'/Integration')) {
                    $finder = new Finder();
                    $finder->files()->name('*Integration.php')->in($plugin['directory'].'/Integration')->ignoreDotFiles(true);

                    $id                  = $installedPlugins[$plugin['bundle']]['id'];
                    $this->byPlugin[$id] = [];
                    $pluginReference     = $this->em->getReference('MauticPluginBundle:Plugin', $id);
                    $pluginNamespace     = str_replace('MauticPlugin', '', $plugin['bundle']);

                    foreach ($finder as $file) {
                        $integrationName = substr($file->getBaseName(), 0, -15);

                        if (!isset($integrationSettings[$integrationName])) {
                            $newIntegration = new Integration();
                            $newIntegration->setName($integrationName)
                                ->setPlugin($pluginReference);
                            $integrationSettings[$integrationName] = $newIntegration;
                            $integrationContainerKey               = strtolower("mautic.integration.{$integrationName}");

                            // Initiate the class in order to get the features supported
                            if ($this->container->has($integrationContainerKey)) {
                                $this->integrations[$integrationName] = $this->container->get($integrationContainerKey);

                                $features = $this->integrations[$integrationName]->getSupportedFeatures();
                                $newIntegration->setSupportedFeatures($features);

                                // Go ahead and stash it since it's built already
                                $this->integrations[$integrationName]->setIntegrationSettings($newIntegration);

                                $newIntegrations[] = $newIntegration;

                                unset($newIntegration);
                            } else {
                                /**
                                 * @deprecated: 2.8.2 To be removed in 3.0
                                 *            This keeps BC for 3rd party plugins
                                 */
                                $class    = '\\MauticPlugin\\'.$pluginNamespace.'\\Integration\\'.$integrationName.'Integration';
                                $refClass = new \ReflectionClass($class);

                                if ($refClass->isInstantiable()) {
                                    $this->integrations[$integrationName] = new $class($this->factory);
                                    $features                             = $this->integrations[$integrationName]->getSupportedFeatures();

                                    $newIntegration->setSupportedFeatures($features);

                                    // Go ahead and stash it since it's built already
                                    $this->integrations[$integrationName]->setIntegrationSettings($newIntegration);

                                    $newIntegrations[] = $newIntegration;

                                    unset($newIntegration);
                                } else {
                                    // Something is bad so ignore
                                    continue;
                                }
                            }
                        }

                        /** @var \Mautic\PluginBundle\Entity\Integration $settings */
                        $settings                          = $integrationSettings[$integrationName];
                        $this->available[$integrationName] = [
                            'isPlugin'    => true,
                            'integration' => $integrationName,
                            'settings'    => $settings,
                            'namespace'   => $pluginNamespace,
                        ];

                        // Sort by feature and plugin for later
                        $features = $settings->getSupportedFeatures();
                        foreach ($features as $feature) {
                            if (!isset($this->byFeatureList[$feature])) {
                                $this->byFeatureList[$feature] = [];
                            }
                            $this->byFeatureList[$feature][] = $integrationName;
                        }
                        $this->byPlugin[$id][] = $integrationName;
                    }
                }
            }

            $coreIntegrationSettings = $this->getCoreIntegrationSettings();

            // Scan core bundles for integration classes
            foreach ($this->bundleHelper->getMauticBundles() as $coreBundle) {
                if (
                    // Skip plugin bundles
                    strpos($coreBundle['directory'], 'app/bundles') !== false
                    // Skip core bundles without an Integration directory
                    && is_dir($coreBundle['directory'].'/Integration')
                ) {
                    $finder = new Finder();
                    $finder->files()->name('*Integration.php')->in($coreBundle['directory'].'/Integration')->ignoreDotFiles(true);

                    $coreBundleNamespace = str_replace('Mautic', '', $coreBundle['bundle']);

                    foreach ($finder as $file) {
                        $integrationName = substr($file->getBaseName(), 0, -15);

                        if (!isset($coreIntegrationSettings[$integrationName])) {
                            $newIntegration = new Integration();
                            $newIntegration->setName($integrationName);
                            $integrationSettings[$integrationName] = $newIntegration;

                            $integrationContainerKey = strtolower("mautic.integration.{$integrationName}");

                            // Initiate the class in order to get the features supported
                            if ($this->container->has($integrationContainerKey)) {
                                $this->integrations[$integrationName] = $this->container->get($integrationContainerKey);
                                $features                             = $this->integrations[$integrationName]->getSupportedFeatures();
                                $newIntegration->setSupportedFeatures($features);

                                // Go ahead and stash it since it's built already
                                $this->integrations[$integrationName]->setIntegrationSettings($newIntegration);

                                $newIntegrations[] = $newIntegration;
                            } else {
                                continue;
                            }
                        }

                        /** @var \Mautic\PluginBundle\Entity\Integration $settings */
                        $settings                          = isset($coreIntegrationSettings[$integrationName]) ? $coreIntegrationSettings[$integrationName] : $newIntegration;
                        $this->available[$integrationName] = [
                            'isPlugin'    => false,
                            'integration' => $integrationName,
                            'settings'    => $settings,
                            'namespace'   => $coreBundleNamespace,
                        ];
                    }
                }
            }

            // Save newly found integrations
            if (!empty($newIntegrations)) {
                $integrationRepo->saveEntities($newIntegrations);
                unset($newIntegrations);
            }
        }

        // Ensure appropriate formats
        if ($specificIntegrations !== null && !is_array($specificIntegrations)) {
            $specificIntegrations = [$specificIntegrations];
        }

        if ($withFeatures !== null && !is_array($withFeatures)) {
            $withFeatures = [$withFeatures];
        }

        // Build the integrations wanted
        if (!empty($pluginFilter)) {
            // Filter by plugin
            $filteredIntegrations = $this->byPlugin[$pluginFilter];
        } elseif (!empty($specificIntegrations)) {
            // Filter by specific integrations
            $filteredIntegrations = $specificIntegrations;
        } else {
            // All services by default
            $filteredIntegrations = array_keys($this->available);
        }

        // Filter by features
        if (!empty($withFeatures)) {
            $integrationsWithFeatures = [];
            foreach ($withFeatures as $feature) {
                if (isset($this->byFeatureList[$feature])) {
                    $integrationsWithFeatures = $integrationsWithFeatures + $this->byFeatureList[$feature];
                }
            }

            $filteredIntegrations = array_intersect($filteredIntegrations, $integrationsWithFeatures);
        }

        $returnServices = [];

        // Build the classes if not already
        foreach ($filteredIntegrations as $integrationName) {
            if (!isset($this->available[$integrationName]) || ($publishedOnly && !$this->available[$integrationName]['settings']->isPublished())) {
                continue;
            }

            if (!isset($this->integrations[$integrationName])) {
                $integration             = $this->available[$integrationName];
                $integrationContainerKey = strtolower("mautic.integration.{$integrationName}");

                if ($this->container->has($integrationContainerKey)) {
                    $this->integrations[$integrationName] = $this->container->get($integrationContainerKey);
                    $this->integrations[$integrationName]->setIntegrationSettings($integration['settings']);
                } else {
                    /**
                     * @deprecated: 2.8.2 To be removed in 3.0
                     *            This keeps BC for 3rd party plugins
                     */
                    $rootNamespace = $integration['isPlugin'] ? '\\MauticPlugin\\' : '\\Mautic\\';
                    $class         = $rootNamespace.$integration['namespace'].'\\Integration\\'.$integrationName.'Integration';
                    $refClass      = new \ReflectionClass($class);

                    if ($refClass->isInstantiable()) {
                        $this->integrations[$integrationName] = new $class($this->factory);

                        $this->integrations[$integrationName]->setIntegrationSettings($integration['settings']);
                    } else {
                        // Something is bad so ignore
                        continue;
                    }
                }
            }

            $returnServices[$integrationName] = $this->integrations[$integrationName];
        }

        foreach ($returnServices as $key => $value) {
            if (!isset($value)) {
                unset($returnServices[$key]);
            }
        }

        foreach ($returnServices as $key => $value) {
            if (!isset($value)) {
                unset($returnServices[$key]);
            }
        }

        if (empty($alphabetical)) {
            // Sort by priority
            uasort($returnServices, function ($a, $b) {
                $aP = (int) $a->getPriority();
                $bP = (int) $b->getPriority();

                if ($aP === $bP) {
                    return 0;
                }

                return ($aP < $bP) ? -1 : 1;
            });
        } else {
            // Sort by display name
            uasort($returnServices, function ($a, $b) {
                $aName = $a->getDisplayName();
                $bName = $b->getDisplayName();

                return strcasecmp($aName, $bName);
            });
        }

        return $returnServices;
    }

    /**
     * Get a single integration object.
     *
     * @param $name
     *
     * @return AbstractIntegration|bool
     */
    public function getIntegrationObject($name)
    {
        $integrationObjects = $this->getIntegrationObjects($name);

        return ((isset($integrationObjects[$name]))) ? $integrationObjects[$name] : false;
    }

    /**
     * Gets a count of integrations.
     *
     * @param $plugin
     *
     * @return int
     */
    public function getIntegrationCount($plugin)
    {
        if (!is_array($plugin)) {
            $plugins = $this->coreParametersHelper->getParameter('plugin.bundles');
            if (array_key_exists($plugin, $plugins)) {
                $plugin = $plugins[$plugin];
            } else {
                // It doesn't exist so return 0

                return 0;
            }
        }

        if (is_dir($plugin['directory'].'/Integration')) {
            $finder = new Finder();
            $finder->files()->name('*Integration.php')->in($plugin['directory'].'/Integration')->ignoreDotFiles(true);

            return iterator_count($finder);
        }

        return 0;
    }

    /**
     * Returns popular social media services and regex URLs for parsing purposes.
     *
     * @param bool $find If true, array of regexes to find a handle will be returned;
     *                   If false, array of URLs with a placeholder of %handle% will be returned
     *
     * @return array
     *
     * @todo Extend this method to allow plugins to add URLs to these arrays
     */
    public function getSocialProfileUrlRegex($find = true)
    {
        if ($find) {
            //regex to find a match
            return [
                'twitter'  => "/twitter.com\/(.*?)($|\/)/",
                'facebook' => [
                    "/facebook.com\/(.*?)($|\/)/",
                    "/fb.me\/(.*?)($|\/)/",
                ],
                'linkedin'  => "/linkedin.com\/in\/(.*?)($|\/)/",
                'instagram' => "/instagram.com\/(.*?)($|\/)/",
                'pinterest' => "/pinterest.com\/(.*?)($|\/)/",
                'klout'     => "/klout.com\/(.*?)($|\/)/",
                'youtube'   => [
                    "/youtube.com\/user\/(.*?)($|\/)/",
                    "/youtu.be\/user\/(.*?)($|\/)/",
                ],
                'flickr' => "/flickr.com\/photos\/(.*?)($|\/)/",
                'skype'  => "/skype:(.*?)($|\?)/",
                'google' => "/plus.google.com\/(.*?)($|\/)/",
            ];
        } else {
            //populate placeholder
            return [
                'twitter'    => 'https://twitter.com/%handle%',
                'facebook'   => 'https://facebook.com/%handle%',
                'linkedin'   => 'https://linkedin.com/in/%handle%',
                'instagram'  => 'https://instagram.com/%handle%',
                'pinterest'  => 'https://pinterest.com/%handle%',
                'klout'      => 'https://klout.com/%handle%',
                'youtube'    => 'https://youtube.com/user/%handle%',
                'flickr'     => 'https://flickr.com/photos/%handle%',
                'skype'      => 'skype:%handle%?call',
                'googleplus' => 'https://plus.google.com/%handle%',
            ];
        }
    }

    /**
     * Get array of integration entities.
     *
     * @return mixed
     */
    public function getIntegrationSettings()
    {
        return $this->em->getRepository('MauticPluginBundle:Integration')->getIntegrations();
    }

    public function getCoreIntegrationSettings()
    {
        return $this->em->getRepository('MauticPluginBundle:Integration')->getCoreIntegrations();
    }

    public function getIntegrationRepository()
    {
        return $this->em->getRepository('MauticPluginBundle:Integration');
    }

    /**
     * Get the user's social profile data from cache or integrations if indicated.
     *
     * @param \Mautic\LeadBundle\Entity\Lead $lead
     * @param array                          $fields
     * @param bool                           $refresh
     * @param string                         $specificIntegration
     * @param bool                           $persistLead
     * @param bool                           $returnSettings
     *
     * @return array
     */
    public function getUserProfiles($lead, $fields = [], $refresh = false, $specificIntegration = null, $persistLead = true, $returnSettings = false)
    {
        $socialCache     = $lead->getSocialCache();
        $featureSettings = [];
        if ($refresh) {
            //regenerate from integrations
            $now = new DateTimeHelper();

            //check to see if there are social profiles activated
            $socialIntegrations = $this->getIntegrationObjects($specificIntegration, ['public_profile', 'public_activity']);

            /* @var \MauticPlugin\leSocialBundle\Integration\SocialIntegration $sn */
            foreach ($socialIntegrations as $integration => $sn) {
                $settings        = $sn->getIntegrationSettings();
                $features        = $settings->getSupportedFeatures();
                $identifierField = $this->getUserIdentifierField($sn, $fields);

                if ($returnSettings) {
                    $featureSettings[$integration] = $settings->getFeatureSettings();
                }

                if ($identifierField && $settings->isPublished()) {
                    $profile = (!isset($socialCache[$integration])) ? [] : $socialCache[$integration];

                    //clear the cache
                    unset($profile['profile'], $profile['activity']);

                    if (in_array('public_profile', $features) && $sn->isAuthorized()) {
                        $sn->getUserData($identifierField, $profile);
                    }

                    if (in_array('public_activity', $features) && $sn->isAuthorized()) {
                        $sn->getPublicActivity($identifierField, $profile);
                    }

                    if (!empty($profile['profile']) || !empty($profile['activity'])) {
                        if (!isset($socialCache[$integration])) {
                            $socialCache[$integration] = [];
                        }

                        $socialCache[$integration]['profile']     = (!empty($profile['profile'])) ? $profile['profile'] : [];
                        $socialCache[$integration]['activity']    = (!empty($profile['activity'])) ? $profile['activity'] : [];
                        $socialCache[$integration]['lastRefresh'] = $now->toUtcString();
                    }
                } elseif (isset($socialCache[$integration])) {
                    //integration is now not applicable
                    unset($socialCache[$integration]);
                }
            }

            if ($persistLead && !empty($socialCache)) {
                $lead->setSocialCache($socialCache);
                $this->em->getRepository('MauticLeadBundle:Lead')->saveEntity($lead);
            }
        } elseif ($returnSettings) {
            $socialIntegrations = $this->getIntegrationObjects($specificIntegration, ['public_profile', 'public_activity']);
            foreach ($socialIntegrations as $integration => $sn) {
                $settings                      = $sn->getIntegrationSettings();
                $featureSettings[$integration] = $settings->getFeatureSettings();
            }
        }

        if ($specificIntegration) {
            return ($returnSettings) ? [[$specificIntegration => $socialCache[$specificIntegration]], $featureSettings]
                : [$specificIntegration => $socialCache[$specificIntegration]];
        }

        return ($returnSettings) ? [$socialCache, $featureSettings] : $socialCache;
    }

    /**
     * @param      $lead
     * @param bool $integration
     *
     * @return array
     */
    public function clearIntegrationCache($lead, $integration = false)
    {
        $socialCache = $lead->getSocialCache();
        if (!empty($integration)) {
            unset($socialCache[$integration]);
        } else {
            $socialCache = [];
        }
        $lead->setSocialCache($socialCache);
        $this->em->getRepository('MauticLeadBundle:Lead')->saveEntity($lead);

        return $socialCache;
    }

    /**
     * Gets an array of the HTML for share buttons.
     */
    public function getShareButtons()
    {
        static $shareBtns = [];

        if (empty($shareBtns)) {
            $socialIntegrations = $this->getIntegrationObjects(null, ['share_button'], true);
            $templating         = $this->templatingHelper->getTemplating();

            /**
             * @var string
             * @var \Mautic\PluginBundle\Integration\AbstractIntegration $details
             */
            foreach ($socialIntegrations as $integration => $details) {
                /** @var \Mautic\PluginBundle\Entity\Integration $settings */
                $settings = $details->getIntegrationSettings();

                $featureSettings = $settings->getFeatureSettings();
                $apiKeys         = $details->decryptApiKeys($settings->getApiKeys());
                $plugin          = $settings->getPlugin();
                $shareSettings   = isset($featureSettings['shareButton']) ? $featureSettings['shareButton'] : [];

                //add the api keys for use within the share buttons
                $shareSettings['keys']   = $apiKeys;
                $shareBtns[$integration] = $templating->render($plugin->getBundle().":Integration/$integration:share.html.php", [
                    'settings' => $shareSettings,
                ]);
            }
        }

        return $shareBtns;
    }

    /**
     * Loops through field values available and finds the field the integration needs to obtain the user.
     *
     * @param $integrationObject
     * @param $fields
     *
     * @return bool
     */
    public function getUserIdentifierField($integrationObject, $fields)
    {
        $identifierField = $integrationObject->getIdentifierFields();
        $identifier      = (is_array($identifierField)) ? [] : false;
        $matchFound      = false;

        $findMatch = function ($f, $fields) use (&$identifierField, &$identifier, &$matchFound) {
            if (is_array($identifier)) {
                //there are multiple fields the integration can identify by
                foreach ($identifierField as $idf) {
                    $value = (is_array($fields[$f]) && isset($fields[$f]['value'])) ? $fields[$f]['value'] : $fields[$f];

                    if (!in_array($value, $identifier) && strpos($f, $idf) !== false) {
                        $identifier[$f] = $value;
                        if (count($identifier) === count($identifierField)) {
                            //found enough matches so break
                            $matchFound = true;
                            break;
                        }
                    }
                }
            } elseif ($identifierField === $f || strpos($f, $identifierField) !== false) {
                $matchFound = true;
                $identifier = (is_array($fields[$f])) ? $fields[$f]['value'] : $fields[$f];
            }
        };

        $groups = ['core', 'social', 'professional', 'personal'];
        $keys   = array_keys($fields);
        if (count(array_intersect($groups, $keys)) !== 0 && count($keys) <= 4) {
            //fields are group
            foreach ($fields as $group => $groupFields) {
                $availableFields = array_keys($groupFields);
                foreach ($availableFields as $f) {
                    $findMatch($f, $groupFields);

                    if ($matchFound) {
                        break;
                    }
                }
            }
        } else {
            $availableFields = array_keys($fields);
            foreach ($availableFields as $f) {
                $findMatch($f, $fields);

                if ($matchFound) {
                    break;
                }
            }
        }

        return $identifier;
    }

    /**
     * Get the path to the integration's icon relative to the site root.
     *
     * @param $integration
     *
     * @return string
     */
    public function getIconPath($integration)
    {
        $systemPath  = $this->pathsHelper->getSystemPath('root');
        $bundlePath  = $this->pathsHelper->getSystemPath('bundles');
        $pluginPath  = $this->pathsHelper->getSystemPath('plugins');
        $genericIcon = $bundlePath.'/PluginBundle/Assets/img/generic.png';

        if (is_array($integration)) {
            // A bundle so check for an icon
            $icon = $pluginPath.'/'.$integration['bundle'].'/Assets/img/icon.png';
        } elseif ($integration instanceof Plugin) {
            // A bundle so check for an icon
            $icon = $pluginPath.'/'.$integration->getBundle().'/Assets/img/icon.png';
        } elseif ($integration instanceof AbstractIntegration) {
            return $integration->getIcon();
        }

        if (file_exists($systemPath.'/'.$icon)) {
            return $icon;
        }

        return $genericIcon;
    }

    /**
     * @param null $specificintegration
     *
     * @return array|mixed
     */
    public function getIntegrationDetails($specificintegration =null)
    {
        /** @var \Mautic\CoreBundle\Templating\Helper\AssetsHelper $assetsHelper */
        $assetsHelper = $this->factory->getHelper('template.assets');
        $instapage    = $this->factory->getTranslator()->trans('le.integration.name.instapage');
        $calendly     = $this->factory->getTranslator()->trans('le.integration.name.calendly');
        $unbounce     = $this->factory->getTranslator()->trans('le.integration.name.unbounce');
        $integrations =[
            'facebook_lead_ads'=> [
                'name'     => 'Facebook Lead Ads',
                'image_url'=> $assetsHelper->getUrl('media/images/integrations/facebook_lead_ads.png'),
                'route'    => $this->container->get('router')->generate('le_integrations_config', ['name'=>'facebook_lead_ads']),
            ],
            'facebook_custom_audiences' => [
                'name'     => 'Facebook Custom Audiences',
                'image_url'=> $assetsHelper->getUrl('media/images/integrations/facebook_custom_audiences.png'),
                'route'    => $this->container->get('router')->generate('le_integrations_config', ['name'=>'facebook_custom_audiences']),
            ],
            $instapage => [
                'name'     => $this->factory->getTranslator()->trans('le.integration.label.instapage'),
                'image_url'=> $assetsHelper->getUrl('media/images/integrations/instapage.png'),
                'route'    => $this->container->get('router')->generate('le_integrations_config', ['name'=>$instapage]),
            ],
            $unbounce => [
                'name'     => $this->factory->getTranslator()->trans('le.integration.label.unbounce'),
                'image_url'=> $assetsHelper->getUrl('media/images/integrations/unbounce.png'),
                'route'    => $this->container->get('router')->generate('le_integrations_config', ['name'=>$unbounce]),
            ],
            $calendly => [
                'name'     => $this->factory->getTranslator()->trans('le.integration.label.calendly'),
                'image_url'=> $assetsHelper->getUrl('media/images/integrations/calendly.png'),
                'route'    => $this->container->get('router')->generate('le_integrations_config', ['name'=>$calendly]),
            ],
        ];
        if ($specificintegration != null) {
            return  $integrations[$specificintegration];
        }

        return $integrations;
    }

    public function getIntegrationSettingsbyName($name)
    {
        $integrationsettings=[];
        /** @var \Mautic\PluginBundle\Helper\IntegrationHelper $integrationHelper */
        $integrationHelper = $this->factory->getHelper('integration');
        $integrationrepo   =$integrationHelper->getIntegrationRepository();
        $integrations      =$integrationrepo->findBy(
            [
                'name' => $name,
            ]
        );
        if (sizeof($integrations) > 0) {
            $integration        =$integrations[0];
            $integrationsettings=$integration->getApiKeys();
        }

        return $integrationsettings;
    }

    public function getFieldMappingObject($group, $integration)
    {
        try {
            $fieldmapping      =false;
            $integrationrepo   =$this->getIntegrationRepository();
            $mappings          =$integrationrepo->getAllFieldMapping($group, $integration->getId());
            foreach ($mappings as $id => $mapping) {
                $fieldmapping=$mapping;
                break;
            }
            if (!$fieldmapping) {
                $fieldmapping=new IntegrationFieldMapping();
            }
        } catch (\Exception $ex) {
        }

        return $fieldmapping;
    }

    public function getIntegrationInfobyName($name)
    {
        $integrationrepo   =$this->getIntegrationRepository();
        $integrations      =$integrationrepo->findBy(
            [
                'name' => $name,
            ]
        );
        if (sizeof($integrations) > 0) {
            return $integrations[0];
        } else {
            $integrationentity=new Integration();
            $integrationentity->setName($name);
            $integrationrepo->saveEntity($integrationentity);

            return $integrationentity;
        }
    }

    public function putPayLoadHistory($jsonData, $integrationName)
    {
        $payload      ='';
        $enocodeNeeded=false;
        if ($integrationName == $this->factory->getTranslator()->trans('le.integration.name.instapage')) {
            $payload      =$jsonData;
            $enocodeNeeded=true;
        } elseif ($integrationName == $this->factory->getTranslator()->trans('le.integration.name.unbounce')) {
            $payload=$jsonData->data_json;
        } elseif ($integrationName == $this->factory->getTranslator()->trans('le.integration.name.calendly')) {
            $payload= $jsonData->payload;
            if (isset($payload->invitee)) {
                $payload = $payload->invitee;
            }
            $enocodeNeeded=true;
        } elseif ($integrationName == 'facebook_lead_ads') {
            $payload  =$jsonData;
            $leadgenid='';
            if (isset($payload->leadgen_id)) {
                $leadgenid=$payload->leadgen_id;
            }
            $fbapiHelper        = $this->factory->getHelper('fbapi');
            $integrationsettings=$this->getIntegrationSettingsbyName($integrationName);
            if (sizeof($integrationsettings) > 0 && !empty($leadgenid)) {
                $fbapiHelper->initFBAdsApi($integrationsettings['authtoken']);
                $payload = $fbapiHelper->getLeadDetailsByID($leadgenid);
            }
        }
        if ($enocodeNeeded) {
            $payload=json_encode($payload);
        }
        $integration=$this->getIntegrationInfobyName($integrationName);
        if ($integration) {
            $newHistory=new IntegrationPayLoadHistory();
            $newHistory->setIntegration($integration);
            $newHistory->setPayLoad($payload);
            $newHistory->setCreatedOn(new \DateTime());
            $intgrepo=$this->getIntegrationRepository();
            $intgrepo->saveEntity($newHistory);
        }
    }

    public function updateIntegrationFieldInfo($payload, $integrationName)
    {
        $integrationsettings=$this->getIntegrationSettingsbyName($integrationName);
        if ($integrationName == $this->factory->getTranslator()->trans('le.integration.name.instapage')) {
            $response=$this->getInstaPageFieldInformation($payload);
        } elseif ($integrationName == $this->factory->getTranslator()->trans('le.integration.name.unbounce')) {
            $payload =json_decode($payload->data_json);
            $response=$this->getUnBounceFieldInformation($payload);
        } elseif ($integrationName == 'facebook_lead_ads') {
            $response=$this->getFBLeadFieldInformation($payload, $integrationsettings);
        }
        if (isset($response['groupname']) && !empty($response['groupname']) && isset($response['fields']) && !empty($response['fields'])) {
            $integration=$this->getIntegrationInfobyName($integrationName);
            if ($integration) {
                $fieldmapping=$this->getFieldMappingObject($response['groupname'], $integration);
                try {
                    $fieldmapping->setIntegration($integration);
                    $fieldmapping->setGroup($response['groupname']);
                    $fieldmapping->setFields($response['fields']);
                    $intgrepo=$this->getIntegrationRepository();
                    $intgrepo->saveEntity($fieldmapping);
                } catch (\Exception $ex) {
                }
            }
        }
    }

    public function parseJsonResponse($jsonData, $integrationName, $properties)
    {
        $data            = [];
        $data['isvalid'] = false;
        $integration     =$this->getIntegrationInfobyName($integrationName);
        if ($integrationName == $this->factory->getTranslator()->trans('le.integration.name.instapage')) {
            if (isset($jsonData->page_name) && ($properties['page_name'] == '' || $properties['page_name'] == $jsonData->page_name)) {
                $data['firstname']  = isset($jsonData->first_name) ? $jsonData->first_name : '';
                $data['lastname']   = isset($jsonData->last_name) ? $jsonData->last_name : '';
                $data['email']      = isset($jsonData->email) ? $jsonData->email : '';
                $data               =$this->parseInstaPageData($integration, $data, $jsonData);
            }
        } elseif ($integrationName == $this->factory->getTranslator()->trans('le.integration.name.unbounce')) {
            $jsonData           = json_decode($jsonData->data_json);
            if (isset($jsonData->page_name) && ($properties['pagename'] == '' || $properties['pagename'] == $jsonData->page_name[0])) {
                $data['firstname']  = isset($jsonData->first_name) ? $jsonData->first_name[0] : '';
                $data['lastname']   = isset($jsonData->last_name) ? $jsonData->last_name[0] : '';
                $data['email']      = isset($jsonData->email) ? $jsonData->email[0] : '';
                $data               =$this->parseUnBounceData($integration, $data, $jsonData);
            }
        } elseif ($integrationName == $this->factory->getTranslator()->trans('le.integration.name.calendly')) {
            $payload            = $jsonData->payload;
            $event              = $payload->event_type;
            if ($properties['event_name'] == '' || $properties['event_name'] == $event->name) {
                $inviteeData        = $payload->invitee;
                $name               = explode(' ', $inviteeData->name);
                $data['firstname']  = $name[0];
                $data['lastname']   = isset($name[1]) ? $name[1] : '';
                $data['email']      = $inviteeData->email;
                $data               =$this->applydefaultData($integration, $data);
            }
        } elseif ($integrationName == 'facebook_lead_ads') {
            $leadgenData = $jsonData;
            $leadgenid   ='';
            $fbpageid    ='';
            $fbformid    ='';
            if (isset($leadgenData->leadgen_id)) {
                $leadgenid=$leadgenData->leadgen_id;
            }
            if (isset($leadgenData->page_id)) {
                $fbpageid=$leadgenData->page_id;
            }
            if (isset($leadgenData->form_id)) {
                $fbformid=$leadgenData->form_id;
            }
            if (!empty($leadgenid) && !empty($fbpageid) && !empty($fbformid)) {
                $eventFbPage     =$properties['fbpage'];
                $eventLeadGenForm=$properties['leadgenform'];
                if (empty($eventFbPage) || $eventFbPage == '-1' || ($eventFbPage == $fbpageid && $eventLeadGenForm == $fbformid)) {
                    if ($integration) {
                        $data=$this->parseFBLeadData($integration, $leadgenid, $data, $fbpageid, $fbformid);
                    }
                }
            }
        }
        if (!empty($data['email'])) {
            $data['isvalid'] = true;
        }

        return $data;
    }

    public function parseInstaPageData($integration, $data, $payload)
    {
        $fieldMapping=$integration->getFeatureSettings();
        $fieldMapping=isset($fieldMapping['field_mapping']) ? $fieldMapping['field_mapping'] : [];
        $pagename    =$payload->page_name;
        $fieldvalues =[];
        foreach ($payload as $key => $value) {
            $fieldvalues[$key]=$value;
        }
        if (!empty($fieldMapping)) {
            foreach ($fieldMapping as $mappingdetails) {
                if (isset($mappingdetails['localfield'])) {
                    $isRemoteMatch=false;
                    if (isset($mappingdetails['remotefield'])) {
                        $remotefields=$mappingdetails['remotefield'];
                        foreach ($remotefields as $remotefield) {
                            $remotefieldarr =explode('@$@', $remotefield);
                            $groupname      =$remotefieldarr[0];
                            $remotefieldname=$remotefieldarr[1];
                            if ($groupname == $pagename && isset($fieldvalues[$remotefieldname])) {
                                $data[$mappingdetails['localfield']] = $fieldvalues[$remotefieldname];
                                $isRemoteMatch                       =true;
                            }
                        }
                    }
                    if (!$isRemoteMatch && isset($mappingdetails['defaultvalue'])) {
                        $data[$mappingdetails['localfield']] = $mappingdetails['defaultvalue'];
                    }
                }
            }
        }

        return $data;
    }

    public function parseUnBounceData($integration, $data, $payload)
    {
        $fieldMapping=$integration->getFeatureSettings();
        $fieldMapping=isset($fieldMapping['field_mapping']) ? $fieldMapping['field_mapping'] : [];
        $pagename    =$payload->page_name[0];
        $fieldvalues =[];
        foreach ($payload as $key => $value) {
            $fieldvalues[$key]=$value[0];
        }
        if (!empty($fieldMapping)) {
            foreach ($fieldMapping as $mappingdetails) {
                if (isset($mappingdetails['localfield'])) {
                    $isRemoteMatch=false;
                    if (isset($mappingdetails['remotefield'])) {
                        $remotefields=$mappingdetails['remotefield'];
                        foreach ($remotefields as $remotefield) {
                            $remotefieldarr =explode('@$@', $remotefield);
                            $groupname      =$remotefieldarr[0];
                            $remotefieldname=$remotefieldarr[1];
                            if ($groupname == $pagename && isset($fieldvalues[$remotefieldname])) {
                                $data[$mappingdetails['localfield']] = $fieldvalues[$remotefieldname];
                                $isRemoteMatch                       =true;
                            }
                        }
                    }
                    if (!$isRemoteMatch && isset($mappingdetails['defaultvalue'])) {
                        $data[$mappingdetails['localfield']] = $mappingdetails['defaultvalue'];
                    }
                }
            }
        }

        return $data;
    }

    public function applydefaultData($integration, $data)
    {
        $fieldMapping=$integration->getFeatureSettings();
        $fieldMapping=isset($fieldMapping['field_mapping']) ? $fieldMapping['field_mapping'] : [];
        if (!empty($fieldMapping)) {
            foreach ($fieldMapping as $mappingdetails) {
                if (isset($mappingdetails['localfield'])) {
                    if (isset($mappingdetails['defaultvalue'])) {
                        $data[$mappingdetails['localfield']] = $mappingdetails['defaultvalue'];
                    }
                }
            }
        }
        $data['created_on'] =new \DateTime();

        return $data;
    }

    public function parseFBLeadData($integration, $leadgenid, $data, $fbpageid, $fbformid)
    {
        $fbapiHelper        = $this->factory->getHelper('fbapi');
        $integrationsettings=$integration->getApiKeys();
        if (sizeof($integrationsettings) > 0) {
            $fbapiHelper->initFBAdsApi($integrationsettings['authtoken']);
            $leadgenformname=$fbapiHelper->getLeadGenFormNameByID($integrationsettings['authtoken'], $fbpageid, $fbformid);
            $fieldMapping   =$integration->getFeatureSettings();
            $fieldMapping   =isset($fieldMapping['field_mapping']) ? $fieldMapping['field_mapping'] : [];
            $fbleadjson     =$fbapiHelper->getLeadDetailsByID($leadgenid);
            if (!empty($fbleadjson)) {
                $fblead           = json_decode($fbleadjson);
                if (isset($fblead->field_data)) {
                    $fbfieldsinfo =$fblead->field_data;
                    $fbleadcreated=$fblead->created_time;
                    $fieldvalues  =[];
                    foreach ($fbfieldsinfo as $fbfieldinfo) {
                        $fbfieldname =$fbfieldinfo->name;
                        $fbfieldvalue=$fbfieldinfo->values[0];
                        if ($fbfieldname == 'full_name') {
                            $data['firstname'] = $fbfieldvalue;
                        } elseif ($fbfieldname == 'email') {
                            $data['email'] = $fbfieldvalue;
                        } elseif ($fbfieldname == 'first_name') {
                            $data['firstname'] = $fbfieldvalue;
                        } elseif ($fbfieldname == 'last_name') {
                            $data['lastname'] = $fbfieldvalue;
                        }
                        $fieldvalues[$fbfieldname]=$fbfieldvalue;
                    }
                    if (!empty($fieldMapping)) {
                        foreach ($fieldMapping as $mappingdetails) {
                            if (isset($mappingdetails['localfield'])) {
                                $isRemoteMatch=false;
                                if (isset($mappingdetails['remotefield'])) {
                                    $remotefields=$mappingdetails['remotefield'];
                                    foreach ($remotefields as $remotefield) {
                                        $remotefieldarr =explode('@$@', $remotefield);
                                        $groupname      =$remotefieldarr[0];
                                        $remotefieldname=$remotefieldarr[1];
                                        if ($groupname == $leadgenformname && isset($fieldvalues[$remotefieldname])) {
                                            $data[$mappingdetails['localfield']] = $fieldvalues[$remotefieldname];
                                            $isRemoteMatch                       =true;
                                        }
                                    }
                                }
                                if (!$isRemoteMatch && isset($mappingdetails['defaultvalue'])) {
                                    $data[$mappingdetails['localfield']] = $mappingdetails['defaultvalue'];
                                }
                            }
                        }
                    }
                    $data['created_on'] =new \DateTime($fbleadcreated);
                }
            }
        }

        return $data;
    }

    public function subscribeCalendlyWebhook($token, $webhookUrl)
    {
        $events   = [];
        $events[] = 'invitee.created';
        $events[] = 'invitee.canceled';
        $data     = [
            'url'    => $webhookUrl,
            'events' => $events,
        ];

        $encodedData = json_encode($data);

        $handle = curl_init('https://calendly.com/api/v1/hooks');
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $encodedData);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'X-TOKEN: '.$token]);

        $result = curl_exec($handle);
        $result = json_decode($result);

        return $result;
    }

    public function deleteCalendlyWebhook($token, $webhookid)
    {
        $handle = curl_init('https://calendly.com/api/v1/hooks/'.$webhookid);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'X-TOKEN: '.$token]);

        $result = curl_exec($handle);
        $result = json_decode($result);

        return $result;
    }

    public function getInstaPageFieldInformation($payload)
    {
        $response=[];
        if (isset($payload->page_name)) {
            $response['groupname']=$payload->page_name;
            $fielddetails         =[];
            foreach ($payload as $key => $value) {
                $fielddetails[]=$key;
            }
            $response['fields'] =  $fielddetails;
        }

        return $response;
    }

    public function getUnBounceFieldInformation($payload)
    {
        $response=[];
        if (isset($payload->page_name)) {
            $response['groupname']=$payload->page_name[0];
            $fielddetails         =[];
            foreach ($payload as $key => $value) {
                $fielddetails[]=$key;
            }
            $response['fields'] =  $fielddetails;
        }

        return $response;
    }

    public function getFBLeadFieldInformation($payload, $integrationsettings)
    {
        $response =[];
        $leadgenid='';
        if (isset($payload->leadgen_id)) {
            $leadgenid=$payload->leadgen_id;
        }
        $fbpageid='';
        if (isset($payload->page_id)) {
            $fbpageid=$payload->page_id;
        }
        $fbformid='';
        if (isset($payload->form_id)) {
            $fbformid=$payload->form_id;
        }
        $fbapiHelper       = $this->factory->getHelper('fbapi');
        if (sizeof($integrationsettings) > 0 && !empty($leadgenid)) {
            $fbapiHelper->initFBAdsApi($integrationsettings['authtoken']);
            $response['groupname']=$fbapiHelper->getLeadGenFormNameByID($integrationsettings['authtoken'], $fbpageid, $fbformid);
            $payload              = $fbapiHelper->getLeadDetailsByID($leadgenid);
            $payload              =json_decode($payload);
            $fielddetails         =[];
            if (isset($payload->field_data)) {
                $fbfieldsinfo=$payload->field_data;
                foreach ($fbfieldsinfo as $fbfieldinfo) {
                    $fielddetails[]=$fbfieldinfo->name;
                }
            }
            // $fielddetails[]="email";
            //  $fielddetails[]="address";
            //  $fielddetails[]="website";
            //  $response["groupname"]="Test Form";
            $response['fields'] =  $fielddetails;
        }

        return $response;
    }
}
