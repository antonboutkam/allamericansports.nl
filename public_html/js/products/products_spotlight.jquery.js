
function addProduct(product_id){
    send = {_do:'add_product',ajax:1,id:product_id};
    $.post(request_uri,send,function(data){
        $('#searchresult').html($('#searchresult',data.index).html());
    },'json');
}

$(document).ready(function(){
    $('.remove_product').live('click',function(e){
        e.preventDefault();
        send = {_do:'remove_product',ajax:1,id:$(this).attr('data-id')};
        if(confirm($('#suredelete').html())){
            $.post(request_uri,send,function(data){
                $('#searchresult').html($('#searchresult',data.index).html());
            },'json');                    
        }
    });    
});