function moveProductsToLocation(properties,newLocationId){
    $.fancybox.showActivity();
    $.post(request_uri+'?'
        +properties
        +'&newLocationId='+newLocationId
        +'&_do=move'
        +'&ajax=1'
        +'&location='+$('#flirter_location').val(),{},function(data){                        
            $('#searchresult').html(data.stock_tbl);
            $.fancybox.hideActivity();     
            $.fancybox.close();   
    },'json');
}
var sort;
var updatetimer = null;

function updateTable(resetPage){
    $.fancybox.showActivity();
    if(resetPage!=undefined)
        $('#current_page').val(resetPage);
    if(updatetimer!=null)
        clearTimeout(updatetimer);
    updatetimer = setTimeout(function(){
    send = {    'location'      :   $('#flirter_location').val(),
                'ajaxresult'    :   1,
                'query'         :   $('#fld_search').val(),
                'defaultquery'  :   $('#fld_search').attr('title'),
                'current_page'  :   current_page,
                'view'          :   view,
                'sort'          :   sort}
    $.post(request_uri,
        send,function(data){

            $('#export_location').attr('href',root+'/export.php?type=stock&location='+data.location);
            $('#export_location').html(data.location_name);

            $('#searchresult').html(data.stock_tbl);
            $.bindFancybox();
            $.fancybox.hideActivity();
        },'json');
    },333);
}
$(document).ready(function() {
    var currRemoveId = null;
    var currProductId = null;
    var currRemoveQt = null;
    
    $('.remove-products').live('click',function(e){
        e.preventDefault();
        action = ($('img',this).attr('src').indexOf('save')==-1)?'modify':'save';
        $('.remove-products').children('img').attr('src',root+'/img/icons/down-icon-16x16.png');
        
        if(action=='modify'){                    
            $(this).find('img').attr('src',root+'/img/icons/save-icon-16x16.png');            
            // Restore state last edit
            if(currRemoveId)
                $('#quantity'+currRemoveId).html(currRemoveQt);
            
            currRemoveId = $(this).attr('rel');
            currProductId= $(this).attr('id').replace('product','');
            currRemoveQt = $('#quantity'+currRemoveId).html()
            dropdown = '<select id="remove-count" style="width:70px" name="quantity['+currRemoveId+']">';
            y=0;
            removeTxt = '';
            for(x=(currRemoveQt-1);x>=0;x--){
                y+=1;
                selected    = (x==currRemoveQt)?'selected="selected"':'';
                remove      =  '-'+(currRemoveQt-x);
                dropdown    = dropdown + '<option value="'+remove+'" '+selected+'>'+x+' / '+(remove)+'</option>';
            }             
            dropdown = dropdown + '</select>';
            dropdown = dropdown + $('#dd-reason').html().replace('id="fillin"','id="reason"');
                                                       
            $('#quantity'+currRemoveId).html(dropdown); 
        }else{
            if($('#reason').val()==''){                
                $(this).find('img').attr('src',root+'/img/icons/save-icon-16x16.png');
                return alert($('#err-reason').html());            
            }                        
            $.fancybox.showActivity();
                   
            $.post(request_uri,
            {   '_do'           :   'remove',
                'ajax'          :   '1',
                'locationId'    :   currRemoveId,
                'productId'     :   currProductId,
                'reason'        :   $('#reason').val(),
                'quantity'      :   $('#remove-count').val()
            },function(data){
                $.fancybox.hideActivity();
                $('#searchresult').html(data.stock_tbl);                
            },'json');
        }                
    });
    if($('#fld_search').val()=='')
        $('#fld_search').val($('#fld_search').attr('title'));    
               
    $('.sort').live('click',function(e){
        e.preventDefault();
        sort = $(this).attr('rel');
        updateTable();         
    });            
    $('.reload').live('click',function(e){
        e.preventDefault(); 
        updateTable();          
    });
    $('.paginate').live('click',function(e){
        e.preventDefault();
        current_page = $(this).attr('rel');
        updateTable();
    });
    $('#flirter_location').live('change',function(){
        updateTable(true); 
    });
    $('#fld_search').live('keyup',function(e){
        updateTable(1);
    });                            
});