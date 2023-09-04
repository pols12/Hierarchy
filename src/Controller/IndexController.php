<?php
namespace ItemHierarchy\Controller;

use ItemHierarchy\Form\ConfigForm;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Model\ViewModel;
use Laminas\Form\Form;
use Omeka\Api\Exception as ApiException;
use Omeka\Settings\Settings;
use Omeka\Stdlib\Message;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $form = $this->getForm(Form::class)->setAttribute('id', 'hierarchy-form');

        if ($this->getRequest()->isPost()) {
            $formData = $this->params()->fromPost();
            if (isset($formData['layout']) && $formData['layout'] == 'hierarchy') {
                $content = $this->viewHelpers()->get('hierarchyHelper')->hierarchyFormElement($form);
                $response = $this->getResponse();
                $response->setContent($content);
                return $response;
            } else {
                // $jstree = json_decode($formData['jstree'], true);
                // $jstreeData = $this->fromJstree($jstree);
                $jstreeData = [];
                $form->setData($formData);
                if ($form->isValid()) {
                    unset($formData['form_csrf']);
                    foreach($formData['hierarchy'] as $hierarchyData) {
                        //check if hierarchy already exists before adding
                        $hierarchyID = $hierarchyData['id'] ?: 0;
                        $content = $this->api()->search('item_hierarchy', ['id' => $hierarchyID])->getContent();
                        if (empty($content)) {
                            $response = $this->api($form)->create('item_hierarchy', $hierarchyData);
                        } else {
                            $response = $this->api($form)->update('item_hierarchy', $hierarchyData['id'], $hierarchyData);
                        }
                    }
                    if ($response) {
                        $this->messenger()->addSuccess('Item Hierarchy successfully updated'); // @translate
                        return $this->redirect()->refresh();
                    }
                } else {
                    $this->messenger()->addFormErrors($form);
                }
            }
        } else {
            $jstreeData = [];
        }

        $view = new ViewModel;
        $view->setVariable('hierarchyTree', $this->toJstree($jstreeData));
        $view->setVariable('form', $form);
        return $view;
    }

    public function toJstree(array $data)
    {
        $buildLinks = function ($linksIn) use (&$buildLinks) {
            $linksOut = [];
            foreach ($linksIn as $linkData) {
                $linkData = $linkData['data'];
                $linkLabel = isset($linkData['label']) && '' !== trim($linkData['label']) ? $linkData['label'] : null;
                $linksOut[] = [
                    'text' => $linkLabel,
                    'data' => [
                        'label' => $linkData['label'],
                        'query' => $linkData['query'],
                    ],
                    'children' => $linkData['links'] ? $buildLinks($linkData['links']) : [],
                ];
            }
            return $linksOut;
        };
        $links = $buildLinks($data);
        return $links;
    }
}
