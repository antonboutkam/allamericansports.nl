function is_email(email){
    var result = email.search(/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z]{2,3})+$/);
    if(result > -1){ return true; } else { return false; }
}

$(document).ready(function(){
    $('#send').live('click',function(e){
        hasErr = false;
        var lang = $('#lang').val();
        console.log(lang);
        e.preventDefault();
        if(!$.trim($('#fld_name').val())){
            if(lang=='gb')
                alert('Please fill in a name.');
            else
                alert('Voer uw naam in a.u.b.');
            hasErr =true;
        }else if(!$.trim($('#fld_eml').val())){
            if(lang=='gb')
                alert('Please fill in a emailaddress.');// 
            else
                alert('Voer een e-mail adres in a.u.b.');// Please fill in a emailaddress
            hasErr =true;
        }else if(!is_email($('#fld_eml').val())){
            if(lang=='gb')
                alert('Please fill in a valid emailaddress, like iemand@domain.com');
            else
                alert('Voer een geldig e-mail adres in a.u.b. Bijvoorbeeld iemand@domain.com.');// 'Please fill in a valid emailaddress, like iemand@domain.com'
            hasErr =true;
        }else if($.trim($('#fld_phone').val())==''){
            if(lang=='gb')
                alert('Please fill in a phonenumber');
            else
                alert('Voer een telefoonnummer in a.u.b.');
            hasErr =true;            
        }else if($.trim($('#fld_content').val())==''){
            if(lang=='gb')
                alert('Your message doesn\'t contain any content.');// Your message doesn't contain any content.'
            else
                alert('Uw bericht heeft geen inhoud.');// Your message doesn't contain any content.'
            hasErr =true;
        }
        if(!hasErr){
            $('#frmcontact').trigger('submit');
        }    
    });
});