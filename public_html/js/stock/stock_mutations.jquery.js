function updateMutations(resetPagination){
    $.fancybox.showActivity();
    if(resetPagination)
        current_page = 1;    

    pdata   = {
       ajaxresult   :   1, 
       show         :   'mutations',
       onlynegative :   $('#fld_onlynegative').is(':checked')?1:0,
       sort         :   sort,
       current_page :   current_page,
       form         :   $('.mutations').serialize()
    };
    $.post(request_uri,
        pdata,function(data){
         $.fancybox.hideActivity();
         $.bindFancybox();
         $('#searchresult').html(data.stock_mutations_tbl);
    },'json');        
} 

$(document).ready(function() {    
    $('.reload').live('click',function(e){
        updateMutations(false);
    });
    $('.sortmutations').live('click',function(e){   
        e.preventDefault();
        sort = (sort == $(this).attr('href'))?sort + ' DESC':$(this).attr('href');                
        updateMutations(true);    
    });
    $('.mutations').live('change',function(e){
        updateMutations(true);        
    });
    $('.paginate').live('click',function(e){
        e.preventDefault();
        current_page = $(this).attr('rel');
        updateMutations(false);    
    });        
});
