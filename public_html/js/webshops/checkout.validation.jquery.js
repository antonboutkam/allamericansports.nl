formHasErrors = function(){    
    emptyCheckBilling = ['cp_firstname','cp_lastname','email','phone','billing_street','billing_postal','billing_city','billing_country'];
    emptyCheckShipping = ['shipping_street','shipping_postal','shipping_city','shipping_country'];
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
	console.log('een');
        hasErrors = true;
    }
    
    if($.trim($('#email').val())!='' && !$.isEmail($('#email').val())){
        markInvalid('email');
        hasErrors = true;
		console.log('twee');		
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