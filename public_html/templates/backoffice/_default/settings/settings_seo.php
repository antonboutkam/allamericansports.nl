<?php
class Settings_seo{
    function  run($params){                  
        if($params['_do']=='store'){
            store('article_generator',array('id'=>$params['id']),$params['article_generator']);
            redirect($params['root'].'/settings/seo.html');
        }            
        $params['sections']      =  Seo::getArticleGeneratorSections();
        $params['product_types'] =  ProductTypeDao::getAll();
                         
        if($params['id'])
           $params['article_generator'] =  Seo::getParagraphById($params['id']);            

        $params['articles'] = Seo::getAllParagraphs();
        
        return $params;
    }

}