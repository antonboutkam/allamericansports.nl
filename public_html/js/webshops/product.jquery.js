$(document).ready(function(){                    			
    $("#gallery_output img").not(":first").hide();
    $("#product a").click(function(e) {   
        e.preventDefault();
        if($("#" + $(this).attr('data-id')).is(":hidden")) {
            $("#gallery_output img").css('display','none');
            $("#" + $(this).attr('data-id')).css('display','block');
        }
    });			    

    $('a.fancy').fancybox({        
            'speedIn'           :   600,
            'speedOut'          :   200,            
            'type'              :   'image',
            'transitionIn'	:   'elastic',
            'transitionOut'	:   'elastic',
            'autoDimensions'    :   false
    });

    $('#redirect_size').live('change',function(e){
        $.fancybox.showActivity();
        window.location = $(this).val();
    });                                                                                 
    $('.size_toggle').live('click',function(e){                                            
        $.fancybox.showActivity();
    });                                 
});          	
