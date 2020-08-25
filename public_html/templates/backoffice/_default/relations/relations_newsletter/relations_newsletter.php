<?php
class Relations_newsletter{
    function  run($params){    
    
        if($params['del']){
            Newsletter::deleteById($params['del']);
            redirect('/relations/newsletter.html');
        }
        if(!isset($_SESSION['current_page_newsletter']))
            $_SESSION['current_page_newsletter'] = 1;
        else if(isset($params['current_page']))
            $_SESSION['current_page_newsletter'] = $params['current_page'];
        
        $params['items'] = Newsletter::find($_SESSION['current_page_newsletter'],$params['query']);
        
        // Bij delete van laatste item van de huidige pagina.
        if(count($params['items']['data'])==0 && $_SESSION['current_page_newsletter']>1){
            $_SESSION['current_page_newsletter'] = $_SESSION['current_page_newsletter']-1;
            redirect('/relations/newsletter.html');
        }
        if($params['ajaxresult']){
            print $params['relations_tbl'];
            exit(); 
        }
        $params['subscribers']  = parse('subscribers',$params,__FILE__);
        $params['content']      = parse('relations_newsletter',$params,__FILE__);
        return $params;
    }
}