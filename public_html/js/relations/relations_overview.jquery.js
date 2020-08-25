var sort;
$.searchTimeout = null;
$.updateClientTable = function(){
	clearTimeout($.searchTimeout);
	$.searchTimeout	= setTimeout(function(){
		$.fancybox.showActivity();                      
		if($('#advanced-search').is(':visible'))            
			send = {    'query':$('.advsrc').serialize(),
						'ajaxresult':1,
						'ajax':1,
						'type':'advanced',
						'view':view,
						'items_pp':$('#items_pp').val(),
						'current_page':$('#current_page').val(),
						'sort':sort}              
		else
			send = {    'query':$('#fld_search').val(),
						'ajaxresult':1,
						'ajax':1,
						'defaultquery':$('#fld_search').attr('title'),
						'view':view,
						'items_pp':$('#items_pp').val(),						
						'current_page':$('#current_page').val(),
						'sort':sort}                                                    
		$.post(request_uri,
				send,function(data){
					$('#relations_tbl').html(data);
					$.bindFancybox();
					$.fancybox.hideActivity();
		}); 
	},500);
}
$(document).ready(function() {
    
    $('.viewclient').live('click',function(e){
       e.preventDefault();       
       $.cookie('query', $('#fld_search').val());
       $.cookie('view', view);
       $.cookie('current_page', current_page);
       if(sort)
            $.cookie('sort', sort);                     
       window.location = root+'/relations/view.html?id='+$(this).attr('rel')+'&iframe=1&view=picker'; 
    });
    if(restoreState){
         view           = $.cookie('view');
         current_page   = $.cookie('current_page');
         if($.cookie('sort')!='undefined')
            sort        = $.cookie('sort');
         $('#fld_search').val($.cookie('query'));
         
		 $('#current_page').val('0');
		 $.updateClientTable();
    }
    
    $('.sort').live('click',function(e){
        e.preventDefault();
        sort = $(this).attr('rel');
        $('#current_page').val('0');      
    });
    $('.add_customer').live('click',function(e){
       e.preventDefault(); 
       parent.addCustomer($(this).attr('rel'));
    });
    $('#fld_search, .advsrc, #items_pp').keyup(function(e){
		$('#current_page').val(1);
		$.updateClientTable();
	}).blur(function(e){
		$('#current_page').val(1);
		$.updateClientTable();
	}).change(function(e){
		$('#current_page').val(1);
		$.updateClientTable();
	});        
         
    $('.reload').live('click',function(e){
        e.preventDefault(); 
        $.updateClientTable();
    });
    $('.paginate').live('click',function(e){
        e.preventDefault();        
		$('#current_page').val($(this).attr('rel'));
        $.updateClientTable();
    });
    $('#toggle-advanced').click(function(e){
        e.preventDefault();
        $('#advanced-search').show('blind');
        $('#simple-search').hide('blind');
    });
    $('#toggle-simple').click(function(e){
        e.preventDefault();
        $('#advanced-search').hide('blind');
        $('#simple-search').show('blind');
    });    
});