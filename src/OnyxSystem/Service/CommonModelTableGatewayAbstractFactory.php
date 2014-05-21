<?php
namespace OnyxSystem\Service;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

/**
 * Description of CommonModelTableAbstractFactory
 *
 * @author pheadington
 * 
 * This abstract Factory creates services for models in the main application without having to manually add them
 */

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
 
class CommonModelTableGatewayAbstractFactory implements AbstractFactoryInterface
{
    public function canCreateServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {
        return (substr($requestedName, -12) === 'TableGateway');
    }
 
    public function createServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {
        $className = 'Application\\Model\\'.substr($requestedName, 0, -12);
        $tablename = strtolower(preg_replace('/(?<!^)([A-Z])/', '_\\1', substr($requestedName, 0, -12)));
        $dbAdapter = $locator->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new $className());
        return new TableGateway($tablename, $dbAdapter, null, $resultSetPrototype);
    }
}

