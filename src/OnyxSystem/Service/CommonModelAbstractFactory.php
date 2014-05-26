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
 
class CommonModelAbstractFactory implements AbstractFactoryInterface
{
    public function canCreateServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {       
        $className = 'Application\\Model\\'.$requestedName;            
        return class_exists($className);
        
    }
 
    public function createServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {
        $className = 'Application\\Model\\'.$requestedName;
        $model = new $className();
        return $model;
    }
}
