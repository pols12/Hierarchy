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
    public function hierarchyFormElements($form)
    {
        $hierarchies = $this->getView()->api()->search('item_hierarchy', ['sort_by' => 'position'])->getContent();

        $html = '<div id="hierarchies">';
        foreach ($hierarchies as $hierarchy) {
            $html .= $this->hierarchyFormElement($form, $hierarchy);
        }
        $html .= '</div>';
        return $html;
    }

    public function hierarchyFormElement($form, $hierarchy = null) {
        $view = $this->getView();
        return $view->partial('item-hierarchy/common/hierarchy', [
            'label' => $hierarchy ? $hierarchy->getLabel() : null,
            'jsTreeData' => $hierarchy ? $hierarchy->getData() : '',
            'hierarchyContent' => $this->formElement($form, $hierarchy),
        ]);
    }

    public function formElement($form, $hierarchy = null) {
        $defaults = [
            'id' => '',
            'label' => '',
            'data' => '',
            'position' => '',
            'delete' => 0,
        ];
        $data = $hierarchy ? $hierarchy->getJsonLd() + $defaults : $defaults;
        
        $form->add([
            'name' => 'hierarchy[__hierarchyIndex__][label]',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Hierarchy Label', // @translate
            ],
        ]);

        $form->add([
            'name' => 'hierarchy[__hierarchyIndex__][id]',
            'type' => 'hidden',
        ]);

        $form->add([
            'name' => 'hierarchy[__hierarchyIndex__][data]',
            'type' => 'hidden',
        ]);

        $form->add([
            'name' => 'hierarchy[__hierarchyIndex__][position]',
            'type' => 'hidden',
        ]);

        $form->add([
            'name' => 'hierarchy[__hierarchyIndex__][delete]',
            'type' => 'hidden',
        ]);

        $form->setData([
            'hierarchy[__hierarchyIndex__][label]' => $data['label'],
            'hierarchy[__hierarchyIndex__][id]' => $data['id'],
            'hierarchy[__hierarchyIndex__][data]' => $data['data'],
            'hierarchy[__hierarchyIndex__][position]' => $data['position'],
            'hierarchy[__hierarchyIndex__][delete]' => $data['delete'],
        ]);

        $view = $this->getView();
        return $view->formCollection($form);
    }
}
