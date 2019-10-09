<?php

function spec_get(string $name): ?SimpleXMLElement {
    try{
        $specFile = 'spec/'.strtolower($name).'.xml';
        if(!file_exists($specFile)){
            return null;
        }
        
        return simplexml_load_file($specFile);
    } catch (Exception $ex) {
        throw $ex;
    }
}