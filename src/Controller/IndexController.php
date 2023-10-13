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
                $form->setData($formData);
                if ($form->isValid()) {
                    unset($formData['form_csrf']);
                    foreach($formData['hierarchy'] as $hierarchyData) {

                        // Check if hierarchy already exists before adding/removing/updating
                        $hierarchyID = $hierarchyData['id'] ?: 0;
                        $content = $this->api()->search('item_hierarchy', ['id' => $hierarchyID])->getContent();
                        if (!empty($hierarchyData['delete'])) {
                            if (!empty($content)) {
                                $response = $this->api($form)->delete('item_hierarchy', $hierarchyData['id']);
                            } else {
                                continue;
                            }
                        } else if (empty($content)) {
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
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;
    }

    public function groupingFormAction()
    {
        $view = new ViewModel;
        $view->setTerminal(true);

        $itemSetArray = [];
        $itemSets = $this->api()->search('item_sets')->getContent();
        foreach ($itemSets as $itemSet) {
            $itemSetArray[$itemSet->id()] = $itemSet->title();
        }

        $view->setVariable('itemSetArray', $itemSetArray);
        $view->setVariable('data', $this->params()->fromPost('data'));
        return $view;
    }
}
