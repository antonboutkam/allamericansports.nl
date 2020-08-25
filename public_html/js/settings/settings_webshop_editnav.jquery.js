$(document).ready(function(){
    /*
    $('#btn_add_item').live('click',function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        var send = {ajax:1,_do:'save',data:$('#add_form').serialize()};        
        $.post(request_uri,send,function(data){
            parent.$.loadMenuStructure($('#fld_parent').val(),$('#fld_webshop_id').val());            
        });
    });
    */ 

    if(parentSectionId!='' && webshopId!=''){        
        parent.$.loadMenuStructure(parentSectionId,webshopId,false);
    }
   
});    