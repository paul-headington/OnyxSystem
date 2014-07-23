<?php


namespace OnyxSystem\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use OnyxSystem\Service;
use OnyxSystem\Model\AclRole;
use OnyxSystem\Model\AclResource;
use OnyxRest\Model\RestResource;
use Zend\Session\Container;

class SystemController extends AbstractActionController
{
    private $moduleName;
    private $aclResourceTable;
    private $aclRoleTable;
    private $restResourceTable;
    protected $eventIdentifier = 'Onyx\Service\EventManger';
    
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
        // trigger MyEvent
//        $this->getEventManager()->trigger('sendMessage', null, array(
//            "to" => array('paul.headington@colensobbdo.co.nz', 'Paul'),
//            "subject" => "test subject",
//            "body" => "<h1>test</h1><br/><br/><p>message</p>",
//            ));
        
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
    
    public function restAction(){
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $metadata = new \Zend\Db\Metadata\Metadata($dbAdapter);
        $data = $metadata->getTableNames();   
        $tableNames = array();
        foreach($data as $tableName){ 
            //create default factory name
            $factoryDefault = '';
            $parts = explode("_", $tableName);
            foreach ($parts as $part){
                $factoryDefault .= ucfirst($part);
            }
            $modelFactoryDefault = $factoryDefault;
            $factoryDefault .= "Table";
            
            $tableNames[$tableName] = array(
                "tablename" => $tableName,
                "name" => $tableName,
                "factory" => $factoryDefault,
                "modelfactory" => $modelFactoryDefault,
                "checked" => '',
                "auth" => '',
                'custom' => false,
                "id" => null,
            );            
        }
        
        $dbRecords = $this->getRestResource();
        
        foreach($dbRecords as $row){
            $authChecked = '';
            if((bool)$row->auth){
                $authChecked = 'checked';
            }
            if(array_key_exists($row->tablename, $tableNames)){
                $tableNames[$row->tablename]['name'] = $row->name;
                $tableNames[$row->tablename]['factory'] = $row->factory;
                $tableNames[$row->tablename]['modelfactory'] = $row->modelfactory;
                $tableNames[$row->tablename]['checked'] = 'checked';
                $tableNames[$row->tablename]['auth'] = $authChecked;                
            }else{
                $tableNames[$row->tablename] = array(
                    "tablename" => $row->tablename,
                    "name" => $row->name,
                    "factory" => $row->factory,
                    "modelfactory" => $row->modelfactory,
                    "checked" => 'checked',
                    "auth" => $authChecked,
                    "custom" => true,
                    "id" => $row->id,
                ); 
            }
        }
        
        // posted data function
        if($this->getRequest()->isPost()){
            
            $data = $this->getRequest()->getPost();
            $RestResourceTable = $this->getRestResourceTable();
            
            foreach($data['allow'] as $key => $state){
                //add new record
                $authChecked = false;
                if(isset($data['auth'][$key])){
                    $authChecked = true;
                }
                $resource = new RestResource();
                $resource->tablename = $key;
                $resource->name = $data['name'][$key];
                $resource->factory = $data['factory'][$key];
                $resource->modelfactory = $data['modelfactory'][$key];
                $resource->auth = $authChecked;
                $RestResourceTable->save($resource);                
            }
            //cleanup
            foreach($dbRecords as $item){
                $RestResourceTable->delete($item->id);
            }
                
            $this->flashMessenger()->addMessage('Rest Resource data updated');           
            return $this->redirect()->toRoute('rest-api');
        }
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $return['messages'] = $flashMessenger->getMessages();
        }
        
        $return["tables"] = $tableNames;        
        return new ViewModel($return);
    }
    
    public function restaddAction(){
        if($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            $RestResourceTable = $this->getRestResourceTable();
            $resource = new RestResource();
            $resource->tablename = $data['name'] . '_' .  substr(md5(time()), 0,3);
            $resource->name = $data['name'];
            $resource->factory = $data['factory'];
            $resource->modelfactory = $data['modelfactory'];
            $resource->auth = false;
            $RestResourceTable->save($resource);  
            $this->flashMessenger()->addMessage('Resource data updated');  
        }
        
        return $this->redirect()->toRoute('rest-api');
    }
    
    public function restdeleteAction(){
        $id = $this->params()->fromRoute('id');
        if($id){
            $RestResourceTable = $this->getRestResourceTable();
            $RestResourceTable->delete($id);
            $this->flashMessenger()->addMessage('Resource data delete');             
        }            
        return $this->redirect()->toRoute('rest-api');
    }

    public function aclAction(){
        $routes = array();
        $roles = array();
        
        $sm = $this->getServiceLocator();    
        $dataMap = $this->getRoleResourceMap();
        
        $aclRoleTable = $this->getAclRoleTable();
        $aclRoleTableData = $aclRoleTable->fetchAllWithInheritance();
        foreach($aclRoleTableData as $role){
            $roles[$role->id] = $role->name;
        
        }
        $config = $sm->get('config');
        
        //\Zend\Debug\Debug::dump($config['router']['routes']['home']['child_routes']);
        //exit();
        
        foreach($config['router']['routes'] as $key => $data){
            $routes[] = $key;
            if(isset($data['child_routes'])){
                foreach($data['child_routes'] as $childKey => $childData){
                    $routes[] = $key . '/' . $childKey;
                }            
            }
            
        }
        
        $return = array('routes' => $routes, 'roles' => $roles, 'data' => $dataMap);
        
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $return['messages'] = $flashMessenger->getMessages();
        }
        return new ViewModel($return);
    }
    
    public function aclResourceAction(){
        if($this->getRequest()->isPost()){
            $dbRecords = $this->getRoleResourceMap();
            $data = $this->getRequest()->getPost();
            if(count($data['route'])> 0){
                $aclResourceTable = $this->getAclResourceTable();
                foreach($data['route'] as $rule){
                    if(!array_key_exists($rule, $dbRecords)){
                        $resource = new AclResource();
                        $data = explode('_', $rule);
                        $resource->roleid = $data[1]; 
                        $resource->route = $data[0];                        
                        $aclResourceTable->save($resource);
                    }else{
                        unset($dbRecords[$rule]);
                    }
                };                
                //cleanup
                foreach($dbRecords as $item){
                    $aclResourceTable->delete($item->id);
                }
                
                $this->flashMessenger()->addMessage('Resource data updated');           
            }
        }
        
        return $this->redirect()->toRoute('acl');
    }

    public function aclRoleAction(){
        if($this->getRequest()->isPost()){
            $newRole = strtolower($this->getRequest()->getPost("newrole"));
            if($newRole){
                $role = new AclRole();
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
        $output = array();        
        $aclResourceTable = $this->getAclResourceTable();
        $aclResourceTableData = $aclResourceTable->fetchAll();
        foreach($aclResourceTableData as $resource){
            $output[$resource->route . "_" . $resource->roleid] = $resource;
        }
        return $output;
    }
    
    private function getRestResource(){
        $output = array();        
        $restResourceTable = $this->getRestResourceTable();
        $data = $restResourceTable->fetchAll();
        foreach($data as $resource){
            $output[$resource->tablename] = $resource;
        }
        return $output;
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
    
    private function getRestResourceTable(){
        if (!$this->restResourceTable) {
            $sm = $this->getServiceLocator();
            $this->restResourceTable = $sm->get('RestResourceTable');
        }
        return $this->restResourceTable;
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
