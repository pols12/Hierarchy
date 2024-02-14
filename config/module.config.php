<?php
return [
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => OMEKA_PATH . '/modules/Hierarchy/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'api_adapters' => [
        'invokables' => [
            'hierarchy' => 'Hierarchy\Api\Adapter\HierarchyAdapter',
            'hierarchy_grouping' => 'Hierarchy\Api\Adapter\HierarchyGroupingAdapter',
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Hierarchy\Controller\Index' => 'Hierarchy\Controller\IndexController',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            OMEKA_PATH . '/modules/Hierarchy/view',
        ],
    ],
    'block_layouts' => [
        'invokables' => [
            'Hierarchy' => Hierarchy\Site\BlockLayout\Hierarchy::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'hierarchyHelper' => Hierarchy\View\Helper\HierarchyHelper::class,
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            OMEKA_PATH . '/modules/Hierarchy/src/Entity',
        ],
        'proxy_paths' => [
            OMEKA_PATH . '/modules/Hierarchy/data/doctrine-proxies',
        ],
    ],
    'form_elements' => [
        'factories' => [
            'Hierarchy\Form\ConfigForm' => 'Hierarchy\Service\Form\ConfigFormFactory',
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'hierarchy' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/hierarchy',
                            'defaults' => [
                                '__NAMESPACE__' => 'Hierarchy\Controller',
                                'controller' => 'Index',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'action' => [
                                'type' => \Laminas\Router\Http\Segment::class,
                                'options' => [
                                    'route' => '[/:action]',
                                    'constraints' => [
                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Hierarchy', // @translate
                'route' => 'admin/hierarchy',
                'resource' => 'Hierarchy\Controller\Index',
            ],
        ],
    ],
];
