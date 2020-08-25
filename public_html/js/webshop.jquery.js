$(document).ready(function(){
    $('.add_basked').click(function(e){
        e.preventDefault();
        
        send = {product_id:$(this).attr('rel'), quantity:1,ajax:1,_do:'add_basket'};
        $.post(request_uri,send,function(data){
            $('#basket').html(data.basket);
        },'json');
    })
});
