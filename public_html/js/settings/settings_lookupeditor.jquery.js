current_page  = 1;
$.filterItems = function(){            
    send        = {_do:'update',ajax:1,group:group,current_page:current_page};
    $.fancybox.showActivity();
    action      = function(data){
            $.fancybox.hideActivity();
            $('#output_data').html($('#output_data',data.index).html());                        
    }
    $.post(request_uri,send,action,'json');                  
}
$(document).ready(function(){            
    value_field = $('#value_field');
    value_field.focus();
    
    $('.toggle_editmode').live('click',function(e){
        e.preventDefault();
        id          = $(this).attr('data-id');
        view_item   = $('#view_'+id);
        edit_item   = $('#edit_'+id);
       
        if(view_item.is(':visible')){
            view_item.css('display','none');
            edit_item.css('display','inline');
        }else{
            view_item.css('display','inline');
            edit_item.css('display','none');            
        }               
    });    
    update_fld = $('.update_fld');    
    update_fld.live('click',function(e){        
        e.preventDefault();
        myid        = $(this).attr('data-id');
        valfield    = $('#fld_'+myid);        
        send        = {_do:'update',ajax:1,id:myid,newval:valfield.val(),group:group,current_page:current_page};
        $.fancybox.showActivity();
        action      = function(data){
                $.fancybox.hideActivity();
                $('#output_data').html($('#output_data',data.index).html());                        
        }
        $.post(request_uri,send,action,'json');              
    });        
    paginage_lnk = $('.gotopage');
    paginage_lnk.live('click',function(e){
        e.preventDefault();
        current_page = $(this).attr('rel');
        $.filterItems();
    })
    
    delete_item = $('.delete_item');
    sure_delete = $('#sure_delete');
    added_msg   = $('#added_msg');
    saved_msg   = $('#saved_msg');
    
    if(_do=='add'){                
        alert(added_msg.html());
    }
    delete_item.live('click',function(e){
        if(confirm(sure_delete.html())){
            e.preventDefault();
            $.fancybox.showActivity();
            current_page    = 1;
            myid            = $(this).attr('data-id');
            send            = {_do:'delete',ajax:1,id:myid,group:group,current_page:current_page};            
            action          = function(data){
                $.fancybox.hideActivity();
                $('#output_data').html($('#output_data',data.index).html());
                window.parent.$.removeFromDropdown(data.group,data.id);                        
                // alert('Item verwijderd');
            }
            $.post(request_uri,send,action,'json');            
        }        
    });
    reload = $('.reload');
    reload.live('click',function(e){
        $.filterItems();
    });
    
});