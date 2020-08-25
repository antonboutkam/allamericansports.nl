$(document).ready(function() {
    $('#fld_save').click(function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        $.post(root + '/edit_note.php?ajax=1&_do=store',
            $('#note_edit').serialize(),function(data){
                if(data.id!=undefined)
                    $('#fld_id').val(data.id);
                
                parent.updateNotes();
                parent.$.fancybox.close();  
                $.fancybox.hideActivity();        
                
                $('.stored-ok').css('display','inline');
                setTimeout(function(){$('.stored-ok').css('display','none');},3000);
            },'json');            
    });
});