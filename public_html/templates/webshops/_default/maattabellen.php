<?php
class Maattabellen{
    public static function run($params){
        $params               = Webshop::doFirst($params);
        $params['sizetables'] = Sizetables::getAll();
        return $params;
    }
}