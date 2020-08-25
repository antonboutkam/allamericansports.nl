$(document).ready(function(){
    $('#fill_in_checkout_form').live('click',function(e){
        e.preventDefault();
        $('#cp_firstname').val('Anton');
        $('#cp_lastname').val('Boutkam');
        $('#company_name').val('Nui Boutkam');
        $('#email').val('anton@nui-boutkam.nl');
        $('#phone').val('0297567716');
        $('#billing_street').val('Amstelstraat');
        $('#billing_number').val('1');
        $('#billing_postal').val('1421AW');
        $('#billing_city').val('Uithoorn');
        $('#billing_country').val('Nederland');        
    });
    
    $('#toggle_debugpanel').live('click',function(e){
        e.preventDefault();
        newState = '';
        if($('#debug_panel_container').css('height')=='0px'){
            newState = 'open';
            $('#debug_panel_container').css('height','240px');
        }else{
            newState = 'closed';
            $('#debug_panel_container').css('height','0px');
        }                
        
        $.post('/debuginfo.php',{_do:'set_state',new_state:newState});
    });    
    $('.editfile').live('click',function(e){
        e.preventDefault();
        $('#preview_panel').attr('src','http://codemirror.nuidev.nl/mode/htmlmixed/?file='+code_base+'/templates'+$(this).attr('href'));                        
    });
    
    
    $('.panelbtn').live('click',function(e){
        e.preventDefault();
        showElement = $(this).attr('id').replace('_btn','');        
        $('.consoles').css('display','none');
        $('.panelbtn').removeClass('active');
        $(this).addClass('active');
        $.post(request_uri,{_do:'set_activetab',active_tab:showElement,ajax:1});
        $('#'+showElement).css('display','block');                
    });
    
    $('#'+active_tab+'_btn').addClass('active').trigger('click');
});