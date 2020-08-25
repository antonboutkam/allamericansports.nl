var perspective;
var sort;
$(document).ready(function() {
    $('.view-product').click(function(e){
        e.preventDefault();        
        window.location = $(this).attr('href')+'&query='+$('#fld_search').val();           
    });            
    if($('#fld_search').val()=='')
        $('#fld_search').val($('#fld_search').attr('title'));

    $('.delete_product').live('click',function(e){
       if(confirm($('#fld_sure_remove').html())){
            $.fancybox.showActivity();
            send = {ajax:1,_do:'delete_product',product_id:$(this).attr('rel')}
            $.post(request_uri,
                send,function(data){
                updateTable(false);
                $.bindFancybox();
                $.fancybox.hideActivity();
            });
       }
    });
    $('.sort').live('click',function(e){
        e.preventDefault();
        sort = $(this).attr('rel');
        updateTable();         
    });
    $('#fld_search, .advsrc,#items_pp').keyup(function(e){updateTable(1);}).blur(function(e){updateTable(1);}).change(function(e){updateTable(1);});        
         
    $('.reload').live('click',function(e){
        e.preventDefault(); 
        updateTable();          
    });
    $('.paginate').live('click',function(e){
        e.preventDefault();
        current_page = $(this).attr('rel');
        updateTable();
    });
                  
    $('#toggle-advanced').click(function(e){
        e.preventDefault();
        $('#advanced-search').show('blind');
        $('#simple-search').hide('blind');
    });
    $('#toggle-simple').click(function(e){
        e.preventDefault();
        $('#advanced-search').hide('blind');
        $('#simple-search').show('blind');
    });
    $('.toggleview').click(function(e){
        e.preventDefault();
        perspective = $(this).attr('rel');
        updateTable();
    });

});
$.updateProductCatalog = function(){
    updateTable(false);
}

function updateTable(resetPage){
    if(resetPage!=undefined){
        $('#current_page').val(1);
        current_page = 1;
    }
    
    if($('#advanced-search').is(':visible'))            
        send = {    'query':$('.advsrc').serialize(),
                    'ajaxresult':1,
                    'type':'advanced',
                    'view':view,
					'items_pp':$('#items_pp').val(),
                    'current_page':current_page,
                    'sort':sort,
                    'perspective':perspective}
    else
        send = {    'query':$('#fld_search').val(),
                    'ajaxresult':1,
                    'defaultquery':$('#fld_search').attr('title'),
                    'current_page':current_page,
                    'view':view,
					'items_pp':$('#items_pp').val(),
                    'sort':sort,
                    'perspective':perspective}
    $.fancybox.showActivity();                                                                        
    $.post(request_uri,
            send,function(data){
                $('#searchresult').html(data);
                $.bindFancybox();
                $('.tipsy').tooltipsy();
                $.fancybox.hideActivity();
            });         
}