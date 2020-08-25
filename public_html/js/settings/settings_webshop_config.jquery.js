$(document).ready(function() {    
    

    var sendCostDropdown = $('#onchangeshowsendfields');    
    var sendMethodFields = $('tr.sendmethodfields');
    
    var showfields       = function(){

        displayRows = $('.disp'+sendCostDropdown.val())    
        sendMethodFields.css('display','none');    
        displayRows.css('display','table-row');    
        
    }
    
    
    sendCostDropdown.live('change',showfields);
    showfields();

    

});

/*
* Het stuk hieronder zorgt ervoor dat het systeem altijd een onderdeel van de instellingern open heeft.
* Het geopende onderdeel wordt naar de server gestuurd en opgeslagen in de user_settings tabel zodat hetzelfde blok open is wanneer de user weer terugkomt.
* Indien er geen blok geopend is dan openen we het eerste blok.
*/

$(document).ready(function(){
    function showOpenedSection(){
        $('.toggler').each(function(i,e){                                    
            if($(e).hasClass('close')){
                section = '.'+$(e).attr('data-section');
                $(section).css('display','none');
            }             
        });            
    }
    function storeVisibleSection(sectionName){
        send = {_do:'set_visible_section',visible_section:sectionName,ajax:1};
        $.post(request_uri,send,function(){},'json');
    }
    visible_section = $('#fld_visible_section').val();
    if(visible_section==''){
        visible_section = $('.toggler:first').attr('data-section');        
        storeVisibleSection(visible_section);
    }
    $('[data-section='+visible_section+']').removeClass('close').addClass('open');
    showOpenedSection();
    
    $('.toggler').live('click',function(e){
        e.preventDefault();
        $('tr').css('display','none');
        $('.toggler').parent().parent().css('display','table-row');
        current_section = $(this).attr('data-section');
        $('.'+$(this).attr('data-section')).css('display','table-row');
        storeVisibleSection(current_section);
    })
});