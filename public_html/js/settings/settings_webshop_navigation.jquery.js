$.loadMenuStructure = function(sectionId,webshopId,closeFancybox){
    if(typeof(closeFancybox) == 'undefined')
    {
        closeFancybox = true;
    }

    send  = {
        _do : 'gettreesegment',
        ajax:1,
        sectionid:sectionId,
        webshop_id:webshopId
    };

    $.post(request_uri,send,function(data){
        if(data.sectionid==false){
            $('.treeselect').remove();
            $('#treesegment').html(data.treesegment);            
        }else{
            $('a[rel='+data.sectionid+']').addClass('open');            
            $('ul',$('#menu_item_'+data.sectionid).parent()).remove();
            $('#menu_item_'+data.sectionid).parent('ul.treeselect').remove();
            $('#menu_item_'+data.sectionid).after(data.treesegment);            
        }       
        $.fancybox.hideActivity();
        if(closeFancybox)
            $.fancybox.close();
        $.bindFancybox();  
        
    },'json');
}
$(document).ready(function(e) {
    var upButton = $('.moveup');
    var downButton = $('.movedown');
    $.move = function(direction, SegementId, itemId, webshop_id){

        $.fancybox.hideActivity(); 
        send = {
            direction : direction,
            itemId : itemId,
            sectionid :  SegementId,
            SegementId : SegementId,
            webshop_id : webshop_id,
            ajax : 1
        };
        action  = function(data){
            $('#section_'+data.sectionid).replaceWith(data.treesegment);
        };
        $.post(request_uri, send, action, 'json');
    }
    upButton.live('click',function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        $.move('up',$(this).attr('data-section'),$(this).attr('data-id'),$(this).attr('data-webshop'));
    });
    downButton.live('click',function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        $.move('down',$(this).attr('data-section'),$(this).attr('data-id'),$(this).attr('data-webshop'));
    });
        
        
    $('#fld_menu_item').focus();    
    $('.remove').live('click',function(e){
        e.preventDefault();
        if(confirm($('#msg-remove').html())){
           $.fancybox.showActivity(); 
           send = {ajax:1,_do:'remove',section_id:$(this).attr('rel'),webshop_id:$('#fld_webshop_id').val()};
           $.post(request_uri,send,function(data){
               $('#menu_item_'+data.removed_section).parent().parent('.treeselect').replaceWith(data.treesegment);
               $.fancybox.hideActivity();
               window.location = window.location;
           },'json');
        };
        window.location = window.location;
    });
    $('.togglemenu').live('click',function(e){
        e.preventDefault();            
        if($(this).hasClass('open')){                
            $('ul',$(this).parent()).remove();
            $('a[rel='+$(this).attr('rel')+']').removeClass('open');
        }else{                
            $.fancybox.showActivity();                                
            $.loadMenuStructure($(this).attr('rel'),$('#fld_webshop_id').val());
        }
    });
});