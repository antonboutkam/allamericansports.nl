<?php
class Relations_mailing{
    function  run($params){
        $mail_templates = glob('./templates/mailings/*');
        foreach($mail_templates as $template)
            $params['mail_templates'][] = basename($template);

        if($params['_do']=='store_preview_data')
            $_SESSION['preview_data'] = $params;
        if($params['_do']=='preview'){            
            print self::parseMailingHtml($_SESSION['preview_data'],$_SESSION['mailing_products'],array('cp_name'=>'Paul B. Singelsma','custom'=>'Laptopcentrale.nl'));
            exit();
        }
        if($params['_do']=='add')
            self::storeTestAddress($params['address']);
        if($params['_do']=='delete')
            self::removeTestAddress($params['id']);
        if($params['_do']=='send')
            $params = self::sendMailing($params);
        if($params['_do']=='add_product'){
            $props = ProductDao::getById($params['product_id']);
            $notebookProps = NotebookDao::get($params['product_id']);            
            if(is_array($notebookProps))
                $props = array_merge($notebookProps,$props);
            $params['multi_img'] = Cfg::isModuleActive('multi_img');
            if($props['photo'])
                $props['photo'] = $params['root'].'/img/upload/200x200_'.$props['id'].'.jpg';
            /*
            if($params['multi_img']){
                $extra_images = Image::getExtraImages($params['product_id']);
                if($extra_images[0]['filename'])
                    $props['photo'] = $params['root'].'/img/product/200x200_'.$extra_images[0]['filename'];
            }
             * 
             */
            $props['preview'] = parse('inc/mailing_products',array_merge($params,$props));
            $_SESSION['mailing_products'][] = $props;
            exit(json_encode($props));
        }
        if($params['_do']=='remove_product')
            foreach($_SESSION['mailing_products'] as $id=>$product)
                if($product['id']==$params['remove_id'])
                    unset($_SESSION['mailing_products'][$id]);
               
        if(isset($_SESSION['mailing_products']) && is_array($_SESSION['mailing_products']))
            foreach($_SESSION['mailing_products'] as $props){
                $props['preview'] = parse('inc/mailing_products',array_merge($params,$props));
                $params['mailing_images'] .= $props['preview'];
            }

        $params['addresses']        = self::getAddresses();
        $params['test_addresses']   = parse('inc/test_addresses',$params);
        
        if($params['ajax'])
            exit(json_encode($params));
        return $params;
    }
    private static function getAddresses($extraSql=''){
        return fetchArray($sql = 'SELECT * FROM `test_mailing` '.$extraSql,__METHOD__);
    }
    private static function storeTestAddress($address){
        parse_str($address,$dat);                
        DB::instance()->insert('test_mailing',$dat['test']);
    }
    private static function removeTestAddress($id){
        query(sprintf('DELETE FROM test_mailing WHERE id=%d',$id),__METHOD__);
    }
    
    private static function sendMailing($params){        
        $params['items_per_round']  = 5;
        $limit = sprintf("LIMIT %d, %d",($params['items_per_round']*$params['currentPage'])-$params['items_per_round'],$params['items_per_round']);
        $params['limit'] = $limit;
        if($params['mode']=='test')
            $addresses = self::getAddresses($limit);
        else{
            if($params['to_group']!='newsletter'){
                $where = '';
                if($params['to_group']!='')
                    $where =  sprintf('WHERE r.type = "%s"',$params['to_group']);
                $addresses = fetchArray($sql = sprintf('SELECT r.*,r.website custom, CONCAT(cp_firstname," ",cp_lastname) cp_name FROM relations r %s %s',$where,$limit),__METHOD__);
            }else{
                $addresses = fetchArray($sql = sprintf('SELECT n.name cp_name, n.email, n.custom FROM newsletter n %s',$limit),__METHOD__);                
            }     
        }
        
        foreach($addresses as $address){
            $toMail     = $_SERVER['IS_DEVEL']?'antonboutkam@gmail.com':$address['email'];
            // $toMail		= 'gerard@e-dentify.nl';
            $content    = self::parseMailingHtml($params,$_SESSION['mailing_products'],$address);
            $title      = self::parseTitleHtml($params,$_SESSION['mailing_products'],$address);
            Mailer::sendMail(Webshop::getWebshopSetting($address['custom'], 'mailing_email'), $toMail,$address['cp_name'],$title,$content);
        }
        if(count($addresses)==0)
            $params['done'] = 1;        
        return $params;
    }
    private static function parseTitleHtml($params,$products,$address){
        $title    = str_replace(array('@[',']@'),array('%[',']%'),$params['title']);
        return Template::parseStatic($title, array_merge($params,$address));
    }
    private static function parseMailingHtml($params,$products,$address=array()){

        $params['title']    = self::parseTitleHtml($params,$products,$address);
        $params['content']  = str_replace(array('@[',']@'),array('%[',']%'),$params['content']);
        $params['content']  = Template::parseStatic($params['content'], array_merge($params,$address));

        $tmp = explode(".",$params['template']);
        $template = '../mailings/'.$tmp[0];
        $params['products'] = $products;
        return parse('../../mailings/'.$tmp[0],array_merge($params,$address));
    }
}