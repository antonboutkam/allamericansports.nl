<?php
class LatestemailsWidget{
    function getDescription($lang){
        if($lang=='nl')
            return 'U kunt de bedrijfsinbox inzien met deze widget.';
        return 'You can check your companyinbox with this widget';        
    }
    
    function getDefaultWidth(){
        return 'full';
    }    
    function getAcceptedSizes(){
        return array('full','three_quarters','half','quarter');
    }           
    function getWidth(){        
        $width = BackofficeWidget::getCurrentWidgetWidth(__CLASS__);                
        if(empty($width))
            $width = $this->getDefaultWidth();
        return $width;
    }
    function isEnabled(){
            return Cfg::isModuleActive('imap_enabled');
    }	
    function getTitle($lang){
        if($lang=='nl')
            return 'Mail inbox';
        return 'Email inbox';        
    }
    function _do($params){
        if ($params['_do'] == 'deletemessage'){
            Imap::deleteMessage($params['id']);
        }
     return $params;                
    }
      
    function  getContents($params){   
        $mails          = Imap::getInbox(8);
        $mails          = array_reverse($mails);

        foreach ($mails as $i => $mail){                             
            $params['mail'][$i]['subject']                  = $mail->subject;  
            #$params['mail'][$i]['subject_full']             = substr($mail->subject,0,105).'...';
            
            if((strlen($mail->subject))>25){$params['mail'][$i]['subject_half'] = substr($mail->subject,0,25).'...'; }
                else {$params['mail'][$i]['subject_half'] = $mail->subject;}      
            if((strlen($mail->subject))>80){$params['mail'][$i]['subject_three_quarters'] = substr($mail->subject,0,80).'...'; }
                else {$params['mail'][$i]['subject_three_quarters'] = $mail->subject;} 
            if((strlen($mail->subject))>105){$params['mail'][$i]['subject_full'] = substr($mail->subject,0,105).'...'; }
                else {$params['mail'][$i]['subject_full'] = $mail->subject;} 
                
            #$params['mail'][$i]['subject_three_quarters']   = substr($mail->subject,0,80).'...';                   
            $params['mail'][$i]['date']                     = substr($mail->date,5);
            $params['mail'][$i]['textplain']                = $mail->textPlain;
            $params['mail'][$i]['fromName']                 = $mail->fromName;
            $params['mail'][$i]['subject']                  = $mail->subject;                 
            $params['mail'][$i]['fromAddress']              = $mail->fromAddress;
            $params['mail'][$i]['seen']                     = $mail->seen;
            $params['mail'][$i]['id']                       = $mail->id; 
        }
         $params['unreadmails']              = Imap::getNewEmailCount();

       
        $params['mailhostname']             = str_replace('backoffice.','',$params['hostname']);
        $params['mailhostname']             = str_replace('nuidev.','',$params['mailhostname']);
        $params['mailhostname']             = str_replace('nuicart.','',$params['mailhostname']);
        $params['current_view']             = $this->getWidth();   
        $params['widget_name']              = strtolower(__CLASS__);
        $params['widget_title']             = self::getTitle($params['lang']);

         return parse('widgets/latestemails/latestemails',$params);
    }
}