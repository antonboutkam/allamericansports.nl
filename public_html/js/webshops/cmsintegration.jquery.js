$.cmsCount = 0;
$(document).ready(function(){
	$('body').append('<a id="cms_trigger" href="http://"></a>');
	$('#cms_trigger').fancybox({
			'transitionIn'	    :  'elastic',
			'transitionOut'	    :  'elastic',
			'scrolling'	        :  'auto',
			'width'				:	1000,
			'height'			:	900,
			/*
			'width'             :  parseInt($(this).attr('data-iw')),
			'height'            :  parseInt($(this).attr('data-ih')),                
			*/
			'speedIn'	        :  600, 
			'speedOut'	        :  500, 
			'type'              :  'iframe',
			onClosed			: function() {
									$.fancybox.showActivity();
									window.location=window.location;
								}				
	});

	$(window).keypress(function(e){			
		// Drie keer op x drukken = cms starten
		
		if(e.which==120){
			$.cmsCount += 1;
		}else{
			$.cmsCount = 0;
		}
		if($.cmsCount<2){
			return;
		}
		
		if($.cmsEditMode == true){
			$.cmsEditMode = false;
			$('.cms_edit').removeClass('cms_edit');				
			$('.hiddencms,.hiddencms_nostyle').css('display','none');
		}else{
			$.cmsEditMode  = true;
			$('.cms').addClass('cms_edit');				
			$('.hiddencms,.hiddencms_nostyle').css('display','inline');
			
			
		}			
	
	});
	$('.cms_edit').live('click',function(e){
		e.preventDefault();
		if($(this).hasClass('article')){					
			url = 'https://backoffice.'+window.location.hostname+'/settings/webshop_cms.html?_do=edit&iframe=1&id='+$(this).attr('data-cmsid')+'&webshop_id='+current_webshop_id;			
		}else if($(this).hasClass('spotlight')){
			url = 'https://backoffice.'+window.location.hostname+'/products/spotlight.html?iframe=1';			
		}else if($(this).hasClass('product')){
			id = $(this).attr('data-id')?$(this).attr('data-id'):$(this).attr('id');
			url = 'https://backoffice.'+window.location.hostname+'/products/edit.html?id='+id+'&iframe=1';			
		}else if($(this).hasClass('banner')){
			url = 'https://backoffice.'+window.location.hostname+'/settings/webshop_banner.html?webshop_id='+current_webshop_id+'&iframe=1';						
		}else if($(this).hasClass('navtree')){
			url = 'https://backoffice.'+window.location.hostname+'/settings/webshop_navigation.html?webshop_id='+current_webshop_id+'&iframe=1';						
		}else if($(this).hasClass('webshop_setting')){
			url = 'https://backoffice.'+window.location.hostname+'/settings/webshop_config.html?webshop_id='+current_webshop_id+'&iframe=1';						
		}else if($(this).hasClass('navtreeitemtext')){
			url = 'https://backoffice.'+window.location.hostname+'/settings/webshop_editnav.html?iframe=1&parent=&menu_id='+$(this).attr('id').replace('submenu','')+'&webshop_id='+current_webshop_id+'&iframe=1';						
		}
		url = url.replace('www.','');
					
		$('#cms_trigger').attr('href',url);
		$('#cms_trigger').click();									
	});
});	
