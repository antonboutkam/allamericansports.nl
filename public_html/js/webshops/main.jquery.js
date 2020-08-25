$.isEmail = function (email){
	var result = email.search(/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z]{2,3})+$/);
	if(result > -1){return true;} else {return false;}
}

$.showPreview = function(content){
   if($('.product-info-box').length==0){                   
       originalContent = $('#content').html();               
       $('.product-info-box div.cnt').html(content);              
   }else
       $('.product-info-box div.cnt').html(content);        
    if($.browser.msie)
        $('.product-info-box').show();   
    else
        $('.product-info-box').fadeIn(400,'swing');            
}
$.resetHeight = function(){    
    $('#maincnt').css('min-height',($(document).height()-150)+'px');  
}
$.menuHideTimer = null;
$.stopMenuHideTimer = function(){
    clearTimeout($.menuHideTimer);    
}
$.startMenuHideTimer = function(){    
    $.stopMenuHideTimer();
    $.menuHideTimer = setTimeout(function(){
        $('.hovermenu').hide(200);    
    },1000)
}
var originalContent = '';
var showTimeout = false;
var currElement;
var prefetched = {};
var searchTimeOut = null;

$.cmsEditMode = false;
$(document).ready(function(){
    $('a').each(function(i,e){
        if($(e).attr('href') == window.location.pathname){
            $(e).addClass('active');
        }    
    }); 
   /** Meet de populariteit van produkt linkjes */
   $('a').live('click',function(e){                
        if($(this).hasClass('track_menu_item')){
            send = {_do:'track_menu_item',fk_menu_item:$(this).attr('data-menu-id')};                             
			$.post(root+'/statistics.html',send);
		}
   });   
   
   
    $('#newsletter').live('click',function(e){ 
		e.preventDefault();
		if($.trim($('#email').val())==''){
			return alert('Geef alstublieft uw e-mailadres op.');
		}	
		alert('bedankt voor uw inschrijving');
		$('#subscribe_newsletter').submit();
		
	});

    $('.previewimg').live('mouseover',function(e){        
        $('#preview_img').css({'display':'block','left':(e.pageX + 5)+'px','top':e.pageY+'px'});
        $('#preview_img img').attr('src',$(this).attr('data-previewimg')); 
    }).live('mouseout',function(e){
        $('#preview_img').css('display','none');
    });  
    
    $('input,select').live('focus',function(e){
        e.preventDefault();
        $(this).removeClass('error');
    })
    maskmail = $('.maskmail');
    if(maskmail.length>0)
        $('.maskmail').html($('.maskmail').html().replace('#','@'));
        
        
    submit_lnk = $('.submit_lnk');
    submit_lnk.live('click',function(e){
        e.preventDefault();        
        $(this).closest('form').trigger('submit'); 
    });
    
    
    $.trySubmitLoginForm = function(){
        if(!$.isEmail($('#fld-eml').val()))
            return alert('Geef alstublieft een geldig e-mailadres op.');        
        if($.trim($('#fld-password').val())=='')
            return alert('Geef alstublieft uw wachtwoord op.');        
        $.fancybox.showActivity();		
        $('#loginform').submit();
        return true;              
    }
    $('#fld_login').click(function(e){
	   e.preventDefault();
        $.trySubmitLoginForm();      
    });

    $('#fld-eml,#fld-password').keypress(function(e){
        if(e.which == 13)
           $.trySubmitLoginForm();        
    });

    

    /* pre-fetch previews */
    $('.smallcartbox').each(function(i,e){
       $.post($(e).attr('href'),{ajax:1,prefetchId:$(e).attr('rel'),hide_basket:1},function(data){                     
           prefetched[data.prefetchId] = data.content;
       },'json');                         
    });
    
    $('.searchresgoto').live('click',function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        send = {current_page:$(this).attr('rel'),ajax:1,query:$('#fldsearch').val(),lang:lang}
        $.post('/'+lang+'/search.php',send,function(data){
            $('#main').html(data.content);     
             $('html, body').animate({scrollTop:0}, 'slow');            
            $.fancybox.hideActivity();    
        },'json'); 
    });
    $('.gotopage').live('click',function(e){
        /*e.preventDefault(); */
        $.fancybox.showActivity();
        /*
        send = {current_page:$(this).attr('rel'),ajax:1}
        
        $.post(request_uri,send,function(data){
            $('#content').html(data.content);     
             $('html, body').animate({scrollTop:0}, 'slow');            
            $.fancybox.hideActivity();    
        },'json'); 
        */
    });
    $('.smallcartbox').live('mouseover',function(){        
        clearTimeout(showTimeout);
        currElement = $(this);               
       if(prefetched[currElement.attr('rel')]){
           $.showPreview(prefetched[currElement.attr('rel')]);
       }else{ 
           showTimeout = setTimeout(function(){    
               $.post(currElement.attr('href'),{ajax:1,hide_basket:1},function(data){                     
                   $.showPreview(data.content);            
               },'json');                  
           },100);       
       }        
    })
    $('.smallcartbox').live('mouseout',function(){ 
        clearTimeout(showTimeout);
        showTimeout = setTimeout(function(){ 
            if($.browser.msie)
                $('.product-info-box').hide();   
            else
                $('.product-info-box').fadeOut(200);                           
        },250);
    });    
    if($('#contactbox').html()){$('#contactbox').html($('#contactbox').html().replace('#at#','@'));}
    if($('#contact').html()){ $('#contact').html($('#contact').html().replace('#at#','@'));}
    if($('#contactinfo').html()){$('#contactinfo').html($('#contactinfo').html().replace('#at#','@'));}   
    $('#logo').click(function(e){
        window.location = root+'/';
    });
    $.resetHeight();    
    $.bindFancybox();
    $('#fldsearch').live('keyup',function(e){       
        if($.trim($('#fldsearch').val())==''){
            return;
        }
        clearTimeout(searchTimeOut);
        searchTimeOut = setTimeout(function(){			
		$.fancybox.showActivity();
//		data = {ajax:1,,lang:lang};
        window.location = '/nl/search.php?query='+$('#fldsearch').val();
    /*    
		$.post('/'+lang+'/search.php',data,function(data){
				$.fancybox.hideActivity();            
				$('#main').html(data.content);
				$.resetHeight();
		},'json');                                                
	*/
        },600);
    });

    $('select').each(function(id,field){
        if($(field).attr('val'))
            $(field).val($(field).attr('val'));
    });   
    $('#email').focus(function(e){
        $('#validemailmsg').hide(300);
    });
    $('#password,#password_confirm').focus(function(e){
        $('#validpassword').hide(300);
    });
    $('.paymethod').focus(function(e){
        $( '#selpaymethod').hide(300);
    });
    showAddress = function(){
        $('#billing_addr').css('display',$(this).is(':checked')?'none':'block');
        if($(this).is(':checked'))
            $('.ship').val('');
    }
    showPassword = function(){
        $('#set_password').css('display',$(this).is(':checked')?'block':'none');
    }
    $('#fldsameasbilling').live('click',showAddress).live('focus',showAddress).live('blur',showAddress);
    $('#create_account').live('click',showPassword).live('focus',showPassword).live('blur',showPassword);
    isEmpty = function(elementName){
        if($.trim($("#"+elementName).val())=='')
            return true;
    }
    markInvalid = function(elementId){        
        $("#"+elementId).addClass('error');        
    }
    $('.news_signup').live('click',function(e){
        e.preventDefault();
        if($('.news_signup:selected').val())
            state = $('.news_signup:checked').val();
        else if($('.news_signup:checked').val())
            state = $('.news_signup:checked').val();
        else
            state = $('#signup_state').val();                     
        if(($('#fld_newsname').val()==$('#fld_newsname').attr('title')||$.trim($('#fld_newsname').val()=='')) && state=='in'){            
                $('.news_signup').attr('checked',false);
                markInvalid('fld_newsname');
                return alert('Geef alstublieft uw naam op.');
        }if($('#fld_newsemail').val()==$('#fld_newsemail').attr('title')){
            $('.news_signup').attr('checked',false);
            markInvalid('fld_newsemail');
            return alert('Geef alstublieft uw e-mailadres op.');
        }if(!$.isEmail($('#fld_newsemail').val())){
            $('.news_signup').attr('checked',false);
            markInvalid('fld_newsemail');
            return alert('Geef alstublieft een geldig e-mailadres op.');
        }
        $.fancybox.showActivity();
        send = {_do:'newsletter',action:state,email:$('#fld_newsemail').val(),name:$('#fld_newsname').val(), inout:state, ajax:1};
        $.post(root+'/ajax.php',send,function(data){
            if(data.inout=='in')
                $('#signedinmsg').css('display','block');
            else
                $('#signedoutmsg').css('display','block');            
            $('#news_signupform').css('display','none');
            $.fancybox.hideActivity();
        },'json');        
    });

    $('.add_basket').live('click',function(e){
        e.preventDefault();                         
        $.fancybox.showActivity();
        
        if($('#fld_quantity').val()==undefined)
            quantity = 1;
        else            
            quantity = $('#fld_quantity').val()?$('#fld_quantity').val():1;            
                
        if($(this).attr('data-id')){
            id = $(this).attr('data-id');            
        }else{
            id = $('#fld_id').val()?$('#fld_id').val():$(this).attr('rel');
        }                
        optioncount = 0;
        $('.options').each(function(i,e){            
            if($(e).val()){
                optioncount = optioncount + 1;
                optsend = {quantity:1,id:$(e).val(),_do:'order_product',ajax:1};
                $.post(root+'/'+lang+'/ajax.php',optsend,function(data){
                    $.fancybox.hideActivity();
                    $('#cart_items').html(data.cart_items);
                },'json');
            }
        });
        send = {quantity:quantity,id:id,_do:'order_product',ajax:1,optioncount:optioncount};        
        $.post(root+'/ajax.php',send,function(data){
                                                
            $.fancybox.hideActivity();                                    
            if(to_basket_on_order){
                window.location = root+'/'+lang+'/basket.html';
            }else{
                $('#cart_small').html(data.cart_small);    
            }                                    
        },'json');       
    });    
    $('#trace').live('click',function(e){
       e.preventDefault(); 
       var error = false; 
       if($.trim($('#fld_email_address').val())==''){
            markInvalid('fld_email_address');
            error = 'Geef alstublieft een e-mailadres op';            
       }       
       if(!$.isEmail($('#fld_email_address').val())){
            markInvalid('fld_email_address');
            error = 'Geef alstublieft een geldig e-mailadres op';            
       }
       if($.trim($('#fld_bill_number').val())==''){
            markInvalid('fld_bill_number');
            error = 'Geef alstublieft uw ordernummer op';            
       }
       if(error)
        return alert(error); 
       $('#traceform').trigger('submit');              
    });      
   $('.focusblur').live('focus',function(e){
      if($(this).val()==$(this).attr('title'))
          $(this).val('');
   }).live('blur',function(e){
      if($.trim($(this).val())=='')
          $(this).val($(this).attr('title'));
   });
});