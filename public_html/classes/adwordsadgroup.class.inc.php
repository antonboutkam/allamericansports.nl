<?php
class AdwordsadGroup {
    public static function getAddgroupAdds($addGroupId){
        try {
          // Get AdWordsUser from credentials in "../auth.ini"
          // relative to the AdWordsUser.php file's directory.
          $user = new AdWordsUser();

          // Log SOAP XML request and response.
          $user->LogDefaults();

          // Get the AdGroupAdService.
          $adGroupAdService = $user->GetAdGroupAdService('v201101');

          $adGroupId = (float) $addGroupId;

          // Create selector.
          $selector = new Selector();
          $selector->fields = array('Id', 'AdGroupId', 'Status');
          $selector->ordering = array(new OrderBy('Id', 'ASCENDING'));

          // Create predicates.
          $adGroupIdPredicate = new Predicate('AdGroupId', 'IN', array($adGroupId));
          // By default disabled ads aren't returned by the selector. To return them
          // include the DISABLED status in a predicate.
          $statusPredicate =
              new Predicate('Status', 'IN', array('ENABLED', 'PAUSED', 'DISABLED'));
          $selector->predicates = array($adGroupIdPredicate, $statusPredicate);

          // Get all ads.
          $page = $adGroupAdService->get($selector);

          // Display ads.
          if (isset($page->entries)) {
            foreach ($page->entries as $adGroupAd) {
              printf("Ad with id '%s', type '%s', and status '%s' was found.\n",
                  $adGroupAd->ad->id, $adGroupAd->ad->AdType, $adGroupAd->status);
            }
          } else {
            print "No ads were found.\n";
          }
        } catch (Exception $e) {
          print $e->getMessage();
        }        
    }
    public static function createAddGroup($campainId,$addGroupName){
        try {
          // Get AdWordsUser from credentials in "../auth.ini"
          // relative to the AdWordsUser.php file's directory.
          $user = new AdWordsUser();

          // Log SOAP XML request and response.
          $user->LogDefaults();

          // Get the AdGroupService.
          $adGroupService = $user->GetAdGroupService('v201101');

          $campaignId = (float) $campainId;

          // Create ad group.
          $adGroup = new AdGroup();
          $adGroup->name = $addGroupName;
          $adGroup->status = 'ENABLED';
          $adGroup->campaignId = $campaignId;
          
          
          // Create ad group bid.
          $adGroupBids = new ManualCPCAdGroupBids();
          $adGroupBids->keywordMaxCpc = new Bid(new Money(1000000));
          $adGroup->bids = $adGroupBids;

          // Create operations.
          $operation = new AdGroupOperation();
          $operation->operand = $adGroup;
          $operation->operator = 'ADD';

          $operations = array($operation);

          // Add ad group.
          $result = $adGroupService->mutate($operations);

          // Display ad groups.
          if (isset($result->value)) {
            foreach ($result->value as $adGroup) {
              print 'Ad group with name "' . $adGroup->name . '" and id "'
                  . $adGroup->id . "\" was added.\n";
            }
          } else {
            print "No ad groups were added.\n";
          }
        } catch (Exception $e) {
          print $e->getMessage();
        }        
    }
    public static function getAllAddGroups($campainId){
        try {
          // Get AdWordsUser from credentials in "../auth.ini"
          // relative to the AdWordsUser.php file's directory.
          $user = new AdWordsUser();

          // Log SOAP XML request and response.
          $user->LogDefaults();

          // Get the AdGroupService.
          $adGroupService = $user->GetAdGroupService('v201101');

          $campaignId = (float) $campainId;

          // Create selector.
          $selector = new Selector();
          $selector->fields = array('Id', 'Name');
          $selector->ordering = array(new OrderBy('Name', 'ASCENDING'));

          // Create predicates.
          $campaignIdPredicate =
              new Predicate('CampaignId', 'IN', array($campaignId));
          $selector->predicates = array($campaignIdPredicate);

          // Get all ad groups.
          $page = $adGroupService->get($selector);

          // Display ad groups.
          if (isset($page->entries)) {
            foreach ($page->entries as $adGroup) {                
                $result[$adGroup->id]['name'] = $adGroup->name;
                $result[$adGroup->id]['addgroupid'] = $adGroup->id;
                $result[$adGroup->id]['stats']['startDate'] = $adGroup->stats->startDate;
                $result[$adGroup->id]['stats']['endDate'] = $adGroup->stats->endDate;
                $result[$adGroup->id]['stats']['conversions'] = $adGroup->stats->conversions;
                $result[$adGroup->id]['stats']['clicks'] = $adGroup->stats->clicks;
                $result[$adGroup->id]['stats']['impressions'] = $adGroup->stats->impressions;
                $result[$adGroup->id]['stats']['ctr'] = $adGroup->stats->ctr;
                $result[$adGroup->id]['stats']['averageCpm'] = $adGroup->stats->averageCpm;
                $result[$adGroup->id]['stats']['averageCpc'] = $adGroup->stats->averageCpc;
            }
          } else {
            print "No ad groups were found.\n";
          }
          return $result;
        } catch (Exception $e) {
          print $e->getMessage();
        }        
    }
    public static function createAdds($adds){
        try {
          // Get AdWordsUser from credentials in "../auth.ini"
          // relative to the AdWordsUser.php file's directory.
          $user = new AdWordsUser();

          // Log SOAP XML request and response.
          $user->LogDefaults();

          // Get the AdGroupAdService.
          $adGroupAdService = $user->GetAdGroupAdService('v201101');

          $adGroupId = (float) 'INSERT_AD_GROUP_ID_HERE';
          /*
          $videoMediaId = (float) 'INSERT_VIDEO_MEDIA_ID_HERE';
            */
          // Create text ad.
          $textAd = new TextAd();
          $textAd->headline = 'Luxury Cruise to Mars';
          $textAd->description1 = 'Visit the Red Planet in style.';
          $textAd->description2 = 'Low-gravity fun for everyone!';
          $textAd->displayUrl = 'www.example.com';
          $textAd->url = 'http://www.example.com';
   
          /*  
          // Create ad group ad.
          $textAdGroupAd = new AdGroupAd();
          $textAdGroupAd->adGroupId = $adGroupId;
          $textAdGroupAd->ad = $textAd;

          // Create image ad.
          $imageAd = new ImageAd();
          $imageAd->name = 'Cruise to mars image ad #' . time();
          $imageAd->displayUrl = 'www.example.com';
          $imageAd->url = 'http://www.example.com';

          // Create image.
          $image = new Image();
          $image->data = MediaUtils::GetBase64Data('http://goo.gl/HJM3L');
          $imageAd->image = $image;

          // Create ad group ad.
          $imageAdGroupAd = new AdGroupAd();
          $imageAdGroupAd->adGroupId = $adGroupId;
          $imageAdGroupAd->ad = $imageAd;

          // Create template ad, using the Click to Play Video template (id 9).
          $templateAd = new TemplateAd();
          $templateAd->templateId = 9;
          $templateAd->dimensions = new Dimensions(300, 250);
          $templateAd->name = 'Cruise to mars video ad #' . time();
          $templateAd->displayUrl = 'www.example.com';
          $templateAd->url = 'http://www.example.com';

          // Create template ad data.
          $startImage = new Image();
          $startImage->data = MediaUtils::GetBase64Data('http://goo.gl/HJM3L');
          $startImage->type = 'IMAGE';
          $video = new Video();
          $video->mediaId = $videoMediaId;
          $video->type = 'VIDEO';

          $templateAd->templateElements = array(
              new TemplateElement('adData', array(
                  new TemplateElementField('startImage', 'IMAGE', NULL, $startImage),
                  new TemplateElementField('displayUrlColor', 'ENUM', '#ffffff'),
                  new TemplateElementField('video', 'VIDEO', NULL, $video)
              ))
          );

          // Create ad group ad.
          $templateAdGroupAd = new AdGroupAd();
          $templateAdGroupAd->adGroupId = $adGroupId;
          $templateAdGroupAd->ad = $templateAd;
*/
          // Create operations.
          $textAdGroupAdOperation = new AdGroupAdOperation();
          $textAdGroupAdOperation->operand = $textAdGroupAd;
          $textAdGroupAdOperation->operator = 'ADD';
/*
          $imageAdGroupAdOperation = new AdGroupAdOperation();
          $imageAdGroupAdOperation->operand = $imageAdGroupAd;
          $imageAdGroupAdOperation->operator = 'ADD';

          $templateAdGroupAdOperation = new AdGroupAdOperation();
          $templateAdGroupAdOperation->operand = $templateAdGroupAd;
          $templateAdGroupAdOperation->operator = 'ADD';

          $operations = array($textAdGroupAdOperation, $imageAdGroupAdOperation,
              $templateAdGroupAdOperation);
*/
          $operations = array($textAdGroupAdOperation);          
          // Add ads.
          $result = $adGroupAdService->mutate($operations);

          // Display ads.
          if (isset($result->value)) {
            foreach ($result->value as $adGroupAd) {
              print 'Ad with id "' . $adGroupAd->ad->id . '" and type "'
                  . $adGroupAd->ad->AdType . "\" was added.\n";
            }
          } else {
            print "No ads were added.\n";
          }
        } catch (Exception $e) {
          print $e->getMessage();
        }        
    }    
    /**
     * Created adwords adds by product type
     * @param type $productType [Adapter|Lader|Shawl]
     */
    
    public static function getByProductType($productType){
        $sql = sprintf('SELECT 
                            wmggp.menu_item l4,
                            wmgp.menu_item l3,
                            wmp.menu_item l2,
                            wm.menu_item l1, 
                            c.article_name,
                            pt.`type`,
                            c.article_number,
                            c.description,
                            w.hostname,
                            IF(wmc.id IS NULL,1,0) end_node
                        FROM                                                    
                            product_type pt,
                            stock s,
                            catalogue c,
                            catalogue_menu cm
                            LEFT JOIN webshop_menu wm ON wm.id=cm.fk_webshop_menu                            
                            LEFT JOIN webshop_menu wmp ON wmp.id=wm.fk_parent
                            LEFT JOIN webshop_menu wmgp ON wmgp.id=wmp.fk_parent
                            LEFT JOIN webshop_menu wmggp ON wmggp.id=wmgp.fk_parent,
                            webshops w,
                            webshop_menu wmlink
                            LEFT JOIN webshop_menu wmc ON wmlink.fk_parent=wmlink.id
                        WHERE    
                        cm.fk_catalogue=c.id
                        AND wmlink.id=wm.id                        
                        AND pt.id=c.`type`
                        AND pt.`type`="%s"
                        AND s.product_id=c.id
                        AND c.deleted IS NULL
                        AND wm.fk_webshop=w.id
                        GROUP BY cm.id
                        HAVING SUM(s.quantity)>1',
                        quote($productType));
        
        $data = fetchArray($sql,__METHOD__);
        foreach($data as $key=>$row){
            $base = '';
            if($row['l4'])
                $base = strtolower(urlencode($row['l4']).'/'.urlencode($row['l3']).'/'.urlencode($row['l2']).'/'.urlencode($row['l1']));
            else if($row['l3'])
                $base = strtolower(urlencode($row['l3']).'/'.urlencode($row['l2']).'/'.urlencode($row['l1']));
            else if($row['l2'])
                $base = strtolower(urlencode($row['l2']).'/'.urlencode($row['l1']));            
            $base = urlencode(strtolower($row['type'])).'/'.$base;            
            if($row['end_node']==1)
                $base = $base.'/'.$row['article_number'].'.html';            
            $data[$key]['url'] = 'http://www.'.$row['hostname'].'/'.preg_replace("#//#",'/',$base);
        }
        return $data;
    }
}
