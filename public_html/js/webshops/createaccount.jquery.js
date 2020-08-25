$(document).ready(function(){   
    $('#cp_lastname').trigger('focus');
    $('#cp_lastname').trigger('click');
    $('#cp_lastname').focus();
    $('#cp_lastname').click();
        
    $('#create_account').live('click',function(e){
        e.preventDefault();
        if($.trim($('#cp_firstname').val())==''){
            return alert($('#cp_firstname').attr('title'));
        }
        if($.trim($('#cp_lastname').val())==''){
            return alert($('#cp_lastname').attr('title'));
        }
        if($.trim($('#email').val())==''){
            return alert($('#email').attr('title'));
        }	
        if(!$.isEmail($('#email').val())){
            return alert($('#email').attr('data-valid'));
        }
        if($.trim($('#fldpassword').val())==''){
            return alert($('#fldpassword').attr('title'));
        }		
        if($('#fldpassword').val() != $('#fldpassword_confirm').val()){
            return alert($('#fldpassword').attr('data-nomatch'));
        }
        alert($('#accountcreatedmsg').html());
        $('#create_account_form').trigger('submit');		
    });
});
