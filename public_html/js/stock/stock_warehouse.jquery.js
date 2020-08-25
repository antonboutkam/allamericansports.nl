$(document).ready(function() {    
    $('.add_picker').click(function(e){
        parent.addLocationToProduct($('#fld_addid').val(),$(this).attr('rel'));            	
    });
    $('.move_picker').click(function(e){   
        parent.moveProductsToLocation($('.mover').serialize(),$(this).attr('rel'));            	
    });
      
    $('#change-location').change(function(){
        $('#location-frm').submit(); 
    });
});
