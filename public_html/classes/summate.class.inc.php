<?php
class Summate{
    public static function send($params){
        require("Mail.php");
        require("AttachmentMail.php");
        require("Multipart.php");
        
        $params['outfile'] = './tmp/bill'.User::getId().'000.pdf';
        Billgen::run($params);
        
        $mail = new AttachmentMail($params['to'],$params['subject'], "Rama Takshak",$params['from']);                        
        $mail->addAttachment(new Multipart($params['outfile'],'attachment','application/pdf'));        
        
        $params['content'] = '<center><img src="http://backoffice.blueturban.nl/img/the-blue-turban.gif" /></center>'.$params['content'];
        $mail->setBodyHtml($params['content']);
        $mail->setPriority(AbstractMail::HIGH_PRIORITY);
        
        if ($mail->send())
        	$out['result'] = 'success';
        else
        	$out['result'] = 'fail';
          
        exit(json_encode($out));         
    }
    
     
}