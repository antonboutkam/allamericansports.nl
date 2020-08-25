$(document).ready(function(){
   $('#fld_locale').change(function(){    
        $('#fld_do').val('change_language');
        $('#frm_brandbox_form').submit();
   });
   
   $('.delete_lnk').click(function(e){
        if(!confirm('Weet u zeker dat u deze brandbox wilt verwijderen?')){
            e.preventDefault();    
        }                    
   });              
   
   $('#fld_save_brandbox').click(function(e){
        position = $('#fld_leftright').val();
        console.log(position);
        positionLbL = position=='left'?'linker':'rechter';
        if($('.brandbox_'+position).length){
            if(!confirm('Hiermee overschrijft u de huidige '+positionLbL+' brandbox, weet u het zeker?')){
                e.preventDefault();        
            }
        }                        
   });
});