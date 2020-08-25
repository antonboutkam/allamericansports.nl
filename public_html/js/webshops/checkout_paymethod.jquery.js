$(document).ready(function(){
        
   $('radio:first').focus();
   /*
    $('.paymethod').live('change',function(e){                
        if($(this).val()){         
            $('#pmethodfld').val($(this).val());
            $('#paybank').css('display',($('#pmethodfld').val()==1)?'inline':'none');
        }
    }); 
   */
  
  
   $('.submit_bank').live('click',function(e){                         
        e.preventDefault();
        $.fancybox.showActivity();
        $.itemSelected = false;
   
        $('.paymethod').each(function(i,e){
            if($(e).attr('checked')){
                if($('select',$(this).parent()).attr('id')){
                    if(!$('select',$(this).parent()).val()){
                        markInvalid($('select',$(this).parent()).attr('id'));
                    }else{
                        $.fancybox.hideActivity();
                        $(this).closest('form').submit();
                        $.itemSelected = true;
                        return false;
                    }                                                                                        
                }else{
                    $.fancybox.hideActivity();
                    $.itemSelected = true;
                    $(this).closest('form').submit();
                    return false;
                }                                                    
            }         
            
        });
        
        if(!$.itemSelected){
            $.fancybox.hideActivity();
            alert('Selecteer alstublieft een betaalmethode');
        }                
    });

});
