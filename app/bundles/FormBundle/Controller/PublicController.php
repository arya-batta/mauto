<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\FormBundle\Controller;

use Mautic\CoreBundle\Controller\FormController as CommonFormController;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\FormBundle\Event\SubmissionEvent;
use Mautic\FormBundle\Model\FormModel;
use Mautic\LeadBundle\Helper\TokenHelper;
use Mautic\SubscriptionBundle\Entity\Account;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PublicController.
 */
class PublicController extends CommonFormController
{
    /**
     * @var array
     */
    private $tokens = [];

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function submitAction()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->accessDenied();
        }
        $isAjax        = $this->request->query->get('ajax', false);
        $form          = null;
        $post          = $this->request->request->get('leform');
        $messengerMode = (!empty($post['messenger']));
        $server        = $this->request->server->all();
        $return        = (isset($post['return'])) ? $post['return'] : false;

        if (empty($return)) {
            //try to get it from the HTTP_REFERER
            $return = (isset($server['HTTP_REFERER'])) ? $server['HTTP_REFERER'] : false;
        }
        if (!empty($return)) {
            //remove mauticError and mauticMessage from the referer so it doesn't get sent back
            $return = InputHelper::url($return, null, null, null, ['leError', 'leMessage'], true);
            $query  = (strpos($return, '?') === false) ? '?' : '&';
        }

        $translator = $this->get('translator');

        if (!isset($post['formId']) && isset($post['formid'])) {
            $post['formId'] = $post['formid'];
        } elseif (isset($post['formId']) && !isset($post['formid'])) {
            $post['formid'] = $post['formId'];
        }
        //check to ensure there is a formId
        if (!isset($post['formId'])) {
            $error = $translator->trans('mautic.form.submit.error.unavailable', [], 'flashes');
        } else {
            $formModel = $this->getModel('form.form');
            $form      = $formModel->getEntity($post['formId']);

            //check to see that the form was found
            if ($form === null) {
                $error = $translator->trans('mautic.form.submit.error.unavailable', [], 'flashes');
            } else {
                //get what to do immediately after successful post
                $postAction         = $form->getPostAction();
                $postActionProperty = $form->getPostActionProperty();
                //check to ensure the form is published
                $status             = $form->getPublishStatus();
                $dateTemplateHelper = $this->get('mautic.helper.template.date');
                if ($status == 'pending') {
                    $error = $translator->trans(
                        'mautic.form.submit.error.pending',
                        [
                            '%date%' => $dateTemplateHelper->toFull($form->getPublishUp()),
                        ],
                        'flashes'
                    );
                } elseif ($status == 'expired') {
                    $error = $translator->trans(
                        'mautic.form.submit.error.expired',
                        [
                            '%date%' => $dateTemplateHelper->toFull($form->getPublishDown()),
                        ],
                        'flashes'
                    );
                } elseif ($status != 'published') {
                    $error = $translator->trans('mautic.form.submit.error.unavailable', [], 'flashes');
                } else {
                    $result = $this->getModel('form.submission')->saveSubmission($post, $server, $form, $this->request, true);

                    if (!empty($result['errors'])) {
                        if ($messengerMode || $isAjax) {
                            $error = $result['errors'];
                        } else {
                            $error = ($result['errors']) ?
                                $this->get('translator')->trans('mautic.form.submission.errors').'<br /><ol><li>'.
                                implode('</li><li>', $result['errors']).'</li></ol>' : false;
                        }
                    } elseif (!empty($result['callback'])) {
                        /** @var SubmissionEvent $submissionEvent */
                        $submissionEvent = $result['callback'];

                        // Return the first Response object if one is defined
                        $firstResponseObject = false;
                        if ($callbackResponses = $submissionEvent->getPostSubmitCallbackResponse()) {
                            // Some submit actions already injected it's responses
                            foreach ($callbackResponses as $key => $response) {
                                if ($response instanceof Response) {
                                    $firstResponseObject = $key;
                                    break;
                                }
                            }
                        }

                        // These submit actions have requested a callback after all is said and done
                        $callbacksRequested = $submissionEvent->getPostSubmitCallback();
                        foreach ($callbacksRequested as $key => $callbackRequested) {
                            $callbackRequested['messengerMode'] = $messengerMode;
                            $callbackRequested['ajaxMode']      = $isAjax;
                            if (isset($callbackRequested['eventName'])) {
                                $submissionEvent->setPostSubmitCallback($key, $callbackRequested);

                                $this->get('event_dispatcher')->dispatch($callbackRequested['eventName'], $submissionEvent);
                            } elseif (isset($callbackRequested['callback'])) {
                                // @deprecated - to be removed in 3.0; use eventName instead - be sure to remove callback key support from SubmissionEvent::setPostSubmitCallback
                                $callback = $callbackRequested['callback'];
                                if (is_callable($callback)) {
                                    if (is_array($callback)) {
                                        $reflection = new \ReflectionMethod($callback[0], $callback[1]);
                                    } elseif (strpos($callback, '::') !== false) {
                                        $parts      = explode('::', $callback);
                                        $reflection = new \ReflectionMethod($parts[0], $parts[1]);
                                    } else {
                                        $reflection = new \ReflectionMethod(null, $callback);
                                    }

                                    //add the factory to the arguments
                                    $callbackRequested['factory'] = $this->factory;

                                    $pass = [];
                                    foreach ($reflection->getParameters() as $param) {
                                        if (isset($callbackRequested[$param->getName()])) {
                                            $pass[] = $callbackRequested[$param->getName()];
                                        } else {
                                            $pass[] = null;
                                        }
                                    }

                                    $callbackResponses[$key] = $reflection->invokeArgs($this, $pass);
                                }
                            }

                            if (!$firstResponseObject && $callbackResponses[$key] instanceof Response) {
                                $firstResponseObject = $key;
                            }
                        }

                        if ($firstResponseObject && !$messengerMode && !$isAjax) {
                            // Return the response given by the sbumit action

                            return $callbackResponses[$firstResponseObject];
                        }
                    } elseif (isset($result['submission'])) {
                        /** @var SubmissionEvent $submissionEvent */
                        $submissionEvent = $result['submission'];
                    }
                }
            }
        }

        if (isset($submissionEvent) && !empty($postActionProperty)) {
            // Replace post action property with tokens to support custom redirects, etc
            $postActionProperty = $this->replacePostSubmitTokens($postActionProperty, $submissionEvent);
        }

        if ($messengerMode || $isAjax) {
            // Return the call via postMessage API
            $data = ['success' => 1];
            if (!empty($error)) {
                if (is_array($error)) {
                    $data['validationErrors'] = $error;
                } else {
                    $data['errorMessage'] = $error;
                }
                $data['success'] = 0;
            } else {
                // Include results in ajax response for JS callback use
                if (isset($submissionEvent)) {
                    $data['results'] = $submissionEvent->getResults();
                }

                if ($postAction == 'redirect') {
                    $data['redirect'] = $postActionProperty;
                } elseif (!empty($postActionProperty)) {
                    $data['successMessage'] = [$postActionProperty];
                }

                if (!empty($callbackResponses)) {
                    foreach ($callbackResponses as $key => $response) {
                        // Convert the responses to something useful for a JS response
                        if ($response instanceof RedirectResponse && !isset($data['redirect'])) {
                            $data['redirect'] = $response->getTargetUrl();
                        } elseif ($response instanceof Response) {
                            if (!isset($data['successMessage'])) {
                                $data['successMessage'] = [];
                            }
                            $data['successMessage'][] = $response->getContent();
                        } elseif (is_array($response)) {
                            $data = array_merge($data, $response);
                        } elseif (is_string($response)) {
                            if (!isset($data['successMessage'])) {
                                $data['successMessage'] = [];
                            }
                            $data['successMessage'][] = $response;
                        } // ignore anything else
                    }
                }

                // Combine all messages into one
                if (isset($data['successMessage'])) {
                    $data['successMessage'] = implode('<br /><br />', $data['successMessage']);
                }
            }

            if (isset($post['formName'])) {
                $data['formName'] = $post['formName'];
            }

            if ($isAjax) {
                // Post via ajax so return a json responses
                return new JsonResponse($data);
            } else {
                $response = json_encode($data);

                return $this->render('MauticFormBundle::messenger.html.php', ['response' => $response]);
            }
        } else {
            if (!empty($error)) {
                if ($return) {
                    $hash = ($form !== null) ? '#'.strtolower($form->getAlias()) : '';

                    return $this->redirect($return.$query.'leError='.rawurlencode($error).$hash);
                } else {
                    $msg     = $error;
                    $msgType = 'error';
                }
            } elseif ($postAction == 'redirect') {
                return $this->redirect($postActionProperty);
            } elseif ($postAction == 'return') {
                if (!empty($return)) {
                    if (!empty($postActionProperty)) {
                        $return .= $query.'mauticMessage='.rawurlencode($postActionProperty);
                    }

                    return $this->redirect($return);
                } else {
                    $msg = $this->get('translator')->trans('mautic.form.submission.thankyou');
                }
            } else {
                $msg = $postActionProperty;
            }

            $session = $this->get('session');
            $session->set(
                'mautic.emailbundle.message',
                [
                    'message' => $msg,
                    'type'    => (empty($msgType)) ? 'notice' : $msgType,
                ]
            );

            return $this->redirect($this->generateUrl('le_form_postmessage'));
        }
    }

    /**
     * Displays a message.
     *
     * @return Response
     */
    public function messageAction()
    {
        $session = $this->get('session');
        $message = $session->get('mautic.emailbundle.message', []);

        $msg     = (!empty($message['message'])) ? $message['message'] : '';
        $msgType = (!empty($message['type'])) ? $message['type'] : 'notice';

        $analytics = $this->factory->getHelper('template.analytics')->getCode();

        if (!empty($analytics)) {
            $this->factory->getHelper('template.assets')->addCustomDeclaration($analytics);
        }

        $logicalName = $this->factory->getHelper('theme')->checkForTwigTemplate(':'.$this->coreParametersHelper->getParameter('theme').':message.html.php');

        return $this->render($logicalName, [
            'message'  => $msg,
            'type'     => $msgType,
            'template' => $this->coreParametersHelper->getParameter('theme'),
        ]);
    }

    /**
     * Gives a preview of the form.
     *
     * @param int $id
     *
     * @return Response
     *
     * @throws \Exception
     * @throws \Mautic\CoreBundle\Exception\FileNotFoundException
     */
    public function previewAction($id = 0)
    {
        /** @var FormModel $model */
        $objectId          = (empty($id)) ? InputHelper::int($this->request->get('id')) : $id;
        $css               = InputHelper::string($this->request->get('css'));
        $model             = $this->getModel('form.form');
        $form              = $model->getEntity($objectId);
        $customStylesheets = (!empty($css)) ? explode(',', $css) : [];
        $template          = null;
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $accmodel */
        $accmodel        = $this->getModel('subscription.accountinfo');
        $accrepo         = $accmodel->getRepository();
        $accountentity   = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0];
        } else {
            $account = new Account();
        }
        $ishidepoweredby = $account->getNeedpoweredby();
        if ($form === null || !$form->isPublished()) {
            $code           ='404';
            $status_text    =' I\'m sorry! I couldn\'t find the page you were looking for. ';
            $contenttemplate='MauticCoreBundle:Error:formerror.html.php';
            $basetemplate   ='MauticCoreBundle:Default:slim.html.php';

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'baseTemplate'   => $basetemplate,
                        'status_code'    => $code,
                        'status_text'    => $status_text,
                        'exception'      => '',
                        'logger'         => null,
                        'currentContent' => '',
                        'isPublicPage'   => true,
                    ],
                    'contentTemplate' => $contenttemplate,
                    'passthroughVars' => [
                        'error' => [
                            'code'      => $code,
                            'text'      => $status_text,
                            'exception' => '',
                            'trace'     => '',
                        ],
                        'leContent'     => 'error',
                    ],
                    'responseCode' => $code,
                ]
            );
        // return $this->notFound();
        } else {
            $html = $model->getContent($form, true, true, $ishidepoweredby);

            $model->populateValuesWithGetParameters($form, $html);

            $viewParams = [
                'content'     => $html,
                'stylesheets' => $customStylesheets,
                'name'        => $form->getName(),
            ];

            $template = $form->getTemplate();
            if (!empty($template)) {
                $theme = $this->factory->getTheme($template);
                if ($theme->getTheme() != $template) {
                    $config = $theme->getConfig();
                    if (in_array('form', $config['features'])) {
                        $template = $theme->getTheme();
                    } else {
                        $template = null;
                    }
                }
            }
        }

        $viewParams['template'] = $template;

        if (!empty($template)) {
            $logicalName  = $this->factory->getHelper('theme')->checkForTwigTemplate(':'.$template.':form.html.php');
            $assetsHelper = $this->factory->getHelper('template.assets');
            $analytics    = $this->factory->getHelper('template.analytics')->getCode();

            if (!empty($customStylesheets)) {
                foreach ($customStylesheets as $css) {
                    $assetsHelper->addStylesheet($css);
                }
            }

            $this->factory->getHelper('template.slots')->set('pageTitle', $form->getName());

            if (!empty($analytics)) {
                $assetsHelper->addCustomDeclaration($analytics);
            }

            return $this->render($logicalName, $viewParams);
        }

        return $this->render('MauticFormBundle::form.html.php', $viewParams);
    }

    /**
     * Generates JS file for automatic form generation.
     *
     * @return Response
     */
    public function generateAction()
    {
        // Don't store a visitor with this request
        defined('MAUTIC_NON_TRACKABLE_REQUEST') || define('MAUTIC_NON_TRACKABLE_REQUEST', 1);

        $formId = InputHelper::int($this->request->get('id'));

        $model = $this->getModel('form.form');
        $form  = $model->getEntity($formId);
        $js    = '';
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $accmodel */
        $accmodel        = $this->getModel('subscription.accountinfo');
        $accrepo         = $accmodel->getRepository();
        $accountentity   = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0];
        } else {
            $account = new Account();
        }
        $ishidepoweredby = $account->getNeedpoweredby();

        if ($form !== null) {
            $status = $form->getPublishStatus();
            if ($status == 'published') {
                $js = $model->getAutomaticJavascript($form, $ishidepoweredby);
            }
        }

        $response = new Response();
        $response->setContent($js);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    /**
     * @return Response
     */
    public function embedAction()
    {
        $formId = InputHelper::int($this->request->get('id'));
        /** @var FormModel $model */
        $model = $this->getModel('form');
        $form  = $model->getEntity($formId);

        if ($form !== null) {
            $status = $form->getPublishStatus();
            if ($status === 'published') {
                if ($this->request->get('video')) {
                    return $this->render(
                        'MauticFormBundle:Public:videoembed.html.php',
                        ['form' => $form, 'fieldSettings' => $model->getCustomComponents()['fields']]
                    );
                }

                $content = $model->getContent($form, false, true);

                return new Response($content);
            }
        }

        return new Response('', Response::HTTP_NOT_FOUND);
    }

    /**
     * @param $string
     * @param $submissionEvent
     */
    private function replacePostSubmitTokens($string, SubmissionEvent $submissionEvent)
    {
        if (empty($this->tokens)) {
            if ($lead = $submissionEvent->getLead()) {
                $this->tokens = array_merge(
                    $submissionEvent->getTokens(),
                    TokenHelper::findLeadTokens(
                        $string,
                        $lead->getProfileFields()
                    )
                );
            }
        }

        return str_replace(array_keys($this->tokens), array_values($this->tokens), $string);
    }

    public function getSmartFormTrackerAction()
    {
        $js       = $this->getSmartFormTrackerContent();
        $response = new Response();
        $response->setContent($js);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    public function getSmartFormTrackerContent()
    {
        $postUrl=$this->generateUrl('le_smart_form_post_results', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return <<<JS
function includeJQuery(filename, onload) {
            if(!window.jQuery)
            {
                var head = document.getElementsByTagName('head')[0];
                var script = document.createElement('script');
                script.src = filename;
                script.type = 'text/javascript';
                script.onload = script.onreadystatechange = function() {
                    if (script.readyState) {
                        if (script.readyState === 'complete' || script.readyState === 'loaded') {
                            script.onreadystatechange = null;
                            onload(false);
                        }
                    }
                    else {
                        onload(false);
                    }
                };
                head.appendChild(script);
            }
            else
            {
                onload(true);
            }
        }
        includeJQuery('https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js?t=' + Date.now(), function(isJqueryAvailable) {
            var LeJQ;
            if(isJqueryAvailable) {
                LeJQ = jQuery;
            } else {
                LeJQ = $.noConflict();
            }
            LeJQ(document).ready(function() {               
                TrackingForm = function() {                   
                    this.init();
                }

                LeJQ.extend(TrackingForm.prototype, {                    
                    API_METHOD: "POST",
                    API_ENDPOINT: "{$postUrl}",                  

                    init: function() {
                        LeJQ(document).on('submit','form', function(e) {
                            var formElemId = "";
                            var formElemName = "";
                            if(typeof LeJQ(this).attr('id') !== "undefined") {
                                formElemId = LeJQ(this).attr('id');                                
                            }
                            if(typeof LeJQ(this).attr('name') !== "undefined") {
                                formElemName = LeJQ(this).attr('name');                              
                            }
                            var formData = LeJQ(this).serialize();                            
                            var filePath = document.location.pathname;
                            var domain = TrackingForm.prototype.getDomain(document.location.origin);
                            var dataToPost = {'smartformid': formElemId, 'smartformname': formElemName, 'smartformdata': formData, 'file_path': filePath, 'domain': domain};
                            TrackingForm.prototype.sendData(dataToPost);
                        });
                    },

                    getDomain: function(url) {
                        var match = url.match(/:\/\/(www[0-9]?\.)?(.[^/:]+)/i);
                        if (match != null && match.length > 2 && typeof match[2] === 'string' && match[2].length > 0) {
                            return match[2];
                        } else {
                            return null;
                        }
                    },

                    sendData: function(dataToPost) {                        
                        LeJQ.ajax({
                            type: this.API_METHOD,
                            url: this.API_ENDPOINT,
                            data: LeJQ.param(dataToPost),
                            async:false,
                            success: function (e) {
                                if(e.download){
                                    var link = document.createElement("a");
                                    link.download = "Test";
                                    link.target = "_blank";

                                    // Construct the URI
                                    link.href = e.downloadUrl;
                                    document.body.appendChild(link);
                                    link.click();

                                    // Cleanup the DOM
                                    document.body.removeChild(link);
                                    
                                }
                                if (e == '1') {
                                    exists();
                                }
                                if (e == '2') {
                                    success();
                                }
                                if (e == '3') {
                                    error();
                                }
                                if (e == '4') {
                                    confirm();
                                }
                                return;
                            }
                        })
                    }
                });
                var form = new TrackingForm();
            });
        });
JS;
    }

    public function smartFormSubmitAction()
    {
        $responses        =['message'=> 'Lead created successfully!.'];
        $formid           = $this->request->get('smartformid', '');
        $formname         = $this->request->get('smartformname', '');
        $formdata         = $this->request->get('smartformdata', '');
        $filepath         = $this->request->get('file_path', '');
        $domain           = $this->request->get('domain', '');
        parse_str($formdata, $post_results);
        $server        = $this->request->server->all();
        $formmodel     =$this->getModel('form');
        $formrepository=$formmodel->getRepository();
        $messengerMode = true;
        if ($formname == '') {
            $formname = null;
        }
        $forms = [];
        if ($formname == '' && $formid == '') {
            $forms = $this->findSmartFormwithFields($post_results);
        } else {
            $forms = $formrepository->findBy(
                [
                    'smartformname' => $formname,
                    'smartformid'   => $formid,
                ]
            );
        }
        if (sizeof($forms) > 0 && sizeof($post_results) > 0) {
            try {
                $result = $this->getModel('form.submission')->saveSubmission($post_results, $server, $forms[0], $this->request, true);
                if (!empty($result['callback'])) {
                    /** @var SubmissionEvent $submissionEvent */
                    $submissionEvent = $result['callback'];

                    // Return the first Response object if one is defined
                    $firstResponseObject = false;
                    if ($callbackResponses = $submissionEvent->getPostSubmitCallbackResponse()) {
                        // Some submit actions already injected it's responses
                        foreach ($callbackResponses as $key => $callsresponse) {
                            if ($callsresponse instanceof Response) {
                                $firstResponseObject = $key;
                                break;
                            }
                        }
                    }

                    // These submit actions have requested a callback after all is said and done
                    $callbacksRequested = $submissionEvent->getPostSubmitCallback();
                    foreach ($callbacksRequested as $key => $callbackRequested) {
                        $callbackRequested['messengerMode'] = $messengerMode;
                        if (isset($callbackRequested['eventName'])) {
                            $submissionEvent->setPostSubmitCallback($key, $callbackRequested);

                            $this->get('event_dispatcher')->dispatch($callbackRequested['eventName'], $submissionEvent);
                        } elseif (isset($callbackRequested['callback'])) {
                            // @deprecated - to be removed in 3.0; use eventName instead - be sure to remove callback key support from SubmissionEvent::setPostSubmitCallback
                            $callback = $callbackRequested['callback'];

                            if (is_callable($callback)) {
                                if (is_array($callback)) {
                                    $reflection = new \ReflectionMethod($callback[0], $callback[1]);
                                } elseif (strpos($callback, '::') !== false) {
                                    $parts      = explode('::', $callback);
                                    $reflection = new \ReflectionMethod($parts[0], $parts[1]);
                                } else {
                                    $reflection = new \ReflectionMethod(null, $callback);
                                }

                                //add the factory to the arguments
                                $callbackRequested['factory'] = $this->factory;

                                $pass = [];
                                foreach ($reflection->getParameters() as $param) {
                                    if (isset($callbackRequested[$param->getName()])) {
                                        $pass[] = $callbackRequested[$param->getName()];
                                    } else {
                                        $pass[] = null;
                                    }
                                }

                                $callbackResponses[$key] = $reflection->invokeArgs($this, $pass);
                            }
                        }

                        if (!$firstResponseObject && $callbackResponses[$key] instanceof Response) {
                            $firstResponseObject = $key;
                        }
                    }

                    if ($firstResponseObject && !$messengerMode) {
                        // Return the response given by the sbumit action

                        return $callbackResponses[$firstResponseObject];
                    }
                }
                if ($messengerMode) {
                    if (!empty($callbackResponses)) {
                        foreach ($callbackResponses as $key => $callresponse) {
                            $responses['download']   =true;
                            $responses['downloadUrl']=$callresponse['download'];
                        }
                    }
                }
            } catch (\Exception $ex) {
                $responses['message']='Lead creation failed due to technical error';
            }
        } else {
            $responses['message']='Form not found';
        }

        return new JsonResponse($responses);
    }

    public function findSmartFormwithFields($formdata)
    {
        $formmodel     =$this->getModel('form');
        $formrepository=$formmodel->getRepository();
        $forms         = $formrepository->findBy(
            [
                'smartformname' => null,
                'smartformid'   => null,
            ]
        );
        $smform    = [];
        $iscrtform = false;
        for ($i = 0; $i < sizeof($forms); ++$i) {
            $fields = $forms[$i]->getSmartFields();
            foreach ($fields as $field) {
                $fieldkey = $field['smartfield'];
                if (!isset($formdata[$fieldkey])) {
                    $iscrtform = false;
                    break;
                }
                $iscrtform = true;
                continue;
            }
            if ($iscrtform) {
                $smform[] = $forms[$i];
                break;
            }
        }

        return $smform;
    }
}
