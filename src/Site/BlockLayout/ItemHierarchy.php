<?php
namespace ItemHierarchy\Site\BlockLayout;

use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\Form\Element\Select;
use Laminas\View\Renderer\PhpRenderer;

class ItemHierarchy extends AbstractBlockLayout
{

	public function getLabel() {
		return 'Item Hierarchy'; // @translate
	}

	public function form(PhpRenderer $view, SiteRepresentation $site,
        SitePageRepresentation $page = null, SitePageBlockRepresentation $block = null
    ) {
        $hierarchies = $view->api()->search('item_hierarchy', ['sort_by' => 'position'])->getContent();
		
		$options = [];
		foreach ($hierarchies as $hierarchy) {
            $options[$hierarchy->id()] = $hierarchy->getLabel();
        }

        $setHierarchy = $block ? $block->dataValue('itemHierarchy') : '';

        $select = new Select('o:block[__blockIndex__][o:data][itemHierarchy]');
        $select->setValueOptions($options)->setValue($setHierarchy);

        $html = '<div class="field">';
        $html .= '<div class="field-meta"><label>' . $view->translate('Hierarchy') . '</label></div>';
        $html .= '<div class="inputs">' . $view->formSelect($select) . '</div>';
        $html .= '</div>';
        return $html;
    }

	public function render(PhpRenderer $view, SitePageBlockRepresentation $block)
	{	
        $hierarchy = $view->api()->searchOne('item_hierarchy', ['id' => $block->dataValue('itemHierarchy')])->getContent();
		if (!$hierarchy) {
            return '';
        }

		$hierarchyData = $view->hierarchyHelper()->toJstree($hierarchy);

        return $view->partial('item-hierarchy/common/block-layout/hierarchy-public', [
            'hierarchyData' => $hierarchyData,
        ]);
	}
}
