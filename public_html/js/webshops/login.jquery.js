$(document).ready(function() {
    $('#reset-open').click(function(e){
        e.preventDefault();
    	$('#login-screen').css({display:'none'});
    	$('#reset-screen').css({display:'block'});    
    });
    $('#login-open').click(function(e){
        e.preventDefault();
    	$('#login-screen').css({display:'block'});
    	$('#reset-screen').css({display:'none'});    
    });


    $('#fld_testmode').change(function(e){
        $('#fld_testononff').css('display',$(this).is(':checked')?'block':'none');
    });    
    $('#fld-eml').focus();    
    $('#reset').click(function(e){
        e.preventDefault();
		$.fancybox.showActivity();
        $.post(root+'/'+lang+'/login.html',
            {
                '_do'       :   'reset',
                'ajax'      :   '1',
                'email'     :   $('#fld_forgot_email').val()            
            },function(data){
				$.fancybox.hideActivity();
                if(data.error){                    
                    $('#email-not-found').css({'display':'block'});
                    setTimeout(function(){$('#email-not-found').css({'display':'none'});},3000);
                }else{
					if(data.not_found){
						$('#email-not-found').css({'display':'block'});
						setTimeout(function(){
							$('#email-not-found').css({'display':'none'});                                            
						},7000);					
					}else{
						$('#succes').css({'display':'block'});
						setTimeout(function(){
							$('#succes').css({'display':'none'});
							$('#login-screen').css({display:'block'});
							$('#reset-screen').css({display:'none'});                                                 
						},7000);
					}
                }                                        
            },'json'        
        );
    })
});