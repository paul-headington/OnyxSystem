<?php
namespace OnyxSystem;

use OnyxSystem\Model\AclResource;
use OnyxSystem\Model\AclResourceTable;
use OnyxSystem\Model\AclRole;
use OnyxSystem\Model\AclRoleTable;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'AclResourceTable' =>  function($sm) {
                    $tableGateway = $sm->get('AclResourceTableGateway');
                    $table = new AclResourceTable($tableGateway);
                    return $table;
                },
                'AclResourceTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new AclResource());
                    return new TableGateway('acl_resource', $dbAdapter, null, $resultSetPrototype);
                },
                'AclRoleTable' =>  function($sm) {
                    $tableGateway = $sm->get('AclRoleTableGateway');
                    $table = new AclRoleTable($tableGateway);
                    return $table;
                },
                'AclRoleTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new AclRole());
                    return new TableGateway('acl_role', $dbAdapter, null, $resultSetPrototype);
                },       
            ),
            
        );
    }
    
}
