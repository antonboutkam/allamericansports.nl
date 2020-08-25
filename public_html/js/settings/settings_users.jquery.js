var sort;
function updateUserList(){
    $.fancybox.showActivity();        
    $.post(request_uri,
            {   '_do'  :   'update_userlist',
                'ajax'  :   '1',
                'sort'  :   sort},
            function(data){
                $('#userlist_tbl').html(data.userlist_tbl);
                $.bindFancybox();
                $.fancybox.hideActivity();    
            },'json');
} 
$(document).ready(function() {    
   $('.delete').live('click',function(e){
       e.preventDefault();       
       if(confirm($('#sure-delete').html().replace('USERNAME',$(this).attr('title')))){
            $.post(request_uri,
                {   '_do'   :   'delete_user',
                    'ajax'  :   '1',
                    'id'    :   $(this).attr('rel')},
                function(data){
                    $('#userlist_tbl').html(data.userlist_tbl);
                    $('#user-deleted').css({display:'block'});
                    $.bindFancybox();
                    setTimeout(function(){$('#user-deleted').css({display:'none'})},3000);      
                },'json');
       }
       return; 
    });       
});
$('.sort').live('click',function(e){
   e.preventDefault();  
   sort = $(this).attr('rel');
   updateUserList();
});