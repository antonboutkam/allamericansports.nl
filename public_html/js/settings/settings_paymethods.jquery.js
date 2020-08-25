function updatePaymethodList(){
    $.fancybox.showActivity();        
    $.post(request_uri,
            {   '_do'  :   'paymethods_tbl',
                'ajax'  :   '1',
            },
            function(data){
                $('#paymethods_tbl').html(data.paymethods_tbl);
                $.bindFancybox();
                $.fancybox.hideActivity();    
            },'json');    
}
$(document).ready(function() {    
    $('.delete-paym').live('click',function(e){
       e.preventDefault();
       if(confirm($('#sure-delete-paym').html())){
            $.post(request_uri,
                {   '_do'   :   'delete_paymethod',
                    'ajax'  :   '1',
                    'id'    :   $(this).attr('rel')},
                function(data){
                    $('#paymethods_tbl').html(data.paymethods_tbl);
                    $('#paym-deleted').css({display:'block'});
                    $.bindFancybox();
                    setTimeout(function(){$('#paym-deleted').css({display:'none'})},3000);      
                },'json');
       }
       return;           
    })       
});
