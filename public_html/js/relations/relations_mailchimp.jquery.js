$.checkUnsyncedRelationCount = function(){
    
    send = {};
    send.ajax   = 1;
    send._do    = 'sync_relations';
     
    action = function(data){
        $('#unsynced_relationcount').html(data.unsynced_relationcount);
        if(data.unsynced_relationcount>0){            
            $.checkUnsyncedRelationCount();
        }else{
            window.location = window.location+'?done=1';
        }
        
    }
    $.post(request_uri,send,action,'json');
    
}
$(document).ready(function() {  
    if($('#unsynced_relationcount').length == 1)
        $.checkUnsyncedRelationCount();            
});