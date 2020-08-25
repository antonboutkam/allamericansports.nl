
$(document).ready(function() {
    function clearForm(){
        // Clears the form
        $('.wareh').val('');        
    }
    $('.hq').live('click',function(e){
        $.fancybox.showActivity();
        $.post(request_uri,
            {   '_do'           :   'set_hq',
                'ajax'          :   1,
                'location'      :   $('.hq:checked').val()
            },function(data){
                $.fancybox.hideActivity();
            },'json');
    });
    $('.add_location').click(function(e){
        clearForm();
        e.preventDefault();                        
        $('.location_editor').css('display','block');
        $('.configuration_editor').css('display','none');
    });
    $('.delete_location').live('click',function(e){
       e.preventDefault();       
       $.post(request_uri,
            {   '_do'           :   'delete-warehouse',
                'ajax'          :   1,
                'location'      :   $(this).attr('rel')
            },function(data){
                if(!data.can_delete)
                    $('#has_locations_err').css('display','block');
                else{                   
                   $('#warehouse_removed').css('display','block');
                   window.location = window.location;
                }                                                                              
            },'json');                     
    });        
    $('.delete_rack').live('click',function(e){
       e.preventDefault();
       $.fancybox.showActivity();
       $.post(request_uri,
            {   '_do'           :   'delete-rack',
                'ajax'          :   1,
                'id'            :   $('#fld_id').val(),
                'rack'          :   $(this).attr('rel')
            },function(data){
                $.fancybox.hideActivity();
                if(!data.can_delete){
                    alert($('#has_products_err').html());
                }else{
                    $('#rack'+data.rack).remove();
                    alert($('#location_removed').html());
                }                                                                               
            },'json');                     
    });       
    $('.edit_location').click(function(e){
        $.fancybox.showActivity();
        e.preventDefault();
        $.post(request_uri,
                {
                    'id'        :   $(this).attr('rel'),                    
                    '_do'       :   'get-warehouse',
                    'ajax'      :   1                
                },
                function(data){     
                    $('#warehouse_config_tbl').html(data.warehouse_config_tbl);
                    $.each(data.location, function(k, v) {                                                   
                        $('#fld_'+k).val(v);                                                                                           
                    });
                    $.fancybox.hideActivity();
                    $('.configuration_editor').css('display','block');
                    $('.location_editor').css('display','block');
                },'json');        
    });
    $('.fld_cancel').click(function(e){
        clearForm();
        e.preventDefault();
        $('.location_editor').css('display','none');
        $('.configuration_editor').css('display','none');
    });
});
