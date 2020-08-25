var sortOrder = {};
$(document).ready(function(){
    $('.delete').live('click',function(e){
        if(!confirm($('#delete-sure').html())){
            e.preventDefault();
        }
    });
    
    /* Foto's sorteren */
    $(".bannerimgcont").sortable({
        "stop":   function() {
            $.fancybox.showActivity();
            $('.banner_thumb').each(function(i,e){
               sortOrder[i+1] = $(e).attr('id').replace('bitem','');                 
            });
            send = {_do:'change_order',ajax:1,new_order:sortOrder,webshop_id:$('#fld_webshop_id').val()};
            $.post(request_uri,send,function(data){
                $.fancybox.hideActivity();    
            },'json');            
        }
    }).disableSelection();    
    
});