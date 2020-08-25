$(document).ready(function() {
    $('#fld_company_name').focus();
    setInterval(function(){
        if($('#fld_sallutation').val()=='' && $('#fld_cp_firstname').val()=='' && $('#fld_cp_lastname').val()==''){
            $('#cp-unknown').attr('checked',true);
        }else{
            $('#cp-unknown').attr('checked',false);
        }
    },100);
    function copyAddress(){
        $('#shipstreet').val($('#billstreet').val());
        $('#shipsnum').val($('#billnum').val());
        $('#shippost').val($('#billpost').val());
        $('#shipcity').val($('#billcity').val());
        $('#shipcountry').val($('#billcountry').val());         
    }
    $('.bill').keyup(function(e){
       if($('#copy-from-billing').is(':checked')) 
            copyAddress();
    });
    $('#copy-from-billing').change(function(e){
        if($(this).is(':checked')){
            copyAddress();           
        }else{
           if($('#billstreet').val()==$('#shipstreet').val())
                $('#shipstreet').val('');
           if($('#billnum').val()==$('#shipsnum').val())
                $('#shipsnum').val('');           
           if($('#shippost').val()==$('#billpost').val())
                $('#shippost').val('');            
           if($('#shipcity').val()==$('#billcity').val())
                $('#shipcity').val('');     
           if($('#shipcountry').val()==$('#billcountry').val())
                $('#shipcountry').val('');                                          
        }
    });
    $('#fld_save').click(function(e){
        e.preventDefault();
        $.post(root + '/relations/edit.html?ajax=1&_do=store',
            $('#customer_edit').serialize(),function(data){
                
                $('.stored-ok').css('display','inline');
                
                $('.add_customer').css('display','none');
                $('.edit_customer').css('display','block');
                
                $('#fld_id').val(data.id);                
                $('#customer_id').html(data.id);
                

                $('#fld_website').val(data.website);
                setTimeout(function(){
                    $('.stored-ok').css('display','none');
                    if($('#fld_view').val()=='picker')
                        parent.addCustomer($('#fld_id').val());
                    else{
                        parent.$.updateClientTable(false);
                        $.fancybox.close();
                    }
                                                                                   
                },2000);
            },'json');            
    });
    $('#ziplookup').click(function(e){
       $.fancybox.showActivity();
       e.preventDefault();
       $.post(root+'/ziplookup.php',{zip:$('#billpost').val()},function(response){
           $.fancybox.hideActivity();
           $('#billcity,#shipcity').val(response.city);
           $('#billcountry,#shipcountry').val(response.country);

       },'json');
    });
});