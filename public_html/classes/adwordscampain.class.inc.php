<?php
require_once('../adwords/aw_api_php_lib_2.6.3/src/Google/Api/Ads/AdWords/Lib/AdWordsUser.php');
class AdwordsCampain {
    public static function createCampain($campainName){
        try {
          // Get AdWordsUser from credentials in "../auth.ini"
          // relative to the AdWordsUser.php file's directory.
          $user = new AdWordsUser();
          // Log SOAP XML request and response.
          $user->LogDefaults();
          // Get the CampaignService.
          $campaignService = $user->GetCampaignService('v201101');
          // Create campaign.
          $campaign = new Campaign();
          $campaign->name = $campainName;
          $campaign->status = 'PAUSED';
          $campaign->biddingStrategy = new ManualCPC();

          $budget = new Budget();
          $budget->period = 'DAILY';
          $budget->amount = new Money((float) 50000000);
          $budget->deliveryMethod = 'STANDARD';
          $campaign->budget = $budget;

          // Set the campaign network options to Google Search and Search Network.
          $networkSetting = new NetworkSetting();
          $networkSetting->targetGoogleSearch = true;
          $networkSetting->targetSearchNetwork = true;
          $networkSetting->targetContentNetwork = false;
          $networkSetting->targetContentContextual = false;
          $networkSetting->targetPartnerSearchNetwork = false;
          $campaign->networkSetting = $networkSetting;

          // Create operations.
          $operation = new CampaignOperation();
          $operation->operand = $campaign;
          $operation->operator = 'ADD';

          $operations = array($operation);

          // Add campaign.
          $result = $campaignService->mutate($operations);

          // Display campaigns.
          if (isset($result->value)) {
            foreach ($result->value as $campaign) {
              // print 'Campaign with name "' . $campaign->name . '" and id "'. $campaign->id . "\" was added.\n";
            }
          } else {
            print "No campaigns were added.\n";
          }
        } catch (Exception $e) {
          print $e->getMessage();
        }        
    }
    public static function deleteCampain($campaignId){
        try {
          // Get AdWordsUser from credentials in "../auth.ini"
          // relative to the AdWordsUser.php file's directory.
          $user = new AdWordsUser();
          // Log SOAP XML request and response.
          $user->LogDefaults();
          // Get the CampaignService.
          $campaignService = $user->GetCampaignService('v201101');
          $campaignId = (float)$campaignId;          

          // Create campaign with DELETED status.
          $campaign = new Campaign();
          $campaign->id = $campaignId;
          $campaign->status = 'DELETED';

          // Create operations.
          $operation = new CampaignOperation();
          $operation->operand = $campaign;
          $operation->operator = 'SET';

          $operations = array($operation);

          // Delete campaign.
          $result = $campaignService->mutate($operations);

          // Display campaigns.
          if (isset($result->value)) {
            foreach ($result->value as $campaign) {
              print 'Campaign with name "' . $campaign->name . '" and id "'
                  . $campaign->id . "\" was deleted.\n";
            }
          } else {
            print "No campaigns were deleted.";
          }
        } catch (Exception $e) {
          print $e->getMessage();
        }        
    }
    public static function getCampains(){
        try {
          // Get AdWordsUser from credentials in "../auth.ini"
          // relative to the AdWordsUser.php file's directory.
          $user = new AdWordsUser();

          // Log SOAP XML request and response.
          $user->LogDefaults();

          // Get the CampaignService.
          $campaignService = $user->GetCampaignService('v201101');

          // Create selector.
          $selector = new Selector();                     
          $selector->fields = array('Id', 'Name','Status');           
          $selector->ordering = array(new OrderBy('Name', 'ASCENDING'));

        // Create predicates.
        $statusPredicate = new Predicate('Status', 'NOT_IN', array('DELETED'));            
        $selector->predicates = array($statusPredicate);          
          
          // Get all campaigns.
          $page = $campaignService->get($selector);
                    
          // Display campaigns.
          if (isset($page->entries))
            foreach ($page->entries as $campaign){                
                $result[$campaign->id]['name'] = $campaign->name;                                            
                $result[$campaign->id]['id'] = $campaign->id;                                                            
            }    
            return $result;
        } catch (Exception $e) {
          print $e->getMessage();
        }        
    }

}
