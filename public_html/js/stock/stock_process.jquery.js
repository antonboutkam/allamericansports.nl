// Called from child window
function addLocationToProduct(fromStockId,toConfigurationId){
    $.fancybox.showActivity();
    $.post(request_uri,
    {   '_do'               :   'set_location',
        'ajax'              :   1,
        'fromStockId'       :   fromStockId,
        'toConfigurationId' :   toConfigurationId,
        'did'               :   $('#fld_did').val()
    },function(result){
        $('#stock_place_tbl').html(result.stock_place_tbl);
        $.fancybox.hideActivity();
        $.fancybox.close();
    },'json');
}

$(document).ready(function() {

    $('.edit_transfer').live('click',function(e){
        $.fancybox.showActivity();
        e.preventDefault();
        $('#fld_did').val($(this).attr('rel'));
        send =  {   _do :   "edit_transfer",
                    did :   $(this).attr('rel'),
                    ajax:   1};
        $.post(request_uri,send,
        function(result){                                    
            $('#stock_place_tbl').html(result.stock_place_tbl);
            $.bindFancybox();
            $.fancybox.hideActivity();            
        },'json')
    });
    $('#fld_complete').live('click',function(e){
        e.preventDefault();
        var unknownLocations = 0;
        $('.unknown').each(function(e,i){
            unknownLocations++;
        });
        if(unknownLocations>0)
            return alert($('#err-set-location').html());
        
        send = {
                _do :'complete',
                did : $('#fld_did').val(),
                ajax:1
                }
        $.post(request_uri,send,function(result){
               alert($('#msg-complete').html());
               $('#stock_process_open').html(result.stock_process_open);
               $('#stock_place_tbl').html('');
        },'json');
    });
});