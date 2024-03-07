<?php
namespace Hierarchy\Site\BlockLayout;

use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\Form\Element\Select;
use Laminas\View\Renderer\PhpRenderer;

class Hierarchy extends AbstractBlockLayout
{

	public function getLabel() {
		return 'Hierarchy'; // @translate
	}

	public function form(PhpRenderer $view, SiteRepresentation $site,
        SitePageRepresentation $page = null, SitePageBlockRepresentation $block = null
    ) {
        $hierarchies = $view->api()->search('hierarchy', ['sort_by' => 'position'])->getContent();
		
		$options = [];
		foreach ($hierarchies as $hierarchy) {
            $options[$hierarchy->id()] = $hierarchy->getLabel();
        }

        $setHierarchy = $block ? $block->dataValue('Hierarchy') : '';

        $select = new Select('o:block[__blockIndex__][o:data][Hierarchy]');
        $select->setValueOptions($options)->setValue($setHierarchy);

        $html = '<div class="field">';
        $html .= '<div class="field-meta"><label>' . $view->translate('Hierarchy') . '</label></div>';
        $html .= '<div class="inputs">' . $view->formSelect($select) . '</div>';
        $html .= '</div>';
        return $html;
    }

	public function render(PhpRenderer $view, SitePageBlockRepresentation $block)
	{	
        $hierarchy = $view->api()->searchOne('hierarchy', ['id' => $block->dataValue('Hierarchy')])->getContent();
		if (!$hierarchy) {
            return '';
        }

		$hierarchyData = $view->hierarchyHelper()->toJstree($hierarchy);

        return $view->partial('hierarchy/common/block-layout/hierarchy-public', [
            'hierarchy' => $hierarchy,
			'hierarchyData' => $hierarchyData,
        ]);
	}
}
