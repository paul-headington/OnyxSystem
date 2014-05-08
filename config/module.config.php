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
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/system',
                    'defaults' => array(
                        '__NAMESPACE__' => 'OnyxSystem\Controller',
                        'controller'    => 'system',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:controller[/:action][/:id]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'         => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            'acl' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/system/acl',
                    'defaults' => array(
                        '__NAMESPACE__' => 'OnyxSystem\Controller',
                        'controller'    => 'system',
                        'action'        => 'acl',
                    ),
                ),
            ),
            'aclrole' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/system/aclRole',
                    'defaults' => array(
                        '__NAMESPACE__' => 'OnyxSystem\Controller',
                        'controller'    => 'system',
                        'action'        => 'aclRole',
                    ),
                ),
            ),
            'aclresource' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/system/aclResource',
                    'defaults' => array(
                        '__NAMESPACE__' => 'OnyxSystem\Controller',
                        'controller'    => 'system',
                        'action'        => 'aclResource',
                    ),
                ),
            ),
            'createmodel' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/system/create-model[/:table]',
                    'constraints' => array(
                        'table' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'OnyxSystem\Controller',
                        'controller'    => 'system',
                        'action'        => 'createModel',
                        'table'         => null
                    ),
                ),
            ),
            'createform' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/system/create-form[/:table]',
                    'constraints' => array(
                        'table' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'OnyxSystem\Controller',
                        'controller'    => 'system',
                        'action'        => 'createForm',
                        'table'         => null
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