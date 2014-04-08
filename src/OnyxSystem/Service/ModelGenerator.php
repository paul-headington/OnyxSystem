<?php

/*
 * Copyright (c) 2011 , Paul Headington
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgement:
 *    This product includes software developed by the <organization>.
 * 4. Neither the name of the <organization> nor the
 *    names of its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY Paul Headington \'AS IS\' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Description of ModelGenerator
 *
 * @author paulh
 */


namespace OnyxSystem\Service;

use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Generator\FileGenerator;
use Zend\Session\Container;
use OnyxSystem\Form\ModelForm;

define('PHP_TAB', "\t");

class ModelGenerator {
    
    private $createDate;
    private $applicationName;
    private $author = 'Paul Headington';
    private $license;
    private $dbAdapter;
    private $moduleName;
    
    public function __construct($sm) {        
        $this->dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $this->createDate = date(DATE_RFC822);
        $this->applicationName = '';
        $this->license = 'Copyright (c) '.date('Y', time()).', '.$this->author.'All rights reserved.'.PHP_EOL.
                PHP_EOL.'Redistribution and use in source and binary forms, with or without'.PHP_EOL.'modification, are permitted provided that the following conditions are met:'.PHP_EOL.
                '1. Redistributions of source code must retain the above copyright'.PHP_EOL.
                'notice, this list of conditions and the following disclaimer.'.PHP_EOL.
                '2. Redistributions in binary form must reproduce the above copyright'.PHP_EOL.
                'notice, this list of conditions and the following disclaimer in the'.PHP_EOL.
                'documentation and/or other materials provided with the distribution.'.PHP_EOL.
                '3. All advertising materials mentioning features or use of this software'.PHP_EOL.
                'must display the following acknowledgement:'.PHP_EOL.
                'This product includes software developed by the <organization>.'.PHP_EOL.
                '4. Neither the name of the <organization> nor the'.PHP_EOL.
                'names of its contributors may be used to endorse or promote products'.PHP_EOL.
                'derived from this software without specific prior written permission.'.PHP_EOL.PHP_EOL.
                'THIS SOFTWARE IS PROVIDED BY '.$this->author.' \'AS IS\' AND ANY'.PHP_EOL.
                'EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED'.PHP_EOL.
                'WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE'.PHP_EOL.
                'DISCLAIMED. IN NO EVENT SHALL '.$this->author.' BE LIABLE FOR ANY'.PHP_EOL.
                'DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES'.PHP_EOL.
                '(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;'.PHP_EOL.
                'LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND'.PHP_EOL.
                'ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT'.PHP_EOL.
                '(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS'.PHP_EOL.
                'SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.'.PHP_EOL;
        $container = new Container('systemBuild');
        if($container->moduleName == NULL){
            $container->moduleName = 'Application';
        }
        $this->moduleName = $container->moduleName;
    }
    
    public function createForm($table){
        // need to remove underline first, ucwords, and then remove space
        $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
        $classname = $modelName . "Form";
        
        $basepath = realpath($_SERVER['DOCUMENT_ROOT'] . '/../');
        $formModelTemplate = $basepath . '/vendor/paul-headington/OnyxSystem/src/OnyxSystem/Templates/ModelForm.php';
        
        $generator = $this->loadBaseFromFile($formModelTemplate);        
        $class = $generator->getClass();        
        $class->setName($classname);
        $class->setNamespaceName($modelName . '\Form');
        $constuct = $class->getMethod('__construct');
        $body = $constuct->getBody();
        $body = str_replace("{Model}", $modelName, $body);
        $body = str_replace("Model", $modelName, $body);
        $body = str_replace("OnyxSystem", $this->moduleName, $body);
        $constuct->setBody($body);
        $class_code = $class->generate();
         try{
            $this->saveFile($class_code, $classname, $this->moduleName, 'Form');
        }catch(Exception $e){
            return false;
        }
        // now to generate the fieldset
        
        $classname = $modelName . "Fieldset";
        
        $fieldsetModelTemplate = $basepath . '/vendor/paul-headington/OnyxSystem/src/OnyxSystem/Templates/ModelFieldset.php';
        
        $generator = $this->loadBaseFromFile($fieldsetModelTemplate);        
        $class = $generator->getClass();        
        $class->setName($classname);
        $class->setNamespaceName($modelName . '\Form');
        $class->addUse($this->moduleName . "\Model\\" . $modelName);
        $constuct = $class->getMethod('__construct');
        $body = $constuct->getBody();
        $body = str_replace("{Model}", $modelName, $body);
        
        //        $this->add(array(
//            'name' => 'firstname',
//            'options' => array(
//                'label' => 'First Name'
//            ),
//            'attributes' => array(
//                'required' => 'required'
//            )
//        ));
        
        
        $constuct->setBody($body);
        $class_code = $class->generate();
         try{
            $this->saveFile($class_code, $classname, $this->moduleName, 'Form');
        }catch(Exception $e){
            return false;
        }
        
        
        return true;
    }

    public function createModel($table){

        // need to remove underline first, ucwords, and then remove space
        $classname = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));

        // get all fields
        $metadata = new \Zend\Db\Metadata\Metadata($this->dbAdapter);
        $fields = $metadata->getColumns($table);
        
        // want to track primary ids for table
        $primary = array();

        // add to columns each field with a default value
        $columns = array();

        // getters and setters in here
        $getset = array();

        $required = array();
        
        // track primary field(s) for table
        $constraints  = $metadata->getTable($table)->getConstraints();

        foreach ($constraints AS $constraint) {
            if ($constraint->isPrimaryKey()) {
                $primary = $constraint->getColumns();
            }
        }

        // init empty body strings
        $exchangebody = '';
        $saveBody = '';
        
        foreach($fields as $field)
        {
            switch($field->getDataType()){
                case "int":
                    $type = 'int';
                    $dvalue = 0;
                    $validator = array(
                        array(
                            'name' => 'not_empty',
                        ),
                        array(
                            'name' => 'string_length',
                            'options' => array(
                                'min' => 1
                            ),
                        ));
                    break;
                case "varchar":
                case "text":
                case "blob":
                    $type = 'string';
                    $dvalue = NULL;
                    $validator = array(
                        array(
                            'name' => 'not_empty',
                        ),
                        array(
                            'name' => 'string_length',
                            'options' => array(
                                'min' => 3
                            ),
                        ));
                    break;
                default:
                    $type = 'other';
                    $dvalue = NULL;
                    $validator = array();
                    break;
            }
            
            
            if($field->getName() == 'id'){
                $dvalue = null;
            }
            // if int field default to 0
            $columns[] = array(
                'name' => '_'.$field->getName(),
                'defaultValue' => $dvalue,
                'visibility' => PropertyGenerator::FLAG_PROTECTED,                
            );

            //$getset[] = $this->makeGetter($field->getName());
            //$getset[] = $this->makeSetter($field->getName(), $type);
            
            if($type == 'other'){ $type = '';}
            $required[$field->getName()] = array('required' => false, 'validator' => $validator);
            
           
            $fieldname = strtolower($field->getName());
            $exchangebody .= '$this->' . $fieldname . PHP_TAB . PHP_TAB . '= (isset($data["'.$fieldname.'"])) ? $data["'.$fieldname.'"] : null;'.PHP_EOL;
            $saveBody .= PHP_TAB . '\'' . $fieldname . '\' => $'.strtolower($classname).'->' . $fieldname . ',' . PHP_EOL;
            
            
        }
        $columns[] = array(
            'name' => 'filter',
            'defaultValue' => null,
            'visibility' => PropertyGenerator::FLAG_CONSTANT,            
        );

        $columns[] = array(
            'name' => 'validation',
            'defaultValue' => $required,
            'visibility' => PropertyGenerator::FLAG_PROTECTED,
            
        );        

        // configure docblock
        $docblock = DocBlockGenerator::fromArray(array(
                'shortDescription' => $classname . ' model',
                'longDescription'  => 'This is a class generated with Paul\'s Zend MVC Model Generator.',
                'tags' => array(
                        array(
                            'name' => 'author',
                            'description' => $this->author,
                        ),
                        array(
                            'name' => 'createdOn',
                            'desciption' => $this->createDate,
                        ),
                        array(
                            'name'        => 'license',
                            'description' => $this->license,
                        ),
                    )
            ));


        $methods_class = array(
            MethodGenerator::fromArray(array(
                'name'       => '__construct',
                'parameters' => array(),
                //'body'       => '$this->filter = new Zend_Filter();'.PHP_EOL.'$this->filter->addFilter(new Zend_Filter_StripTags())'.PHP_EOL.PHP_TAB.'->addFilter(new Zend_Filter_StripNewlines())'.PHP_EOL.PHP_TAB.'->addFilter(new Zend_Filter_StringTrim());'.PHP_EOL,
                'body' => '',
                'docblock'   => DocBlockGenerator::fromArray(array(
                    'shortDescription' => 'build the model',
                    'longDescription'  => null,
                    'tags'             => array(),
                )),
            )),
            MethodGenerator::fromArray(array(
                'name'       => 'getValidation',
                'parameters' => array(),
                'body'       => 'return $this->validation;'.PHP_EOL,
                'docblock'   => DocBlockGenerator::fromArray(array(
                    'shortDescription' => 'Validation selector',
                    'longDescription'  => null,
                    'tags'             => array(),
                )),
            )),
            MethodGenerator::fromArray(array(
                'name'       => 'exchangeArray',
                'parameters' => array('data'),
                'body'       => $exchangebody,
                'docblock'   => DocBlockGenerator::fromArray(array(
                    'shortDescription' => 'set array data to object',
                    'longDescription'  => null,
                    'tags'             => array(),
                )),
            )),            
        );



        // create main class file
        $class = new OnyxClassGenerator();
        // set name and docblock
        $class->setName($classname)
              ->setNamespaceName($this->moduleName . '\Model')
              ->setDocblock($docblock)
              ->addTrait('\GetSet\SetterGetter')
              ->addProperties($columns)
              ->addMethods($methods_class);
        $class_code = $class->generate();
        
        try{
            $this->saveFile($class_code, $classname, $this->moduleName);
        }catch(Exception $e){
            return false;
        }

        // configure docblock
        $tabledocblock = DocBlockGenerator::fromArray(array(
                'shortDescription' => $classname . 'Table model',
                'longDescription'  => 'This is a class generated with Paul\'s Zend MVC Model Generator.',
                'tags' => array(
                        array(
                            'name' => 'author',
                            'description' => $this->author,
                        ),
                        array(
                            'name' => 'createdOn',
                            'desciption' => $this->createDate,
                        ),
                        array(
                            'name'        => 'license',
                            'description' => $this->license,
                        ),
                    )
            ));
        
        
        $tablemethods_class = array(
            MethodGenerator::fromArray(array(
                'name'       => '__construct',
                'parameters' => array(new \Zend\Code\Generator\ParameterGenerator('tableGateway', 'TableGateway')),
                'body'       => '$this->tableGateway = $tableGateway;',
                'docblock'   => DocBlockGenerator::fromArray(array(
                    'shortDescription' => 'build the model',
                    'longDescription'  => null,
                    'tags'             => array(),
                )),
            )),
            MethodGenerator::fromArray(array(
                'name'       => 'fetchAll',
                'parameters' => array(),
                'body'       => '$resultSet = $this->tableGateway->select();'.PHP_EOL.'return $resultSet;',
                'docblock'   => DocBlockGenerator::fromArray(array(
                    'shortDescription' => 'Return all data',
                    'longDescription'  => null,
                    'tags'             => array(),
                )),
            )),
            MethodGenerator::fromArray(array(
                'name'       => 'getById',
                'parameters' => array('id'),
                'body'       => '$id  = (int) $id;'.PHP_EOL.'$rowset = $this->tableGateway->select(array(\'id\' => $id));'.PHP_EOL.'$row = $rowset->current();'.PHP_EOL.'if (!$row) {'.PHP_EOL.PHP_TAB.'throw new \Exception("Could not find row $id");'.PHP_EOL.'}'.PHP_EOL.'return $row;',
                'docblock'   => DocBlockGenerator::fromArray(array(
                    'shortDescription' => 'retrieve object by id',
                    'longDescription'  => null,
                    'tags'             => array(
                        array(
                            'name' => 'id',
                            'description' => 'The primary key of the object',
                        ),
                    ),
                )),
            )), 
            MethodGenerator::fromArray(array(
                'name'       => 'save',
                'parameters' => array(new \Zend\Code\Generator\ParameterGenerator(strtolower($classname), $classname)),
                'body'       => '$data = array('.PHP_EOL.$saveBody.PHP_EOL.');'.PHP_EOL.'$id = (int)$'.strtolower($classname).'->id;'.PHP_EOL.'if ($id == 0) {'.PHP_EOL.PHP_TAB.'$data[\'postdate\'] = date(\'d-m-Y H:i:s\');'.PHP_EOL.PHP_TAB.'$this->tableGateway->insert($data);'.PHP_EOL.'} else {'.PHP_EOL.PHP_TAB.'if ($this->getById($id)) {'.PHP_EOL.PHP_TAB.PHP_TAB.'$this->tableGateway->update($data, array(\'id\' => $id));'.PHP_EOL.PHP_TAB.'} else {'.PHP_EOL.PHP_TAB.PHP_TAB.'throw new \Exception(\''.$classname.' id does not exist\');'.PHP_EOL.PHP_TAB.'}'.PHP_EOL.'}',                
                'docblock'   => DocBlockGenerator::fromArray(array(
                    'shortDescription' => 'retrieve object by id',
                    'longDescription'  => null,
                    'tags'             => array(
                        array(
                            'name' => 'id',
                            'description' => 'The primary key of the object',
                        ),
                    ),
                )),
            )), 
            MethodGenerator::fromArray(array(
                'name'       => 'delete',
                'parameters' => array('id'),
                'body'       => '$this->tableGateway->delete(array(\'id\' => $id));',
                'docblock'   => DocBlockGenerator::fromArray(array(
                    'shortDescription' => 'Delete onject by id',
                    'longDescription'  => null,
                    'tags'             => array(
                            array(
                                'name' => 'id',
                                'description' => "The primary key of the object",
                            ),
                    ),
                )),
            )),
        );


        // create table class file
        $classtable = new OnyxClassGenerator();
        // set name and docblock
        $classtable->setName($classname."Table")
              ->setNamespaceName($this->moduleName . '\Model')
              ->addUse('Zend\Db\TableGateway\TableGateway')
              ->setDocblock($tabledocblock)
              ->addProperties(array('name' => 'tableGateway', 'visibility' => PropertyGenerator::FLAG_PROTECTED))
              ->addMethods($tablemethods_class);
        $classtable_code = $classtable->generate();
                        
        try{
            $this->saveFile($classtable_code, $classname."Table", $this->moduleName);
        }catch(Exception $e){
            return false;
        }

        return true;
    }
    
    private function saveFile($code, $filename, $moduleName, $type = 'Model'){
        try{ 
            $basepath = realpath($_SERVER['DOCUMENT_ROOT'] . '/../');
            $path = $basepath . '/module/' . $moduleName . '/src/' . $moduleName . '/' . $type;

            if(!is_dir($path)){
                mkdir($path, 0775);
                chown($path, 'ubuntu');
            }
            //chmod($path, 0775); 
            
            $result = file_put_contents($path.'/'.$filename.'.php', "<?php" . PHP_EOL . $code . PHP_EOL . "?>");
            if($result === false){
                throw new \Exception("Could not find create file: " . $path . '/' . $filename . '.php');            
            }
        }  catch (Exception $e){
            throw new \Exception("Could not find create file: " . $path . '/' . $filename . '.php');            
        }
        
    }
    
    private function loadBaseFromFile($file){        
        $generator = FileGenerator::fromReflectedFileName($file);
        return $generator;
    }
    
    private function loadBaseFromClass($class){
        $generator = OnyxClassGenerator::fromReflection(
            new ClassReflection($class)
        );
        return $generator;
    }




}

?>
