$(document).ready(function() {    
    $('.gotopage').click(function(e){
        e.preventDefault();
        $('#fld_curr_page').val($(this).attr('rel'));
        $('#filterform').submit();        
    });
    
    $('#filteruser').change(function(){
        $('#fld_curr_page').val(1);
        $('#filterform').submit();        
    });
});