$.filterItems = function(){
    $.fancybox.showActivity();
    searchQuery = $('#fld_searchfield').val().toString().replace($('#fld_searchfield').attr('title'),'');
    send = {ajax:1,current_page:current_page,query:searchQuery};
    $.post(request_uri,send,function(data){
        $.fancybox.hideActivity();   
        $('#product_groups_tbl').html(data.product_groups_tbl);
    },'json');        
}
$.searchTimer = null;
$(document).ready(function(){
   $('#create_group').live('click',function(){
        if($.trim($('#fld_group_name').val())==''){
            alert($('#nameemptyerror').html());
            return;
        }
   });
    $('#fld_searchfield').live('keyup',function(e){
        clearTimeout($.searchTimer);
        $.searchTimer = setTimeout(function(){
            e.preventDefault();
            current_page =1;
            $.filterItems();            
        },300);
    })
    $('.gotopage').live('click',function(e){
        e.preventDefault();
        current_page =$(this).attr('rel');
        $.filterItems()    
    });    

    $('.delete_group').live('click',function(){
        if(confirm($('#suredelete').html())){
            send = {ajax:1,_do:'delete_group',group_id:$(this).attr('data-id')};
            $.fancybox.showActivity();
            $.post(request_uri,send,function(data){
                $.fancybox.hideActivity();    
                $('#product_groups_tbl').html(data.product_groups_tbl);
            },'json');
        }
    });   
    if(group_exists)
        alert($('#group_existserror').html());
        
    if(group_saved)
        if(confirm($('#group_savedmsg').html()))
            window.location = '/products/groupedit.html?id='+new_group_id;        
        
    
});