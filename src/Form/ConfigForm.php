<?php
namespace Hierarchy\Form;

use Laminas\Form\Form;

class ConfigForm extends Form
{
    protected $globalSettings;
    
    public function init()
    {
        $this->add([
            'type' => 'checkbox',
            'name' => 'hierarchy_show_label',
            'options' => [
                        'label' => 'Show hierarchy label', // @translate
                        'info' => 'If checked, assigned label will display as hierarchy header on public pages.', // @translate
                    ],
            'attributes' => [
                'checked' => $this->globalSettings->get('hierarchy_show_label') ? 'checked' : '',
                'id' => 'show-label',
            ],
        ]);

        $this->add([
            'type' => 'checkbox',
            'name' => 'hierarchy_show_all_groupings',
            'options' => [
                        'label' => 'Show all hierarchy groupings', // @translate
                        'info' => 'If left unchecked, only direct ancestors & descendants of assigned grouping will display on resource pages.', // @translate
                    ],
            'attributes' => [
                'checked' => $this->globalSettings->get('hierarchy_show_all_groupings') ? 'checked' : '',
                'id' => 'show-all-groupings',
            ],
        ]);

        $this->add([
            'type' => 'checkbox',
            'name' => 'hierarchy_group_resources',
            'options' => [
                        'label' => 'Combine hierarchy resources', // @translate
                        'info' => 'If checked, groupings will display resources of all child groupings in resource counts and on hierarchy grouping browse pages.', // @translate
                    ],
            'attributes' => [
                'checked' => $this->globalSettings->get('hierarchy_group_resources') ? 'checked' : '',
                'id' => 'group-resources',
            ],
        ]);

        $this->add([
            'type' => 'checkbox',
            'name' => 'hierarchy_show_count',
            'options' => [
                        'label' => 'Show hierarchy resource counts', // @translate
                        'info' => 'If checked, hierarchy groupings will show # of resources within currently assigned itemSet.', // @translate
                    ],
            'attributes' => [
                'checked' => $this->globalSettings->get('hierarchy_show_count') ? 'checked' : '',
                'id' => 'show-count',
            ],
        ]);
    }

    public function setGlobalSettings($globalSettings)
    {
        $this->globalSettings = $globalSettings;
    }
}
