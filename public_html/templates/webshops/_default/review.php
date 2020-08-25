<?php
class Review{
    public static function run($params){        
        $params                     = Webshop::doFirst($params);
		if(isset($_GET['approve']) && $_GET['approve'] !=""){
			$params['action']='Approve';
			$params['review_id']=$_GET['approve'];	
		}
		if(isset($_GET['password']) && $_GET['password'] !=""){
			$params['review_pwd']=$_GET['password'];	
		}
		if($params['review_id'] !="" && $params['review_pwd']!=""){
			
			
			$params['relation_id'] = ReviewDao::getReviewBy($params['review_id']);
			if(ReviewDao::checkUser($params['relation_id'],$params['review_pwd'])){
				$params['error']=ReviewDao::approve($params['review_id']);
				
				if($params['error']){
					$params['review_msg'] ='VALIDATION ERROR'	;
				}else{
					$params['review_msg']='De review is goedgekeurd';	
				}
			}
		}
       
        $params['content']      = parse('review',$params);
        return $params;
    }
}
