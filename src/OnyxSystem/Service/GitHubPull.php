<?php

/*
 * The MIT License
 *
 * Copyright 2014 pheadington.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace OnyxSystem\Service;


use Zend\Http\Client;

/**
 * Description of GitHubPull
 *
 * @author pheadington
 */
class GitHubPull {
    
    protected $username = 'paul-headington';
    
    
    public function getRepoList($username){
        if($username != ''){
            $this->username = $username;
        }
        $uri = "https://api.github.com/users/" . $this->username . "/repos";        
        return $this->runCurl($uri);
    }   
    
    
    protected function runPostCurl($url, $config = null , $postData) {
        $data = json_decode($postData['data']);

        if ($config == NULL) {
            $config = array(
                'adapter'   => 'Zend\Http\Client\Adapter\Curl',
                'curloptions' => array(CURLOPT_FOLLOWLOCATION => true),
            );
        }
        
        try {
          $client = new Client($url, $config);
            foreach ($postData as $key => $value) {
                $client->setParameterPost($key, $value);
            }
          
            $response = $client->request(Client::POST);
            
            $result = json_decode($response->getBody());

            if ($result != NULL) {
              return $result;
            } else {
              return false;
          }          
        } catch (Exception $ex) {
            \Zend\Debug\Debug::dump($ex->getMessage());
            \Zend\Debug\Debug::dump($url);
        }
    }
            
    private function runCurl($url, $config = NULL) {
        if ($config == NULL) {
            $config = array(
                'adapter'   => 'Zend\Http\Client\Adapter\Curl',
                'curloptions' => array(CURLOPT_FOLLOWLOCATION => true),
            );
        }
        try {           
            $client = new Client($url, $config);
            $response = $client->send(); 
                        
            $result = json_decode($response->getBody());
                                   
            if ($result != NULL) {
                return $result;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            \Zend\Debug\Debug::dump($ex->getMessage());
            \Zend\Debug\Debug::dump($url);
        }
    }
}
