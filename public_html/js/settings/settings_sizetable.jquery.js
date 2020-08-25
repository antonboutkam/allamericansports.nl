
$(document).ready(function() {
    
    $('.delete_item').live('click',function(e){        
        e.preventDefault();       
        $.fancybox.showActivity();
        if(confirm($('#delete_sizetable').html())){       
            $.post(request_uri,{   
                '_do'           :   'delete-sizetable',
                'id'            :   $(this).attr('data-id'),
                'ajax'          :   1,
                'location'      :   $(this).attr('rel')
            },function(data){
                $('#content').html(data.content);
                $.bindFancybox();                
                $.fancybox.hideActivity();     
                                                                                      
            },'json');      
        }               
    });     
    $('#fld_save').live('click',function(e){
        
        if($.trim($('#fld_title').val())==''){
            alert($('#please_provide_title').html()); 
            e.preventDefault();
        }  
    });

});
