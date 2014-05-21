<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OnyxSystem\Service;


/**
 * Description of CommonModelTableAbstractFactory
 *
 * @author pheadington
 */

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
 
class CommonModelTableAbstractFactory implements AbstractFactoryInterface
{
    public function canCreateServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {        
        return (substr($requestedName, -5) === 'Table');
    }
 
    public function createServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {
        $className = 'Application\\Model\\'.$requestedName;
        $tableGateway = $locator->get($requestedName.'Gateway');
        $table = new $className($tableGateway);
        return $table;
    }
}
