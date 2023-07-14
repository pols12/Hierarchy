<?php
namespace ItemHierarchy\View\Helper;

use Laminas\Form\View\Helper\AbstractHelper;
use Omeka\Form\Element as OmekaElement;
use Laminas\Form\Element;
use Laminas\Form\Form;

class HierarchyHelper extends AbstractHelper
{
    /**
     * Return the HTML necessary to render all hierarchy form elements.
     *
     */
    public function hierarchyFormElements()
    {
        $hierarchies = $this->getView()->api()->search('item_hierarchy')->getContent();
        
        $html = '<div id="hierarchies">';
        foreach ($hierarchies as $hierarchy) {
            $html .= $this->hierarchyFormElement($hierarchy);
        }
        $html .= '</div>';
        return $html;
    }

    public function hierarchyFormElement($form, $hierarchy = null) {
        $view = $this->getView();
        return $view->partial('item-hierarchy/common/hierarchy', [
            'label' => $hierarchy ? $hierarchy->getLabel() : null,
            'hierarchyContent' => $this->formElement($form, $hierarchy)
        ]);
    }

    public function formElement($form, $hierarchy = null) {
        $defaults = [
            'label' => '',
        ];
        $data = $hierarchy ? $hierarchy->getData() + $defaults : $defaults;
        
        // $form = new Form();
        $form->add([
            'name' => 'label',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Hierarchy Label', // @translate
            ],
        ]);

        $form->setData([
            'label' => $data['label'],
        ]);

        $view = $this->getView();
        return $view->formCollection($form);
    }
}
