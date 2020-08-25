var sort;
$.searchTimeout = null;
$.currentPage = 1;
$.filterItems = function(){
	$.fancybox.showActivity();
	clearTimeout($.searchTimeout);
	$.searchTimeout	= setTimeout(function(){
		$.fancybox.showActivity();                     
		query = $('#fld_search').val().replace($('#fld_search').attr('title'),'');
		send = {    'ajax':1,										
					'current_page':$.currentPage,
					'query':query,
					'sort':sort}              
        action = function(data){				
			$.fancybox.hideActivity();
			$('#subscribers').html(data.subscribers);
		}
		$.post(request_uri,send,action,'json'); 
	},500);
}
$(document).ready(function() {
	$('.deleml').live('click',function(e){
		if(!confirm('Zeker weten?')){
			e.preventDefault();				
		} 
	}); 
	$('#fld_search').live('keyup',function(){		
		if($('#fld_search').val() != $('#fld_search').attr('title')){
			$.currentPage = 1;
			$.filterItems();			
		};
	});
	$('.paginate').live('click',function(e){
		e.preventDefault();		
		$.currentPage = $(this).attr('rel');
		$.filterItems();
	}); 
});