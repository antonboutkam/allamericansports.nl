$.reloadBarcodeTable = function(){
    $.post(request_uri, {_do:'reload',ajax:1}, function(data){
        $('#barcode_tbl').html(data.barcode_tbl);
    }, 'json');
};

$(document).ready(function() {    
    $('.delete_item').live('click',function(e){
       e.preventDefault();
       if(confirm($('#sure_lbl').html())){
           $.fancybox.showActivity();
           send = {_do:'delete','id':$(this).attr('rel'),ajax:1}
           $.post(request_uri,send,function(data){
               $.fancybox.hideActivity();
                $('#barcode_tbl').html(data.barcode_tbl);
           },'json');
       }
    });
});
