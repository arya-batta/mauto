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

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\CoreBundle\Helper\InputHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AjaxController.
 */
class AjaxController extends CommonAjaxController
{
    /**
     * @param Request $request
     * @param string  $name
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function reorderFieldsAction(Request $request, $bundle, $name = 'fields')
    {
        if ('form' === $name) {
            $name = 'fields';
        }
        $dataArray   = ['success' => 0];
        $sessionId   = InputHelper::clean($request->request->get('formId'));
        $sessionName = 'mautic.form.'.$sessionId.'.'.$name.'.modified';
        $session     = $this->get('session');
        $orderName   = ($name == 'fields') ? 'leform' : 'leform_action';
        $order       = InputHelper::clean($request->request->get($orderName));
        $components  = $session->get($sessionName);

        if (!empty($order) && !empty($components)) {
            $components = array_replace(array_flip($order), $components);
            $session->set($sessionName, $components);
            $dataArray['success'] = 1;
        }

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function reorderActionsAction(Request $request)
    {
        return $this->reorderFieldsAction($request, 'actions');
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function updateFormFieldsAction(Request $request)
    {
        $formId     = InputHelper::int($request->request->get('formId'));
        $dataArray  = ['success' => 0];
        $model      = $this->getModel('form');
        $entity     = $model->getEntity($formId);
        $formFields = $entity->getFields();
        $fields     = [];

        foreach ($formFields as $field) {
            if ($field->getType() != 'button') {
                $properties = $field->getProperties();
                $options    = [];

                if (!empty($properties['list']['list'])) {
                    //If the field is a SELECT field then the data gets stored in [list][list]
                    $optionList = $properties['list']['list'];
                } elseif (!empty($properties['optionlist']['list'])) {
                    //If the field is a radio or a checkbox then it will be stored in [optionlist][list]
                    $optionList = $properties['optionlist']['list'];
                }
                if (!empty($optionList)) {
                    foreach ($optionList as $listItem) {
                        if (is_array($listItem) && isset($listItem['value']) && isset($listItem['label'])) {
                            //The select box needs values to be [value] => label format so make sure we have that style then put it in
                            $options[$listItem['value']] = $listItem['label'];
                        } elseif (!is_array($listItem)) {
                            //Keeping for BC
                            $options[] = $listItem;
                        }
                    }
                }

                $fields[] = [
                    'id'      => $field->getId(),
                    'label'   => $field->getLabel(),
                    'alias'   => $field->getAlias(),
                    'type'    => $field->getType(),
                    'options' => $options,
                ];
            }
        }

        $dataArray['fields']  = $fields;
        $dataArray['success'] = 1;

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * Ajax submit for forms.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function submitAction()
    {
        $response     = $this->forwardWithPost('MauticFormBundle:Public:submit', $this->request->request->all(), [], ['ajax' => true]);
        $responseData = json_decode($response->getContent(), true);
        $success      = (!in_array($response->getStatusCode(), [404, 500]) && empty($responseData['errorMessage'])
            && empty($responseData['validationErrors']));

        $message = '';
        $type    = '';
        if (isset($responseData['successMessage'])) {
            $message = $responseData['successMessage'];
            $type    = 'notice';
        } elseif (isset($responseData['errorMessage'])) {
            $message = $responseData['errorMessage'];
            $type    = 'error';
        }

        $data = array_merge($responseData, ['message' => $message, 'type' => $type, 'success' => $success]);

        return $this->sendJsonResponse($data);
    }

    public function scanFormUrlAction(Request $request)
    {
        $dataArray           = ['success' => true];
        $scanurl             = $request->request->get('scanurl'); //InputHelper::int();
        $dataArray['scanurl']=$scanurl;
        $response            =$this->sendFormUrlScanRequest($scanurl);
        if (!isset($response['error'])) {
            if (empty($response) || (isset($response['totalcount']) && $response['totalcount'] == 0)) {
                $dataArray['success']=false;
                $dataArray['message']=$this->translator->trans('le.smart.form.scan.error');
            } else {
                $htmlContent = $this->renderView(
                    'MauticFormBundle:Builder:formlist.html.php',
                   ['forms'    => $response['forms'],
                   'totalcount'=> $response['totalcount'], ]
                );
                $dataArray['newContent']=$htmlContent;
                $dataArray['totalCount']=$response['totalcount'];
            }
        } else {
            $dataArray['success']=false;
            $dataArray['message']=$response['error'];
        }

        return $this->sendJsonResponse($dataArray);
    }

    private function sendFormUrlScanRequest($scanurl)
    {
        try {
            $apikey                      = $this->coreParametersHelper->getParameter('Phantom_JS_Cloud_Apikey');
            $requestpayload              =[];
            $requestpayload['url']       =$scanurl; //http://cloud.cratio.com/maxm/form.html
            $requestpayload['renderType']='html';
            $requestpayload              = json_encode($requestpayload);
            $ch                          = curl_init("http://PhantomJScloud.com/api/browser/v2/$apikey/");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestpayload);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                ['Content-Type:application/json',
                    'Content-Length: '.strlen($requestpayload), 'Accept:', ]
            );
            $html = curl_exec($ch);
            curl_close($ch);
            $response=[];
            if ($html != '' && !$this->isJsonResponse($html)) {
                libxml_use_internal_errors(true);
                $dom = new \DOMDocument();
                $dom->loadHTML($html);
                $dom->preserveWhiteSpace = false;
                $dom->validateOnParse    = true;
                $xpath                   = new \DOMXPath($dom);
                $formlist                = $xpath->query('//form');
                $formcount               =0;
                $forms                   =[];
                foreach ($formlist as $formindex=>$form) {
                    $name        =$form->getAttribute('name');
                    $id          =$form->getAttribute('id');
                    $form        =[];
                    $form['name']=$name;
                    $form['id']  =$id;
                    $query       ='';
                    if ($id != '') {
                        $query="//form[@id='$id']//input|//form[@id='$id']//select|//form[@id='$id']//textarea";
                    } elseif ($name != '') {
                        $query="//form[@name='$name']//input|//form[@name='$name']//select|//form[@name='$name']//textarea";
                    }
                    if ($query != '') {
                        $fieldlist = $xpath->query($query);
                        $fields    =[];
                        foreach ($fieldlist as $fieldindex=>$fieldel) {
                            $field=[];
                            $type =$fieldel->getAttribute('type');
                            if ($type != 'submit' && $type != 'checkbox' && $type != 'password' && $type != 'hidden') {
                                $fieldname     =$fieldel->getAttribute('name');
                                $fieldvalue    =$fieldel->getAttribute('value');
                                $field['name'] =$fieldname;
                                $field['value']=$fieldvalue;
                                $field['type'] =$type;
                                $fields[]      =$field;
                            }
                        }
                        $form['fields']=$fields;
                    }
                    ++$formcount;
                    $forms[]=$form;
                }
                $response['totalcount']=$formcount;
                $response['forms']     =$forms;
            }
        } catch (Exception $ex) {
            $response['error']=$ex->getMessage();
        }

        return $response;
    }

    public function isJsonResponse($string)
    {
        json_decode($string);

        return json_last_error() == JSON_ERROR_NONE;
    }
}
