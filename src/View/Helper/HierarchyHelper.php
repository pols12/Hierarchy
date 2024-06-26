<?php
namespace Hierarchy\View\Helper;

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
        $hierarchies = $this->getView()->api()->search('hierarchy', ['sort_by' => 'position'])->getContent();

        $html = '<div id="hierarchies">';
        foreach ($hierarchies as $hierarchy) {
            $html .= $this->hierarchyFormElement($form, $hierarchy);
        }
        $html .= '</div>';
        return $html;
    }

    public function hierarchyFormElement($form, $hierarchy = null) {
        $view = $this->getView();
        return $view->partial('hierarchy/common/hierarchy', [
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
        $allGroupings = $this->getView()->api()->search('hierarchy_grouping', ['hierarchy' => $hierarchyID, 'sort_by' => 'position'])->getContent();

        $iterate = function ($groupings) use (&$iterate, &$allGroupings, &$childNode, &$childCount, &$prevCount) {
            $jstreeNodes = [];
            foreach ($groupings as $key => $grouping) {
                // Skip groupings with parent unless on 'children' subarray iteration
                if ($grouping->getParentGrouping() != 0 && !$childNode) {
                    continue;
                }
                if ($grouping->getItemSet()) {
                    // Show itemSet count in jstree node label if hierarchy_show_count checked in config
                    $itemSetCount = $this->getView()->setting('hierarchy_show_count') ? $this->itemSetCount($grouping, $allGroupings) : '';
                    // If no grouping label, show itemSet title as grouping heading
                    $nodeText = $grouping->getLabel() ? $grouping->getLabel() . $itemSetCount : $grouping->getItemSet()->title() . $itemSetCount;
                } else {
                    $nodeText = $grouping->getLabel() ?: '';
                }
                $jstreeNodes[$key] = [
                    'text' => $nodeText,
                    'data' => [
                        'label' => $grouping->getLabel() ?: '',
                        'itemSet' => $grouping->getItemSet() ? $grouping->getItemSet()->id() : '',
                        'groupingID' => $grouping->id(),
                        'position' => $grouping->getPosition(),
                    ],
                ];
                // Return any groupings with current grouping ID as parent
                $childArray = array_filter($allGroupings, function($child) use($grouping) {
                    return $child->getParentGrouping() == $grouping->id();
                });
                if (count($childArray) > 0) {
                    // Handle multidimensional hierarchies by saving/retrieving previous state
                    $prevNode = $childNode;
                    $childNode = true;
                    $childCount = count($childArray);
                    $childCount--;
                    $jstreeNodes[$key]['children'] = $iterate($childArray);
                    $childNode = $prevNode;
                } elseif ($childCount >= 1) {
                    // Keep $childNode the same if iterating 'sibling'
                    $childCount--;
                    continue;
                } else {
                    $childNode = false;
                }
            }
            return array_values($jstreeNodes);
        };

        return $iterate($allGroupings);
    }

    public function itemSetCount($currentGrouping, $allGroupings)
    {
        $view = $this->getView();

        $itemSetArray = $this->getChildItemsets($currentGrouping, $allGroupings);
        $itemCount = 0;
        foreach ($itemSetArray as $itemSet) {
            $itemCount += $itemSet->itemCount();
        }

        if ($itemCount > 1) {
            return ' (' . $itemCount . ' items)';
        } else {
            return ' (' . $itemCount . ' item)';
        }
    }

    public function getChildItemsets($currentGrouping, $allGroupings)
    {
        $view = $this->getView();

        // Gather all 'child' itemSets if hierarchy_group_resources checked in config
        if ($view->setting('hierarchy_group_resources')) {
            $iterate = function ($currentGrouping) use ($view, $allGroupings, &$iterate, &$itemSetArray) {
                $itemSet = $currentGrouping->getItemSet() ? $view->api()->read('item_sets', $currentGrouping->getItemSet()->id())->getContent() : '';
                if ($itemSet) {
                    $itemSetArray[] = $itemSet;
                }
                // Return any groupings with current grouping as parent
                $childArray = array_filter($allGroupings, function($child) use($currentGrouping) {
                    return $child->getParentGrouping() == $currentGrouping->id();
                });
                foreach ($childArray as $childGrouping) {
                    $iterate($childGrouping);
                }
            };
            $iterate($currentGrouping);
        } else {
            $itemSetArray[] = $currentGrouping->getItemSet() ? $view->api()->read('item_sets', $currentGrouping->getItemSet()->id())->getContent() : '';
        }

        // Remove duplicate item sets
        $itemSetArray = array_unique($itemSetArray, SORT_REGULAR);

        return $itemSetArray;
    }
}
