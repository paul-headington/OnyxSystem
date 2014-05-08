<?php


namespace OnyxSystem\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use OnyxSystem\Service;
use Zend\Session\Container;

class SystemController extends AbstractActionController
{
    private $moduleName;
    private $aclResourceTable;
    private $aclRoleTable;
    
    public function onDispatch( \Zend\Mvc\MvcEvent $e ){
        $this->layout('layout/onyxsystem');
        return parent::onDispatch($e);
    }
    
    public function __construct(){
        $container = new Container('systemBuild');
        if($container->moduleName == NULL){
            $container->moduleName = 'Application';
            $this->moduleName = $container->moduleName;
        }
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
    
    public function aclAction(){
        $routes = array();
        $roles = array();
        
        $sm = $this->getServiceLocator();    
        
        $aclRoleTable = $this->getAclRoleTable();
        $aclRoleTableData = $aclRoleTable->fetchAll();
        foreach($aclRoleTableData as $role){
            $roles[$role->id] = $role->name;
        
        }
        $config = $sm->get('config');
        
        foreach($config['router']['routes'] as $key => $data){
            $routes[] = $key;
        }
        
        return new ViewModel(array('routes' => $routes, 'roles' => $roles));
    }
    
    public function aclResourceAction(){
        if($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            \Zend\Debug\Debug::dump($data);
            exit();
            if($newRole){
                $role = new \OnyxSystem\Model\AclRole();
                $role->name = $newRole;
                $aclRoleTable = $this->getAclRoleTable();
                $aclRoleTable->save($role);
            }
        }
        
        return $this->redirect()->toRoute('acl');
    }

    public function aclRoleAction(){
        if($this->getRequest()->isPost()){
            $newRole = strtolower($this->getRequest()->getPost("newrole"));
            if($newRole){
                $role = new \OnyxSystem\Model\AclRole();
                $role->name = $newRole;
                $aclRoleTable = $this->getAclRoleTable();
                $aclRoleTable->save($role);
            }
        }
        
        return $this->redirect()->toRoute('acl');
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
    
    public function createFormAction(){
        $sm = $this->getServiceLocator();
        $modelGen = new Service\ModelGenerator($sm);
        $table = $this->params('table');        
        if($modelGen->createForm($table)){
            $this->flashMessenger()->addMessage('Form files created successfully');            
        }else{
            $this->flashMessenger()->addMessage('Error creating form files');
        }
        return $this->redirect()->toRoute('system');
    }
    
    private function getRoleResourceMap(){
        $aclResourceTable = $this->getAclResourceTable();
        $aclResourceTableData = $aclResourceTable->fetchAll();
    }
    
    private function getAclResourceTable(){
        if (!$this->aclResourceTable) {
            $sm = $this->getServiceLocator();
            $this->aclResourceTable = $sm->get('AclResourceTable');
        }
        return $this->aclResourceTable;
    }
    
    private function getAclRoleTable(){
        if (!$this->aclRoleTable) {
            $sm = $this->getServiceLocator();
            $this->aclRoleTable = $sm->get('AclRoleTable');
        }
        return $this->aclRoleTable;
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
