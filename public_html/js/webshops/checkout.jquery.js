$(document).ready(function(){
    $('#sameasbilling').live('change',function(){        
        state = $(this).is(':checked')?'block':'none';
        $('#billing_addr').css('display',state);
    });
    
    
    $('#billing_country').live('change',function(e){
    	if($(this).html()!='Nederland')
            $('#foreign_vat').css('display','block')    		  
    	else{
            $('#foreign_vat').val('');
            $('#foreign_vat').css('display','none')
    	}
    });  
   
    $('input').live('focus',function(e){
        $(this).removeClass('error');
    });
 
    $('.submit').live('click',function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        $(this).closest('form').submit();
    });
    
    
    $('.create_account').live('click',function(e){        
        e.preventDefault();
        if(!formHasErrors()){
            $.fancybox.showActivity();
            $('#onestepform').submit();    
        }else{
            alert('Vul alstubieft de verplichtte velden in');   
        }
    });        
    $.callTimeout = null;
    getAddressByPostalCode = function(){
        clearTimeout($.callTimeout);
        $.callTimeout = setTimeout(function(){
            $.fancybox.showActivity();
            send = {};
            send._do            = 'get_address';
            send.billing_number = $('#billing_number').val();
            send.billing_postal = $('#billing_postal').val();
            
            response = function (data){
                $.fancybox.hideActivity();
                //data[resource][street]
                if(typeof(data.resource)!='undefined'){               
                $('#billing_street').val(data.resource.street);
                $('#billing_city').val(data.resource.town);  
                  }          
                console.log(data);
            }
            $.post(request_uri,send,response,'json');            
        },1100);                
    }
    /*
    $('#billing_number').live('keyup',function(e){
       getAddressByPostalCode();         
    });
    $('#billing_postal').live('keyup',function(e){
        getAddressByPostalCode();
    });
    */

});