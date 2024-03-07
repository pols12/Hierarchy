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
    }

    public function setGlobalSettings($globalSettings)
    {
        $this->globalSettings = $globalSettings;
    }
}
