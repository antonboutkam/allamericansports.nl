$.updateQuantity = function(){
    var quantity = 0;
    $('input[type=checkbox]').each(function(i,e){
       if($(e).is(':checked'))
            quantity = quantity+1;
    });
    $('#quantity').html(quantity);
}
$(document).ready(function() {

    $('#fld_save').live('click',function(e){
        $.fancybox.showActivity();
        e.preventDefault();
        send = {_do:'save_sheet',ajax:1,data:$('#barcodesheet_form').serialize()};
        $.post(request_uri,send,function(data){
            $('#fld_id').val(data.id);
            parent.$.reloadBarcodeTable();            
            $.fancybox.hideActivity();            
            if(data.id){
                $('#fld_sample a').attr('href',$('#fld_sample a').attr('href').replace(/barcodepaper=[0-9]?/,'barcodepaper='+data.id));
                $('#fld_sample').css('display','inline');
            }
        },'json');
    });
});