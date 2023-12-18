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
            'jsTreeData' => $hierarchy ? $this->toJstree($hierarchy) : '',
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

    public function toJstree($hierarchy)
    {
        $hierarchyID = $hierarchy->id();
        $allGroupings = $this->getView()->api()->search('item_hierarchy_grouping', ['hierarchy' => $hierarchyID, 'sort_by' => 'position'])->getContent();

        $iterate = function ($groupings) use (&$iterate, &$allGroupings, &$childNode) {
            $jstreeNodes = [];
            foreach ($groupings as $key => $grouping) {
                // Skip groupings with parent unless on 'children' subarray iteration
                if ($grouping->getParentGrouping() != 0 && !$childNode) {
                    continue;
                }
                $jstreeNodes[$key] = [
                    'text' => $grouping->getLabel() ?: '',
                    'data' => [
                        'label' => $grouping->getLabel() ?: '',
                        'itemSet' => $grouping->getItemSet() ? $grouping->getItemSet()->getId() : '',
                        'groupingID' => $grouping->id(),
                    ],
                ];
                // Return any groupings with current grouping ID as parent
                $childArray = array_filter($allGroupings, function($child) use($grouping) {
                    return $child->getParentGrouping() == $grouping->id();
                });
                if (count($childArray) > 0) {
                    $childNode = true;
                     $jstreeNodes[$key]['children'] = $iterate($childArray);
                } else {
                    $childNode = false;
                }
            }
            return array_values($jstreeNodes);
        };

        return $iterate($allGroupings);
    }
}
