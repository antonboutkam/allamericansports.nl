
$.updatePaymentInfo = function(){    
    $.fancybox.showActivity();
    send = {ajax:1,_do:'update_paymentinfo',data:$('#onestepform').serialize()};
    $.post(request_uri,send,function(data){            
        $('#orderoverview').html(data.orderoverview);
        $.fancybox.hideActivity();
    },'json');
}

$.updateBillingDeliveryVis = function(){
    $('.paymeth').css('display','block');
    if($('.delivery:checked').val()==1){
        $('#paymethodblock5').css('display','none');        
        $('#paymethod5').attr('checked',false);
    }
    if($('.delivery:checked').val()==0){
        $('#paymethodblock4').css('display','none');
        $('#paymethod4').attr('checked',false);
    }
}
$(document).ready(function(){
    formHasErrors = function(){
        emptyCheckBilling = [   'cp_firstname','cp_lastname','email','phone',
                                'billing_street','billing_number','billing_postal',
                                'billing_city','billing_country'];
        emptyCheckShipping = [  'shipping_street','shipping_postal',
                                'shipping_city','shipping_country'];
        emptyCheckCreateAccount = ['password','password_confirm'];
        hasErrors = false;
        $.each(emptyCheckBilling,function(index,Item){            
            if(isEmpty(Item.toString())){
                hasErrors = true;
                markInvalid(Item.toString());
            }
        });
        
        if($('#pmethodfld').val()==1 && $('#paybank').val()==''){
            markInvalid('paybank');
            $('#selbank').css('display','inline');
            hasErrors = true;
        }


        if($.trim($('#email').val())!='' && !$.isEmail($('#email').val())){
            markInvalid('email');
            hasErrors = true;
            $('#validemailmsg').css('display','inline');
        }
        if(!$("#fldsameasbilling").is(':checked')){
            $.each(emptyCheckShipping,function(index,Item){
                if(isEmpty(Item.toString())){
                    hasErrors = true;
                    markInvalid(Item.toString());
                }
            });
        }        
        if($("#create_account").is(':checked')){
            passwordEmpty = false;            
            $.each(emptyCheckCreateAccount,function(index,Item){
                if(isEmpty(Item)){
                    hasErrors = true;
                    passwordEmpty = true;
                    markInvalid(Item);
                }
            });            
            if(!passwordEmpty){
                if($('#password').val() != $('#password_confirm').val()){
                    $('#validpassword').css('display','inline');
                    hasErrors = true;
                    markInvalid('password');
                    markInvalid('password_confirm');
                }
            }            
        }        
        
        if(!$('#pmethodfld').val()){
            $('.paymethod').each(function(i,e){
                hasErrors = true;
                $('#selpaymethod').css('display','inline');
                markInvalid($(e).attr('id'));
            });
        }
        return hasErrors;
    }    
    $('.paymethod').change(function(e){
        $('#pmethodfld').val($(this).val());
        $('#paybank').css('display',($('#pmethodfld').val()==1)?'inline':'none');
    });
    $('.paymethod').click(function(e){
        $('#pmethodfld').val($(this).val());
        $('#paybank').css('display',($('#pmethodfld').val()==1)?'inline':'none');
    });
    $('#billing_country').live('change',function(e){
    	if($(this).html()!='Nederland')
            $('#foreign_vat').css('display','block')    		  
    	else{
            $('#foreign_vat').val('');
            $('#foreign_vat').css('display','none')
    	}
    });

    $.updatePaymentInfo();
    $('.delivery,.paymethod,#paymethodscnt .image').live('click',function(e){        
		if($(this).hasClass('paymethod')){
			$(this).attr('checked',true); 
            $(this).not(':checked').click().change();
        }               
        if($(this).hasClass('image')){
            paymenthodId = $(this).parent().attr('id').replace('paymethodblock','');
            $('input[type=radio]',$(this).parent()).attr('checked',true);   
        }                 		        
        $.updatePaymentInfo();
        $.updateBillingDeliveryVis();
    });
    $('#billing_country,#shipping_country,#fldsameasbilling').live('change',function(e){
        $.updatePaymentInfo();
        $.updateBillingDeliveryVis();
    });
    
/*
    $('#paymethodblock5').css('display','none');
    $('#paymethodblock4').css('display','none');

*/

    $('.image').click(function(e){
        $('input',$(this).parent()).attr('checked', true);
    });
    $.updateBillingDeliveryVis();

    $('.place_order').live('click',function(e){
        e.preventDefault();
        if(!formHasErrors()){
            $.fancybox.showActivity();
            send = {ajax:1,_do:'order',data:$('#onestepform').serialize()};            
            $.post(request_uri,send,function(data){
                if(data && data.orderid)
                    $('#fld_payorder').val(data.orderid);                
                $.fancybox.hideActivity();
                $('#onestepform').submit();
            },'json');
        }
    });
});