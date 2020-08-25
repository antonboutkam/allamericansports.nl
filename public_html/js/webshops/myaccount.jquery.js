$(document).ready(function(){
    formHasErrors = function(){
        emptyCheckBilling = [   'cp_firstname','cp_lastname','email','phone',
                                'billing_street','billing_number','billing_postal',
                                'billing_city','billing_country'];
        emptyCheckShipping = [  'shipping_street','shipping_postal',
                                'shipping_city','shipping_country'];        
        hasErrors = false;
        $.each(emptyCheckBilling,function(index,Item){
            if(isEmpty(Item.toString())){
                hasErrors = true;
                markInvalid(Item.toString());
            }
        });
        if(!$.isEmail($('#email').val())){
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
        return hasErrors;
    }
    $('.updateaccount').live('click',function(e){
        e.preventDefault();
        if(!formHasErrors()){
            $.fancybox.showActivity()
            send = {ajax:1,_do:'updateaccount',data:$('#accountform').serialize()};
            $.post(request_uri,send,function(data){
                $.fancybox.hideActivity();
                alert('Wijzigingen opgeslagen');                
                if($.trim($('#redirect').val())!=''){                
                    window.location = '/'+$('#lang').val()+'/'+$('#redirect').val(); 
                }
            },'json');
        }
    });
    $('.updatepass').click(function(e){
        e.preventDefault();
        hasErrors = false;
        if($.trim($('#password').val())==''){
            markInvalid('password');
            hasErrors = true;
        }
        if($.trim($('#password_confirm').val())==''){
            markInvalid('password_confirm');
            hasErrors = true;
        }
        if(!hasErrors && ($('#password').val()!=$('#password_confirm').val())){            
            markInvalid('password_confirm');
            markInvalid('password');
            alert('De opgegeven wachtwoorden komen niet overeen');
            hasErrors = true;
        }        
        if(!hasErrors){            
            $.fancybox.showActivity();
            send = {ajax:1,_do:'changepass',password:$('#password').val()};
            $.post(request_uri,send,function(data){
                $.fancybox.hideActivity();
                alert('Uw wachtwoord is gewijzigd');
                $('#password_confirm,#password').val('');
            },'json');
        }
    });
});

$('#newsletteryn').live('click',function(e){
    name = $('#cp_firstname').val()+" "+$('#cp_lastname').val();
    email = $('#email').val();
    if($.trim(name)!='' && $.isEmail(email)){
    $.fancybox.showActivity();
        send = {    _do     :   'newsletter',
                    email   :   email,
                    name    :   name,
                    inout   :   $(this).is(':checked')?"in":"out",
                    ajax    :   1
                };
        $.post(root+'/ajax.php',send,function(data){
            $.fancybox.hideActivity();
            $('#signed_'+data.inout).show(100);
            setTimeout(function(){
                $('#signed_'+data.inout).hide(100);
            },1500);
        },'json');
    }    
});

