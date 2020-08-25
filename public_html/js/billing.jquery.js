var sort;
$.updateTable = function (resetPage){
    if(resetPage!=undefined)
        $('#current_page').val(resetPage);
        
        var query = '';
        if($('#fld_search').val()!=$('#fld_search').attr('title')&&$.trim($('#fld_search').val())!=''){
            query = $('#fld_search').val();    
        }
        
        send = {    'ajaxresult'    :   1,
                    'current_page'  :   current_page,
                    'query'         :   query,
                    'view'          :   view,
                    'paid'          :   $('#paid').is(':checked'),
                    'unpaid'        :   $('#unpaid').is(':checked'),          
                    'sort'          :   sort}
    $.fancybox.showActivity();                                                                        
    $.post(root+'/billing.html',
            send,function(data){
                $('#billing_tbl').html(data.billing_tbl);
                $.bindFancybox();
                $.fancybox.hideActivity();
            },'json');         
}
$(document).ready(function() {
    $('#fld_search').live('keyup',function(e){        
         $.updateTable(true);
    });
    $('.filters').live('change',function(e){
        e.preventDefault();
        $.updateTable(true);
    });    
    $('.sort').live('click',function(e){
        e.preventDefault();
        sort = $(this).attr('rel');
        $.updateTable();         
    });            
    $('.reload').live('click',function(e){
        e.preventDefault(); 
        $.updateTable();          
    });
    $('.paginate').live('click',function(e){
        e.preventDefault();
        current_page = $(this).attr('rel');
        $.updateTable();
    });
});