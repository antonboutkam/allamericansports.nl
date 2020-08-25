$.updateCart = function(){
    $.fancybox.showActivity();        
    send = {
        _do     :   'update_cart',
        data    :   $('.cart_products').serialize(),
        ajax    :   1
    }
    $.post(root+'/'+lang+'/ajax.php',send,function(data){                
        if(data.to_many!=undefined)
            alert('U kunt momenteel niet meer dan '+data.to_many+' van deze producten bestellen.');
                 
        $('#cart_table').html(data.cart_table);
        $('#itemcount').html(data.cart_items_zf);        
        $('#totalprice').html(data.total);        
        
        if(data.cart_items==0)		
                window.location = '/checkout_empty.html';
        $.fancybox.hideActivity();
    },'json');
}        
$(document).ready(function(){
    $('.update_cart').live('click',function(e){        
        e.preventDefault();
        $.updateCart();
    });
    $('.add_product').live('click',function(e){
        e.preventDefault();
        element = $('.cart_products',$(this).parent().parent());     
    	element.val(parseInt(element.val())+1);
        $.updateCart();        
    });
    $('.cart_products').live('keyup',function(e){        
        $.updateCart();
    });
    
    $('.remove_product').live('click',function(e){
        e.preventDefault();
        element = $('.cart_products',$(this).parent().parent());         
    	element.val(parseInt(element.val())-1);
        $.updateCart();        
    });    

    $('.cart_remove').live('click',function(e){
        e.preventDefault();
        if(!confirm('Weet u het zeker?'))
            return;
        $.fancybox.showActivity();
        send = {
            _do     :   'remove_from_cart',
            id      :   $(this).attr('rel'),
            ajax    :   1
        }
        $.post(root+'/ajax.php',send,function(data){
            $('#cart_table').html(data.cart_table);
            $('#cart_items').html(data.cart_items);
            $.fancybox.hideActivity();
        },'json');
    });
});

