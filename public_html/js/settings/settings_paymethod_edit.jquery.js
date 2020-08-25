$(document).ready(function() {
    $('#fld_store').click(function(e){
        e.preventDefault();
        $.post(request_uri + '?ajax=1&_do=store',
            $('#paymethods_form').serialize(),function(data){
                if(data.id!=undefined)
                    $('#fld_id').val(data.id);
                
                parent.updatePaymethodList();
                $('.stored-ok').css('display','inline');
                setTimeout(function(){$('.stored-ok').css('display','none');},3000);
            },'json');            
    });     
});