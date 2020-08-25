
/**
* Main javascript
*
* */

var barcodestring;
var barcodetimer = null;
var barcodemaxtimer;
var barcodeformelement;
var lastkey;
$.barcodeEvent = function(barcode){    
    barcode = barcode.replace('#','');
    
    if(barcode.indexOf('BILL')==0)
        window.location = root+'/orders/collect.html?orderid='+barcode.replace('BILL','');
    else if(barcode.indexOf('ARTN')==0){
        if($.barcodeEventProduct!=undefined)
            $.barcodeEventProduct(barcode);
	else if(window.top.frames[0]!=undefined && window.top.frames[0].$.barcodeEventProduct!=undefined)
            window.top.frames[0].$.barcodeEventProduct(barcode) 
        else
            $.barcodeEventDefaultProduct(barcode);   
    }else{
        if(window.top.frames[0]!=undefined && window.top.frames[0].$.barcodeEventProduct!=undefined)
            window.top.frames[0].$.barcodeEventProduct(barcode)
        else if($.barcodeEventProduct!=undefined)
            $.barcodeEventProduct(barcode);
        else
           $.barcodeEventDefaultProduct(barcode);
    }
};
$.barcodeEventDefaultProduct = function(barcode){     
    idtype = (barcode.indexOf('ARTN')!='0')?'barcode':'';        
    $('#barcodelink').attr('href',root+'/products/view.html?iframe=1&idtype='+idtype+'&id='+barcode.replace('ARTN',''));
    $('#barcodelink').addClass('iframe');
    $.bindFancybox();
    $('#barcodelink').click();          
};

$.checkBarcode = function(e, currElement){            
        if(barcodetimer)
            clearTimeout(barcodetimer);
            
        barcodetimer = setTimeout(function(){
            barcodestring = '';
        },100);
                        
        if(e.which==13 && barcodestring.length>=2){
            e.preventDefault();
            if(lastkey!=13){
                $.fancybox.showActivity();
                currElement.val(currElement.val().toString().replace(barcodestring,''));
                $.barcodeEvent(barcodestring);
                $.fancybox.hideActivity();
            }
        }else        
           barcodestring = barcodestring + String.fromCharCode(e.which);      
        lastkey = e.which;
};
$.submenuNav = function(){
    var submenuHideTimer;
    var currentlyVisible;
    $('.submenu_trigger').mouseover(function(){
        
        clearTimeout(submenuHideTimer);
        // $('.submenu_sect').css('display','none');        
        $('#t_'+$(this).attr('id')).show(200);
    });
    $('.submenu_trigger, .submenu_sect .submenu').mouseout(function(){
        submenuHideTimer = setTimeout(function(){$('.submenu_sect').hide(200);},1000);                 
    });
    $('.submenu_sect .submenu').mouseover(function(){
        clearTimeout(submenuHideTimer);
    });               
};
$.langSelBox = function(){
    var hideTimer;
    var upicon = root+'/img/icons/menuup-icon-16x16.png';
    var downicon = root+'/img/icons/menudown-icon-16x16.png';
    $('.set_location').click(function(e){
        $.fancybox.showActivity();
    })
    $('#toggle_alt_locations').mouseover(function(e){
        clearTimeout(hideTimer);
        if(!$('#alt_locations').is(':visible')){
            $('#alt_locations').show('blind',200);
            $('#mnu_ico',this).attr('src',downicon);
        }
    }).mouseout(function(e){
        hideTimer = setTimeout(function(){
          $('#alt_locations').hide('blind',200);
          $('#mnu_ico').attr('src',upicon);
        },1500);
    });
    $('#alt_locations').mouseover(function(e){
        clearTimeout(hideTimer);
    }).mouseout(function(e){
        hideTimer = setTimeout(function(){
          $('#mnu_ico').attr('src',upicon);
          $('#alt_locations').hide('blind',200);
        },1500);
    });
};


$(document).ready(function() {    
    $('.tipsy').tooltipsy();
    $('.previewimg').live('mouseover',function(e){        
        $('#preview_img').css({'display':'block','left':(e.pageX + 5)+'px','top':e.pageY+'px'});
        $('#preview_img img').attr('src',$(this).attr('data-previewimg')); 
    }).live('mouseout',function(e){
        $('#preview_img').css('display','none');
    });    
	$('.mark_paid').live('click',function(e){	
		if(confirm($('#suremarkaid').html())){	
            $.fancybox.showActivity();  
			send = {_do:'mark_paid',id:$(this).attr('rel'),ajax:1};
			$.post(root+'/orders/overview.html',send,function(e){
				$.updateTable();
			},'json');			
		}
	});


    
    $('#fldbarcode').focus();
        
    barcodestring = '';
    $('input').keypress(function(e){
        barcodeformelement = $(this);
        $.checkBarcode(e,barcodeformelement);                                               
    });
    
    $('.date').datepicker({dateFormat:'yy-mm-dd'});      
    $('.accordion').accordion({autoHeight: false,fillSpace: false});

    //  Available in product picker mode
    $('.add_product').live('click',function(e){
        e.preventDefault();
        if($('#addtype').val()!=""){
            parent.addProduct($(this).attr('rel'),$('#addtype').val());
        }else{
            parent.addProduct($(this).attr('rel'));
        }
    });
        
    $('select').each(function(id,field){
        $(field).val($(field).attr('val'));
    });        

    $('.searchfield').focus(function(){
        if($(this).val()==$(this).attr('title'))
            $(this).val('');        
    }).blur(function(){
        if($(this).val()=='')
            $(this).val($(this).attr('title'));                   
    });
   $('.focusblur').live('focus',function(e){
      if($(this).val()==$(this).attr('title'))
          $(this).val('');
   }).live('blur',function(e){
      if($.trim($(this).val())=='')
          $(this).val($(this).attr('title'));
   });
   
    $.submenuNav();
    $.bindFancybox();
    $.langSelBox();

});
