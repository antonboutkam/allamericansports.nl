$.isEmail = function (email){
	var result = email.search(/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z]{2,3})+$/);
	if(result > -1){return true;} else {return false;}
}
/*
 * Called from parent window
 */
function addProduct(productId){
    $.fancybox.showActivity();
    send = {_do:'add_product',product_id:productId,ajax:1};
    $.post(request_uri,send,function(data){
        $.fancybox.hideActivity();
        $('#mailing_images').html($('#mailing_images').html()+data.preview);
    },'json');
}
var mailingInterval;
$(document).ready(function() {
    $('.addaddr').live('click',function(e){
        $.fancybox.showActivity();
        e.preventDefault();
        if(!$.isEmail($('#testaddr').val()))
            return alert($('#provide_valid_email').html());
        send = {_do:'add','address':$('.newtest').serialize(),'ajax':1};
        $.post(request_uri,send,function(data){            
            $.fancybox.hideActivity();
            $('.msg').remove();
            $('.focusblur').val($('.focusblur').attr('title'));            
            $('#addaddressrow').before(data.test_addresses);
        },'json');
    });
    $('.remove_product').live('click',function(e){
            e.preventDefault();
            $.fancybox.showActivity();
            send = {_do:'remove_product',ajax:1,remove_id:$(this).attr('rel')};
            $.post(request_uri,send,function(data){
                $.fancybox.hideActivity();
            },'json');
            $('#product'+$(this).attr('rel')).remove();
    });
    $('.delete').live('click',function(e){
        e.preventDefault();
        if(confirm($('#sure').html())){
            send = {_do:'delete','id':$(this).attr('rel'),'ajax':1};
            $.post(request_uri,send,function(data){
                $('.msg').remove();
                $('#addaddressrow').before(data.test_addresses);
            },'json');
        }
    });
    $('.mode').live('change',function(e){        
        if($('.mode:checked').val()==1){
            $('#testmode_to').css('display','block');
            $('#realmode_to').css('display','none');
        }else{
            $('#testmode_to').css('display','none');
            $('#realmode_to').css('display','block');
        }
    });
    $('.preview').live('click',function(e){
        e.preventDefault();
           send = {
             ajax       : 1,
             _do        : 'store_preview_data',
             title      : $('#title').val(),
             content    : $('#messagecontent').val(),
             currentPage: $('#currentPage').val(),
             to_group   : $('#to_group').val(),
             mode       : ($('.mode:checked').val()==1)?'test':'',
             template   : $('#fldtemplate').val()
           };
            $.post(request_uri,send,function(data){
                $('<a href="'+request_uri+'?iframe=1&_do=preview" class="iframe"></a>').fancybox({
                    'transitionIn'      :   'elastic',
                    'transitionOut'	:   'elastic',
                    'scrolling'         :  'auto',
                    'width'             :   960,
                    'onClosed'          :   function(){
                                            // When iframe closes, sometimes the window below is updated with Ajax.
                                            $.bindFancybox();},
                    'height'            :   800,
                    'speedIn'           :   600,
                    'speedOut'          :   200,
                    'overlayShow'	:   false}).trigger('click');
            },'json');
    });
    $.sendMailing = function(){
       $('.searchbox').css('display','none');
       $('#busy').css('display','block');
       send = {
         ajax       : 1,
         _do        : 'send',
         title      : $('#title').val(),
         content    : $('#messagecontent').val(),
         currentPage: $('#currentPage').val(),
         to_group   : $('#to_group').val(),
         mode       : ($('.mode:checked').val()==1)?'test':'',
         template   : $('#fldtemplate').val()
       };
        $.post(request_uri,send,function(data){
            if(data.done==1){
                $('#sendstatus').html('Mailing is verzonden');
                clearInterval(mailingInterval);
                $('#currentPage').val(1);
                alert($('#mailingdone').html());
                window.location.reload()
            }else{
		  $('#currentPage').val(parseInt($('#currentPage').val())+1);
                $('#sendstatus').html($('#sendstatus').html()+'<br />Bezig met batch '+$('#currentPage').val()+', 5 berichten versturen...');
                $('#currentPage').val(parseInt($('#currentPage').val()));
                $.sendMailing();
            }            
        },'json');
    }
    $('#sendmailing').live('click',function(e){
        $.fancybox.showActivity();
        $.sendMailing();
    });
});