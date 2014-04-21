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
namespace OnyxSystem;
/**
 * Description of DataFunctions
 *
 * @author paulh
 */
class DataFunctions {
    
    static function jsonifyModelObject($obj){
        $arr = (array)$obj;
        //remove filter subclass and validation
        array_pop($arr);
        array_pop($arr);
        $formattedArr = array();
        foreach($arr as $key => $val){
            $newKey = substr($key, strpos($key, "_")+1);
            $formattedArr[$newKey] = $val;
        }
        //remove unsecure data from data models if it is set        
        if(isset($formattedArr['password'])){
            unset($formattedArr['password']);
        }
        if(isset($formattedArr['salt'])){
            unset($formattedArr['salt']);
        }
        return json_encode($formattedArr);
    }

    static function arrayifyModelObject($obj){
        $arr = (array)$obj;
        array_pop($arr);
        $formattedArr = array();
        foreach($arr as $key => $val){
            $newKey = substr($key, strpos($key, "_")+1);
            $formattedArr[$newKey] = $val;
        }
        return $formattedArr;
    }

    static function jsonifyObject($obj){
        $formattedArr = (array)$obj;
        return json_encode($formattedArr);
    }
    
    static function keygen($length=10)
    {
        $letters = array_merge(range('A','H'),range('K','N'),range('P','Z'));
        $numbers = range('2','9');
        $inputs = array_merge($letters, $numbers);
        
        $key = ''; 
        for($i=0; $i<$length; $i++)
        {
            $key .= $inputs{mt_rand(0,30)};
        }
        return $key;
    }
    
    static function getSalt(){
        $dynamicSalt = '';
        for ($i = 0; $i < 50; $i++) {
            $dynamicSalt .= chr(rand(33, 126));
        }
        return md5($dynamicSalt);
    }
}

?>
