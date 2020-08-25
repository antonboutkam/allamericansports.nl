var updatetimer = null;
$.barcodeEventProduct = function(barcode){
    $.fancybox.showActivity();
    console.log("Move!!!!!!");
    $.post(request_uri,
    {   'barcode'           :   barcode,           
        'ajax'              :   1   
    },function(data){
        $.fancybox.hideActivity();
        
        console.log('done, now what?');
        $.fancybox.close();    
    },'json');               
}

$(document).ready(function() {
    $('.add_basked').live('click',function(e){
        e.preventDefault();

        $('.row'+$(this).attr('rel')+' input').val(1);

        $('#shoppinglist .row'+$(this).attr('rel')).remove();
        $('#shoppinglist').append($('.row'+$(this).attr('rel')));

        $('#shoppinglist td').css('display','table-cell');
        $('#shoppinglist').css('display','table');
        
        $('#empty_shoppinglist').css('display','none');
        $('#shoppinglist .basketimg').attr('src',root+'/img/icons/delete-icon-16x16.png');
        $('#shoppinglist .row'+$(this).attr('rel'));
        $.bindFancybox();        
    });


    $('#cancel').click(function(e){
       e.preventDefault();
       $('.move').val('');
    });
    $('#reserve').live('click',function(e){
       $.fancybox.showActivity();
       e.preventDefault();
       hasErrors = false;
       orderHasItems=false;
       $('.move').each(function(i,e){           
           if($.trim($(e).val())){
               if(isNaN($(e).val())||parseInt($(e).val())!=$(e).val()){
                   hasErrors =true;
                   alert($('#err_nan').html());                   
                   $(this).css('background-color','red');
                   $.fancybox.hideActivity();
               }if(parseInt($(e).val()) > parseInt($('#max_'+$(e).attr('id')).html())){
                   hasErrors =true;
                   alert($('#err_tomany').html());
                   $(this).css('background-color','red');
                   $.fancybox.hideActivity();
               }
               if(parseInt($(e).val())>=1)
                   orderHasItems=true;
           }
       });
       if(!orderHasItems){
           hasErrors =true;
           alert($('#ordernoitems').html());
           $.fancybox.hideActivity();
        }
        
       if($('#from_location').val()==$('#to_location').val()){
           hasErrors =true;
           alert($('#err_fromisto').html());
           $.fancybox.hideActivity();
       }
           if(hasErrors){
                return;
            }else{
                send = {    data                : $('.move').serialize(),
                            ajax                : 1,
                            from_location	: $('#from_location').val(),
                            to_location         : $('#to_location').val(),
                            _do                 : "reserve",
                            note_txt            : $('#ontheway_note').html()
                        }
                $.post(request_uri,send,
                    function(data){
                        $.fancybox.hideActivity();
                        alert($('#reserved').html());
                        window.open(root+'/warehouse_pdf.php?deliveryId='+data.did+'&rand='+Math.floor(Math.random()*1100));
                        window.location = root;
                    },'json');
                $('.errors').css('display','none');
            }
    });
    $('#filter').focus(function(e){
        if($.trim($(this).val())==$(this).attr('title'))
            $(this).val('');
        $(this).trigger('select');
    }).blur(function(e){
        if($.trim($(this).val())=='')
            $(this).val($(this).attr('title'));
        
    });

    $.updateEvt = function(){
        $.fancybox.showActivity();
       if(updatetimer!=null)
           clearTimeout(updatetimer);
            updatetimer = setTimeout(function(){
           filter = ($('#filter').val()==$('#filter').attr('title'))?'':$('#filter').val();
           
            send = {    data                : $('.move').serialize(),
                        ajax                : 1,
                        from_location       : $('#from_location').val(),
                        to_location         : $('#to_location').val(),
                        filter              : filter
                    }
            
            $.post(request_uri,send,
                function(data){
                    $.bindFancybox();
                    $.fancybox.hideActivity();
                    $('#product_move_table').html(data.product_move_table);
                    $('#to_location').html(data.locations_to);
                },'json');
            $('.errors').css('display','none');
       },250);
    }
    $('.update').keyup(function(e){
       e.preventDefault();
       $.updateEvt();
    }).change(function(e){
       e.preventDefault();
       $.updateEvt();
    });
});