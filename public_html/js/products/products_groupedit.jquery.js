function addProduct(productId){ 
    send = {    ajax            :   1,
                _do             :   'link_product',
                fk_catalogue    :   productId,
                fk_product_group:   $('#fld_id').val(),
                id              :   $('#fld_id').val()
            };
    $.post(request_uri,send,function(data){
        $('#product_group_products_tbl').html(data.product_group_products_tbl);
    },'json');        
}
$(document).ready(function(){
        
   $('#create_group').live('click',function(){
        if($.trim($('#fld_group_name').val())==''){
            alert($('#nameemptyerror').html());
            return;
        }
   }); 
    $('.unlink_item').live('click',function(){
        if(confirm($('#sureunlink').html())){
            send = {    ajax            :   1,
                        _do             :   'unlink_product',
                        link_id         :   $(this).attr('data-id'),
                        id              :   $('#fld_id').val()
                    };
            $.post(request_uri,send,function(data){
                $('#product_group_products_tbl').html(data.product_group_products_tbl);
            },'json');          
        }
    });
    /*
    $('.delete_group').live('click',function(){
        if(confirm($('#suredelete').html())){
            send = {ajax:1,_do:'delete_group',group_id:$(this).attr('data-id')};
            $.fancybox.showActivity();
            $.post(request_uri,send,function(data){
                $.fancybox.hideActivity();    
                $('#product_groups_tbl').html(data.product_groups_tbl);
            },'json');
        }
    });  
    */ 
    if(group_exists){
        alert($('#group_existserror').html());
    }    
    if(name_changed){
        alert($('#group_namechanged').html());
    }
});