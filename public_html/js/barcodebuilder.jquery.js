$.updateQuantity = function(){
    var quantity = 0;
    $('input[type=checkbox]').each(function(i,e){
       if($(e).is(':checked'))
            quantity = quantity+1;                        
    });    
    $('#quantity').html(quantity);
}
$(document).ready(function() {  
    $('.select').click(function(e){
        var checkuncheck = true;
        $('.'+$(this).attr('rel')).each(function(i,e){
           if($(e).is(':checked'))
                checkuncheck = false;                        
        });        
        $('.'+$(this).attr('rel')).attr('checked',checkuncheck);
        $.updateQuantity();
    });
    $('#all').click(function(e){        
    	e.preventDefault();
        $('input[type=checkbox]').attr('checked',true);
        $.updateQuantity();
    });
    $('#none').click(function(e){
    	e.preventDefault();
        $('input[type=checkbox]').attr('checked',false);
        $.updateQuantity()
    });
    $('#half1').click(function(e){
    	e.preventDefault();
        $('.row1,.row2,.row3,.row4,.row5').attr('checked',true);
        $.updateQuantity();
    });
    $('#half2').click(function(e){
    	e.preventDefault();
        $('.row6,.row7,.row8,.row9').attr('checked',true);
        $.updateQuantity();    
    });




    
});