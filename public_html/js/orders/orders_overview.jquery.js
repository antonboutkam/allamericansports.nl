$.updateTable = function(resetPagination){
    $.fancybox.showActivity();
    if(resetPagination)   
        current_page = 1;
    $.post(request_uri+'?'+$('.filter').serialize(),{
        ajaxresult      :   1,
        current_page    :   current_page,
        sort            :   sort
    },function(data){
        $.fancybox.hideActivity();
        if(data.orders_tbl!=undefined && data.orders_tbl!=''){
            $('#no_orders').css('display','none');
            $('#orders_tbl').html(data.orders_tbl)                
        }else{                
            $('#no_orders').css('display','block');
            $('#orders_tbl').html(' ');
        }                                
    },'json');       
}
$(document).ready(function() {
	
    $('.resend_orderemail').live('click',function(e){	
            $.fancybox.showActivity();  
            send = {_do:'resend_orderemail',id:$(this).attr('rel'),ajax:1};
            $.post(root+'/orders/overview.html',send,function(e){
                    $.updateTable();
                    alert($('#mailsent').html());
            },'json');	
    });

    $('.resend_paymentemail').live('click',function(e){	
            $.fancybox.showActivity();  
                    send = {_do:'resend_paymentemail',id:$(this).attr('rel'),ajax:1};
                    $.post(root+'/orders/overview.html',send,function(e){
                            $.updateTable();
                            alert($('#mailsent').html());
                    },'json');	
    });

    $('.order_pickupemail').live('click',function(e){	
            $.fancybox.showActivity();  
            send = {_do:'orderpickedmail',orderid:$(this).attr('rel'),ajax:1};
            $.post(root+'/sendmail.php',send,function(e){
                    $.updateTable();
                    alert($('#mailsent').html());
            },'json');	
    });

    
    $('.undelete_order').live('click',function(e){
        e.preventDefault();
        if(confirm($('#sure_undelete').html())){
            $.fancybox.showActivity();
            send = {_do:'undelete_order',ajax:1,id:$(this).attr('data-id')};            
            $.post(request_uri, send, function(data){
                $.fancybox.hideActivity();
                $.updateTable();
            },'json');
        }
    });    
    $('.delete_order').live('click',function(e){
        e.preventDefault();
        if(confirm($('#sure_delete').html())){
            $.fancybox.showActivity();
            send = {_do:'delete_order',ajax:1,id:$(this).attr('rel')};            
            $.post(request_uri, send, function(data){
                $.fancybox.hideActivity();
                $.updateTable(true);
            },'json');
        }
    });
       
    $('.delete_order').live('click',function(e){
        e.preventDefault();
        if(confirm($('#sure_delete').html())){
            $.fancybox.showActivity();
            send = {_do:'delete_order',ajax:1,id:$(this).attr('rel')};            
            $.post(request_uri, send, function(data){
                $.fancybox.hideActivity();
                $.updateTable(true);
            },'json');
        }
    });
         
   $('.paginate').live('click',function(e){
        $('.paginate').removeClass('active');
        $(this).addClass('active');
        e.preventDefault();
        current_page = $(this).attr('rel'); 
        $.updateTable(false);
   });
   $('.filter').live('change',function(e){
        e.preventDefault();
        $.updateTable(true);
   });
    $('.sort').live('click',function(e){
        e.preventDefault();
        if(sort==$(this).attr('rel'))
            sort = $(this).attr('rel')+' DESC';    
        else
            sort = $(this).attr('rel');                    
        $.updateTable(true);        
    });   
});

 