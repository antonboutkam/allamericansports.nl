$(document).ready(function(){    
    $('.submitroundup').live('click',function(e){
        e.preventDefault();
        if(!$('#agreeterms').is(':checked')){
            alert($('#agree_terms_err').html());
        }else{            
            $('#onestepform').submit();            
        }
    });       
    
                 
})