<?php
require_once('libs/Zend/Mail/Message.php');  
require_once('libs/Zend/Mail/Part.php');
require_once('libs/Zend/Mail/Storage.php');
require_once('libs/Zend/Mail/Exception.php');
require_once('libs/Zend/Mime/Decode.php');
require_once('libs/Zend/Mime/Exception.php');
require_once('libs/Zend/Mime/Message.php');
require_once('libs/Zend/Mime/Part.php');
require_once('libs/Zend/Mail.php');
require_once('libs/Zend/Mail.php');
require_once('libs/Zend/Http/Client.php');

class MimeMailer extends Zend_Mail{
    
	/**
	* @param $email ...
	* @param $vars an array of vars that should be replaced in the template
	* @param $tag refers to a template + translation
	*/
	static function sendMail($from,$toMail,$toName,$subject,$html,$attachmentBinary=null,$attachementContentType='application/pdf',$attachementFilename='factuur.pdf'){	          
        $sqlTpl = 'INSERT INTO mail_log(tomail,frommail,subject,content,mailtime) VALUE("%s","%s","%s","%s",NOW())';               
        $sql    = sprintf($sqlTpl,quote($toMail),quote($from),quote($subject),quote($html));
        query($sql,__METHOD__);
                        
        #exit($html);
        if($_SERVER['IS_DEVEL'])
            $toMail = 'anton@nui-boutkam.nl';

        if(empty($from))
            $from  = 'info@allamericansports.nl';

try{                                            
        $mail = new MimeMailer();
		$mail->setBodyText(strip_tags($html));
		$mail->setBodyHtml($html);
		$mail->setFrom($from);
		$mail->addTo($toMail,$toName);
		if($attachmentBinary){
			$at = $mail->createAttachment($attachmentBinary);
			$at->type        = $attachementContentType;
			$at->encoding    = Zend_Mime::ENCODING_BASE64;
			$at->filename    = $attachementFilename;
		}
		$mail->setSubject($subject);
		$mail->send();
}catch(Exception $e)
{
// echo $e->getMessage();
// exit();
 //
}


	} 
	public function setBodyHtml($html, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE, $preload_images = true){
		if ($preload_images){
			$this->setType(Zend_Mime::MULTIPART_RELATED); 
			$dom = new DOMDocument(null, $this->getCharset());
			@$dom->loadHTML($html);
			$images = $dom->getElementsByTagName('img');
 
			for ($i = 0; $i < $images->length; $i++){
				$img = $images->item($i);
				$url = $img->getAttribute('src'); 
				$image_http = new Zend_Http_Client($url);
				$response = $image_http->request();
				if ($response->getStatus() == 200){
					$image_content = $response->getBody();
					$pathinfo = pathinfo($url);
					$mime_type = $response->getHeader('Content-Type');                    
                    if($mime_type=='text/html' && $_SERVER['IS_DEVEL']){
                        trigger_error('Het embedden van de afbeelding in het e-mail bericht gaat niet goed, waarschijnlijk een issue met de hosts file op de server '.__METHOD__,E_USER_WARNING);
                        exit();
                    }                     
					$mime = new Zend_Mime_Part($image_content);					
					$mime->id          = md5($url);
					$mime->location    = $url;
					$mime->type        = $mime_type;
					$mime->disposition = Zend_Mime::DISPOSITION_INLINE;
					$mime->encoding    = Zend_Mime::ENCODING_BASE64;
					$mime->filename    = $pathinfo['basename'];					
					$html = str_replace($url, 'cid:'.md5($url) ,$html);					
					$this->addAttachment($mime);
				}
			}
		}	
		return parent::setBodyHtml($html, $charset, $encoding);
	}       
}
