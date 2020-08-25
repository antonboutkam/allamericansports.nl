<?php
class BackofficeWidget{
	private static $widgetDir = './templates/backoffice/_default/widgets/';

    public static function setUserWidth($widgetName,$widgetSize){
        $widgetId = self::getWidgetIdByName($widgetName);
        // hier afmaken
        
       $sql = sprintf('UPDATE backoffice_user_widget 
                        SET `width`="%s" 
                        WHERE fk_user=%d 
                        AND fk_backoffice_widget=%d',$widgetSize,User::getId(),$widgetId);       
       query($sql,__METHOD__);
    }    
    public static function getCurrentWidgetWidth($widgetName){
        $sql = sprintf('SELECT 
                        buw.width 
                    FROM 
                        backoffice_user_widget buw,
                        backoffice_widget bw                        
                    WHERE                     
                        bw.widget_name="%s"
                    AND bw.id = buw.fk_backoffice_widget 
                    AND buw.fk_user=%d',
                    quote($widgetName),
                    User::getId());
        #echo "<br><br>".$widgetName."<br>";
        #echo nl2br($sql);
        return fetchVal($sql,__METHOD__);
    }
    public static function getUserWidget($userId){
        $sql = sprintf('SELECT 
                            bw.*                            
                        FROM 
                            backoffice_widget bw,
                            backoffice_user_widget buw 
                        WHERE buw.fk_user=%d AND buw.fk_backoffice_widget = bw.id
                        ORDER BY sorting',$userId);
                        
		return fetchArray($sql,__METHOD__);                 
    }
    public static function setWidgetSorting($userId,$newWidgetsInOrder){
        $i = 0;
        if(!empty($newWidgetsInOrder)) {
            foreach ($newWidgetsInOrder as $widgetnames){               
                $i++;                
                $params['orders']['widgetname'] = $widgetnames;
                $params['orders']['sorting']    = $i;
                #$params['orders']['id']         = self::getWidgetIdByName($widgetnames);    
                
                $sql = sprintf('UPDATE
                    backoffice_user_widget
                 SET 
                    sorting=%d
                WHERE
                 fk_backoffice_widget=(SELECT id FROM backoffice_widget WHERE widget_name = "%s") AND fk_user=%d',$params['orders']['sorting'],$params['orders']['widgetname'],$userId);
                #update user_wdigget set sorting=x where id=(select id from widgeds where name=%s)
                #echo $sql."\n";
                query($sql,__METHOD__);
            }  
        }
    }
    
    public static function getWidgetNameById($id){        
        $sql = sprintf('SELECT widget_name FROM backoffice_widget WHERE id = "%d"',$id);
        return fetchVal($sql,__METHOD__);
    }
        
    public static function getWidgetIdByName($name){
        $sql = sprintf('SELECT id FROM backoffice_widget WHERE widget_name = "%s"',quote($name));
        return fetchVal($sql,__METHOD__);
    }
    
    public static function setWidgetEnabled($userId,$widgetId,$enabled){
        if ($enabled){
            $sql = sprintf('SELECT IF(max(sorting) IS NULL,0,max(sorting)) from backoffice_user_widget where fk_user=%1$d',$userId);
            $maxSorting = fetchVal($sql,__METHOD__);
            
            $widget = WidgetFactory::getWidget($widgetId);
            
            //,width
            //,"%4$s"
            $sql = sprintf('INSERT IGNORE INTO 
                                backoffice_user_widget 
                            (fk_backoffice_widget,fk_user,sorting,width) 
                            VALUE(%1$d,%2$d,%3$d,"%4$s")',$widgetId,$userId,$maxSorting+1,$widget->getDefaultWidth());
            query($sql,__METHOD__);                             
        }   
        else {            
            $sql = sprintf('SELECT sorting FROM backoffice_user_widget WHERE fk_backoffice_widget=%d AND fk_user=%d',$widgetId,$userId);
            $maxsorting = fetchVal($sql,__METHOD__);
            
            $sql = sprintf('UPDATE backoffice_user_widget SET sorting=sorting-1 WHERE sorting>=%d AND fk_user=%d',$widgetId,$userId);            
            query($sql,__METHOD__);
            
            $sql = sprintf('DELETE FROM backoffice_user_widget WHERE fk_user=%d AND fk_backoffice_widget=%d',$userId,$widgetId);
            query($sql,__METHOD__);                        
        }
        
    }
    
    
	public static function registerNew(){
		
		$widgets 		= glob(self::$widgetDir.'*');

		if(!empty($widgets)){
			foreach($widgets as $widget){
				$className = str_replace(self::$widgetDir,'',$widget).'widget';
				self::addWidget($className);
			}
		}		
	}
	public static function currentWidgets($lang,$userId){
	   $sql = sprintf('SELECT 
                            bw.*,
                            IF(buw.id IS NOT NULL,1,0) widget_enabled 
                        FROM 
                        backoffice_widget bw
                        LEFT JOIN backoffice_user_widget buw ON buw.fk_user=%d AND buw.fk_backoffice_widget = bw.id',$userId);
                        
		$widgets = fetchArray($sql,__METHOD__);
		$out = array();
		if(!empty($widgets)){
			foreach($widgets as $originalWidgetName){			 			 
                $class                      = WidgetFactory::getWidget($originalWidgetName['widget_name']);
                                                                                                                                 
				if(method_exists ($class,'isEnabled')){
					$isEnabled = $class->isEnabled();
				}else{
					$isEnabled = true;
				}
				if($isEnabled){
					$info['title']              = $class->getTitle($lang);
                    
					$info['id']                 = $originalWidgetName['id'];
					$info['description']        = $class->getDescription($lang);  
					$info['widget_name']        = $originalWidgetName['widget_name'];
					$info['widget_enabled']     = $originalWidgetName['widget_enabled'];
					$info['icon']               = $class->imgPath;
					$out[]                      = $info;
				}
            }
        }               
                
                
		return $out;
	}
	public static function addWidget($className){
		$sql = sprintf('INSERT IGNORE INTO backoffice_widget 
					(widget_name) VALUE ("%s")',$className);
		query($sql,__METHOD__);
	}
}