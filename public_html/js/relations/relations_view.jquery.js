$(document).ready(function() {    
    $('#back').click(function(e){
       e.preventDefault();
       if(document.referrer.indexOf('?'))
            referer = document.referrer + '&restoreState=1'; 
       else
            referer = document.referrer + '?restoreState=1';       
       document.location = referer;
    });
    $('#addnote').click(function(e){
       e.preventDefault();
       $.fancybox.showActivity();
       send = {_do:"store_note",ajax:1,note:$("#notecnt").val(),id:$('#fld_id').val()};
       doWhenDone = function(data){
         $.fancybox.hideActivity();
         $.bindFancybox();
         $('#hasnonotes').css('display','none');
         $('#customer_notes').html(data.customer_notes);
         $("#notecnt").val('');
       };
       $.post(request_uri,send,doWhenDone,'json');
    });
    $('#show_address').click(function(e){
        e.preventDefault();
        $('#show_address').css('display','none');
        $('#hide_address').css('display','inline');
        $('#address').show('blind',200);
    });
    $('#hide_address').click(function(e){
        e.preventDefault();
        $('#show_address').css('display','inline');
        $('#hide_address').css('display','none');
        $('#address').hide('blind',200);
    });
});