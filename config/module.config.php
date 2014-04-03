<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'OnyxSystem\Controller\System' => 'OnyxSystem\Controller\SystemController',
        ),
    ),
    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'system' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/system[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'OnyxSystem\Controller\System',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'createmodel' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '[/:table]',
                            'constraints' => array(
                                'table' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'action'     => 'createModel',
                                'table'      => null
                            ),
                        ),
                    ),
                ),
            ),
            
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'onyxsystem' => __DIR__ . '/../view',
        ),
    ),
);