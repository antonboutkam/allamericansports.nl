$.barcodeEventProduct = function(barcode){
   $.fancybox.showActivity();
   send = {ajax:1,_do:'add',data:barcode};
   $.post(request_uri,send,function(data){
        $.fancybox.hideActivity();
        $('#serial').val('');
        $.filterSerialResultset();
   },'json');
}

$.filterSerialResultset = function(){
    $.fancybox.showActivity();
    send = {ajax:1,_do:'filter',data:$('#serial').val(),view:$('#fldview').val()};
    $.post(request_uri,send,function(data){
        $.fancybox.hideActivity();
        $('#serial_table').html(data.serial_table);
    },'json');
   
}
var filterResultsetTimer = null
$(document).ready(function() {
    $('#fldbarcode').focus();
    if($('#fldview').val()=='picker'){
        $('#serial').val(parent.$('#serial').val());
        $.filterSerialResultset();
    }
    $('.link_serial').live('click',function(e){
        e.preventDefault();
        parent.$.addSerial($(this).attr('rel'));
    });
    $('.delete_serial').live('click',function(e){
        e.preventDefault();
        if(confirm('Zeker weten?')){
            send = {ajax:1,_do:'delete',data:$(this).attr('rel')};
            $.post(request_uri,send,function(data){
                $.fancybox.hideActivity();
                $.filterSerialResultset();
            },'json');
        }
    });

    $('#serial').keyup(function(e){        
        if(filterResultsetTimer!=undefined)
            clearTimeout(filterResultsetTimer);
        filterResultsetTimer = setTimeout(function(){
            $.filterSerialResultset();
        },300);
    });
    $('#addserial').click(function(e){
       $.fancybox.showActivity();
       send = {ajax:1,_do:'add',data:$('#serial').val()};
       $.post(request_uri,send,function(data){
            $.fancybox.hideActivity();
            $('#serial').val('');
            $.filterSerialResultset();
       },'json');
    });

});