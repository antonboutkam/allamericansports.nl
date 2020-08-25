$.searchTimer = null;

$.filterItems = function(){
    clearTimeout($.searchTimer);
    $.searchTimer = setTimeout(function(){             
        query   =  ($('#filteritems').val()==$('#filteritems').attr('title'))?'':$('#filteritems').val();
        send    = {ajax:1,webshop_id:$('#fld_webshop_id').val(),current_page:$.currentPage,query:query,fk_locale:$('#fk_locale').val()};
        action  = function(data){
            $.fancybox.hideActivity();		
            $('#webshop_cms_pages').html(data.webshop_cms_pages);
        }    
        $.post(request_uri,send,action,'json');    
    },500)
}

$.moveItem = function(direction,id){
    $.fancybox.showActivity();
    send    = {_do:"move",direction:direction,id:id,ajax:1,webshop_id:$('#fld_webshop_id').val()};
    action  = function(data){
        $.filterItems();
    }
    $.post(request_uri,send,action,'json');
}

$.currentPage = 1;
$(document).ready(function(){


    $('#filteritems').live('keyup',function(){
        $.currentPage = 1;
        $.fancybox.showActivity();
        $.filterItems();
    });
    $('#fk_locale').live('change',function(){
        $.currentPage = 1;
        $.fancybox.showActivity();
        $.filterItems();
    })
    $('.delete').live('click',function(e){
        if(!confirm($('#sure-delete').html()))
        e.preventDefault();
    });
    $('a.move_up').live('click',function(e){
        e.preventDefault();
        $.moveItem('up',$(this).attr('data-id'));
    });
    $('a.move_down').live('click',function(e){
        e.preventDefault();
        $.moveItem('down',$(this).attr('data-id'));
    });
    $('#fld_webshop_id').live('change',function(e){
        $.fancybox.showActivity();
        window.location = '/settings/webshop_cms.html?webshop_id='+$(this).val();
    });

    $('.paginate').live('click',function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        $.currentPage = $(this).attr('rel');
        $.filterItems();
    })
	
});