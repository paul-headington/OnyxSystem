<?php


namespace OnyxSystem\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use OnyxSystem\Service;
use Zend\Session\Container;

class SystemController extends AbstractActionController
{
    private $moduleName;
    
    public function __construct(){
        $container = new Container('systemBuild');
        if($container->moduleName == NULL){
            $container->moduleName = 'Application';
            $this->moduleName = $container->moduleName;
        }
    }
    
    public function testAction(){
        echo "Test";
        exit();
    }


    public function indexAction()
    {
        $return = array();
        $container = new Container('systemBuild');
        if($this->getRequest()->isPost()){
            $container->moduleName = ucfirst(strtolower($this->getRequest()->getPost("module")));
        }
        $this->moduleName = $container->moduleName;
        $return["moduleName"] = $container->moduleName;
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $return['messages'] = $flashMessenger->getMessages();
        }
        
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $metadata = new \Zend\Db\Metadata\Metadata($dbAdapter);
        $data = $metadata->getTableNames();
        $tableNames = array();
        foreach($data as $tableName){
            $tableNames[] = array(
                "name" => $tableName,
                "exists" => $this->checkExists($tableName),
            );            
        }
        $return["tables"] = $tableNames;        
        return new ViewModel($return);
    }
    
    public function createModelAction(){
        $sm = $this->getServiceLocator();
        $modelGen = new Service\ModelGenerator($sm);
        $table = $this->params('table');
        if($modelGen->createModel($table)){
            $this->flashMessenger()->addMessage('Model files created successfully');            
        }else{
            $this->flashMessenger()->addMessage('Error creating model files');
        }
        return $this->redirect()->toRoute('system');
        
    }
    
    private function checkExists($tableName){
        $basepath = realpath($_SERVER['DOCUMENT_ROOT'] . '/../');
        $path = $basepath.'/module/'.$this->moduleName.'/src/'.$this->moduleName.'/Model';
        $filename = '/' . ucfirst($tableName) . '.php';
        $filename2 = '/' . ucfirst($tableName) . 'Table.php';
        
        if (file_exists($path.$filename) === false) {
            return false;
        }
        if (file_exists($path.$filename2) === false) {
            return false;
        }
        return true;
    }
    
  
}
