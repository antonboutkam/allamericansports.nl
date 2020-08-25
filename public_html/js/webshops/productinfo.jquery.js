$(document).ready(function(){
    checkAvailability = function(fieldId){
        send =  {_do:'check_stock',ajax:1,id:$('#fld_id').val()};
        $.post(request_uri,send,function(data){            
            if(data.stock<$('#'+fieldId).val()){
                alert('Er zijn momenteel nog maar '+data.stock+' van dit type voorradig, u kunt er daarom maximaal '+data.stock+' bestellen.');
                $('#'+fieldId).val(data.stock);
            }
            $.fancybox.hideActivity();
        },'json');
    }
    $('.fld_quantity').live('keyup',function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        checkAvailability($(this).attr('id'));
    });
    $('.add_product').live('click',function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        id = $(this).attr('id').replace('add_','');
        $('#'+id).val(parseInt($('#'+id).val())+1);
        checkAvailability(id);
    });
    $('.remove_product').live('click',function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        id = $(this).attr('id').replace('remove_','');
        $('#'+id).val(parseInt($('#'+id).val())-1);
        checkAvailability(id);
    });
    $('#showall,#hideall').live('click',function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        show_all = $(this).attr('id')=='showall'?1:0;
        send = {ajax:1,show_all_alt_notebooks:show_all};
        $.post(request_uri,send,function(data){                                    
            $('.notebooktypes').html($('.notebooktypes',$('"'+data.content+'+')).html());           
            $.fancybox.hideActivity();    
        },'json');
    }); 
    $('#showallparts,#hideallparts').live('click',function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        show_all = $(this).attr('id')=='showallparts'?1:0;
        send = {ajax:1,show_all_alt_partnrs:show_all};
        $.post(request_uri,send,function(data){                                    
            $('.suitablefor').html($('.suitablefor',$('"'+data.content+'+')).html());           
            $.fancybox.hideActivity();    
        },'json');
    });     
});
