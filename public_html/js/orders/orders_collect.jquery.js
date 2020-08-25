$.addSerial = function(serialId){
    $.fancybox.showActivity();
    send =  {ajax:1,_do:'add_serial',serial:serialId,orderid:$('#orderid').val()};
    $.post(request_uri,send,function(data){               
        $.fancybox.close();
        $.fancybox.hideActivity();
        $('#serial_numbers').html(data.serial_numbers);
        $('#serial').val('');
    },'json');            
}

$.barcodeEventProduct = function(barcode){
    $.addSerial(barcode);       
}
var collectInterval;
$(document).ready(function() {
    
    /*
    $('a.addexact').live('click',function(e){
        e.preventDefault(); 
        send = {ajax:1,_do:'add_exact',order_id:$(this).attr('rel')};
        $.fancybox.showActivity();
        $.post(request_uri,send,function(data){
            $.fancybox.hideActivity();
			alert('De order is doorgevoerd in Exact online');
            window.location = window.location;
            //alert(data.exact_result);
        },'json');           
    });
    */
    $('a.addgls').live('click',function(e){
       if(confirm($('#msg-suregls').html())){
            send = {ajax:1,_do:'add_gls',order_id:$(this).attr('rel')};
            $.fancybox.showActivity();
            $.post(request_uri,send,function(data){
                $.fancybox.hideActivity();
				alert('Transactie doorgevoerd.');
				/*
                alerrtstring = "Deze functionaliteit is helaas nog niet operationeel.\n\n"+            
                               "Je zult het dus nog even handmatig moeten doen. )-;\n"+                   
                               "We zouden deze velden posten: "+data.gls_transaction.postfields+"\n"+
                               "Naar deze url: "+data.gls_transaction.url+"\n"+
                               "Maar dat is de url van Laptopcentrale.nl";
                alert(alerrtstring);                  
				*/
            },'json');    
       } 
    });
    $('#fldbarcode').focus();
    $('.singlebox').live('click',function(e){
        e.preventDefault();
        $('.productbox').each(function(){
           if($.trim($(this).val())=='')
                $(this).val('1');           
        });
    });

    $('.delete_serial').live('click',function(e){
       e.preventDefault();
       if(confirm('Zeker weten?')){
           $.fancybox.showActivity();
           send = {ajax:1,_do:'remove_serial',serialId:$(this).attr('rel'),orderid:$('#orderid').val()};
           $.post(request_uri,send,function(data){
               $.fancybox.hideActivity();
               if(data.serial_numbers)
                    $('#serial_numbers').html(data.serial_numbers);
               else
                   $('#serial_numbers').html('');
           },'json');
       }
    });
    $('#serial').live('keyup',function(e){
        if(collectInterval)
            clearTimeout(collectInterval);
        collectInterval = setTimeout(function(){
            send = {ajax:1,serial:$('#serial').val(),_do:'findserial'}
             $.fancybox.showActivity();
            $.post(request_uri,send,function(data){
                 $.fancybox.hideActivity();
                $('#pickerlnk').css('display',data.serials_found.rowcount>0?'block':'inline');
                $('#row-count').html(data.serials_found.rowcount);
            },'json');
        },200);
    });
    $('#finalize').click(function(e){
        e.preventDefault();
        allInABox = true;
        $('.productbox').each(function(){                
           if($.trim($(this).val())=='')
                allInABox = false;                 
        });
        if(!allInABox){
            return alert($('#msg-incompl').html());
        }else{
            if(confirm($('#msg-sure').html())){
                $.fancybox.showActivity();
                $.post(request_uri,{
                    orderid     :   $('#orderid').val(),
                    _do         :   'complete_order',
                    ajaxresult  :   1,
                    boxconfig   :   $('.productbox').serialize()        
                },function(data){
                    $.post(root+'/sendmail.php',{ajax:1,_do:'orderpickedmail',orderid:$('#orderid').val()},function(data){
                        $.fancybox.hideActivity();  
                        if(!alert($('#msg-done').html())){
                            $('#fld_pakbon').css('display','inline-block');
                           // window.open(root+'/warehouse_pdf.php?deliveryId='+delivery_id);
                            window.location = window.location;
                        }
                    },'json');
                },'json');
            }
        }                                             
    });
    $('#park').live('click',function(e){
        e.preventDefault();
        window.location = root+'/orders/overview.html?view=picker';
    });
    $('#cancel').live('click',function(e){        
        e.preventDefault();
        if(confirm($('#msg-cancel').html())){
            $.fancybox.showActivity();
            $.post(request_uri,{
                orderid     :   $('#orderid').val(),
                _do         :   'cancel_order',
                ajaxresult  :   1
            },function(data){
                $.fancybox.hideActivity();
                window.location = root+'/orders/overview.html?view=picker';
            },'json');
        }        
    });
    $('#paid').live('click',function(){        
        if(confirm($('#msg-surepaid').html())){
            data = {
              orderid:$('#orderid').val(),
              _do:'pay_order'            
            };
            $.post(root+'/ajax.php',data, function(){
                parent.$.updateTable();    
                parent.$.fancybox.close();                
            },'json');
        }
    });
    $('#wait').live('click',function(){
        parent.$.fancybox.close();
        parent.$.updateTable();
    });
    $('#show_summation_form').live('click',function(e){
        e.preventDefault();
        $('#infobox, #actionbox').css('display','none');
        $('#summationform').css('display','block');
    });    
    $('#cancelsend').live('click',function(e){
        e.preventDefault();
        $('#infobox, #actionbox').css('display','block');
        $('#summationform').css('display','none');
    });    
    $('#sendsummation').live('click',function(e){
        $.fancybox.showActivity();
        $.post(root+'/ajax.php',$('.summation').serialize(),function(data){
            $.fancybox.hideActivity();
            alert('Bericht verzonden!');            
            $('#infobox, #actionbox').css('display','block');
            $('#summationform').css('display','none');                                    
       },'json');
    });
    $('#notpaid').live('click',function(){
        if(confirm($('#msg-surenotpaid').html())){
            parent.$.updateTable();
        }        
    });    
});
