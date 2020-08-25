
function updateNotes(){
    $.post(root+'/home.php',{
        'ajax':1,
        '_do': 'update_notes'
    },function(data){
        $('#notes').html(data.notes);
    },'json')
}


$.updateChart = (function(elementId){
    if($('#recentsaleswidget').length > 0){
        grapher = "MSArea2D";
    	$(elementId).insertFusionCharts({
    		swfPath:      root+"/fc/Charts/",    
    		data:         root+"/xml/salesmonthdays.html",
    		dataFormat:   "URIData",
    		width:        "468",
            height:       "220"
    	});        
    }
});

$.widgetJsInit = function(){    
    $.updateChart();        
}

/*
* Gewoon hier de plugin schrijven
*/

function widgetSettings(){
    if($(".widgetsettings").css('display') == 'block'){
        $(".widgetsettings").css('display','none');
        $("#snelmenu_outside").css('display','block');
    }
    else {
        $(".widgetsettings").css('display','block');
        $("#snelmenu_outside").css('display','none');
    }

}
$.markWidgetOff = function(widget){
    widget.css('background-position','0px 0px');
    widget.attr('data-isenabled','0');
}
$.markWidgetOn = function(widget){
    widget.css('background-position','0px -148px');
    widget.attr('data-isenabled','1');    
}

function setWidgetVisibilityByName(name,visibleYN){    
    send = {widget_name:name,ajax:1,isEnabled:visibleYN,_do:'set_widget_enabled_by_name'};    
    $.post(request_uri,send,function(data){
        $.fancybox.hideActivity();
        $('.widget_content').html(data.widget_content);
        
        $.markWidgetOff($('#widget_button_'+name)); 
        $.widgetJsInit();           
    },'json');
        
}

function setWidgetVisibility(id,visibleYN){
    console.log(id,visibleYN)
    send = {widget_id:id,ajax:1,isEnabled:visibleYN,_do:'set_widget_enabled'};
    $.post(request_uri,send,function(data){
        $.fancybox.hideActivity();
        $('.widget_content').html(data.widget_content); 
        $.widgetJsInit();                   
    },'json');
        
}


(function( $ ) {
  $.fn.toggleOnOff = function(options) {    
    var buttonHeight = 37;
    $(this).html('');
    $(this).css({
        display:'inline-block',
        height:buttonHeight+'px',
        width:'100px',
        'background-position':'0px 0px',        
        'background-image':'url("/img/icons/buttonslide-100.png")'                            
    });
    $(this).each(function(i,e){        
        if($(e).attr('data-isenabled')==1){ //0 is ook true omdat het een string is!                
            $(e).css('background-position','0px -148px');
        }    
    });
            
    $(this).live('click',function(event){
        event.preventDefault();
        $.fancybox.showActivity();
       var currentElement = $(this);         
       var i = 0;
       var moveInterval = setInterval(function(){            
            i++;
            isEnabled = currentElement.attr('data-isenabled')=='0'?0:1;
            
            if(i>4){                                
                if(isEnabled){               
                    $.markWidgetOff(currentElement);                    
                }else{
                    $.markWidgetOn(currentElement);
                }  
                  
                clearInterval(moveInterval);                     
				setWidgetVisibility(currentElement.attr('data-id'),isEnabled?0:1);

                return;                           
            }
            
            currentHeight = currentElement.css('background-position').split(' ')[1].replace('px','');
            
            if(isEnabled){
                currentHeight =  parseInt(currentHeight)+parseInt(buttonHeight);                
            }else{
                currentHeight =  parseInt(currentHeight)-parseInt(buttonHeight);                
            }
            currentElement.css('background-position','0px '+currentHeight+'px');
       //    $(this).css('background-position-y','-37px');
       },20);
                
    });
    
   
  };
})(jQuery);



$(document).ready(function() {
    $('.resizewidget').live('click',function(e){
        e.preventDefault();
        $.fancybox.showActivity();
        itemToResize = $(this).parent().parent().parent();
        
        sizeClasses = $('.accepted_sizes',itemToResize).val().split(' ');
        
        classes = itemToResize.attr('class').split(' ');
        for(i=1;i<classes.length;i++){
            for(i2=0;i2<sizeClasses.length;i2++){                
                if(classes[i]==sizeClasses[i2]){
                    currentClass = classes[i];                    
                    if(i2==(sizeClasses.length-1)){
                       nextClass = sizeClasses[0];
                    }else{
                       nextClass = sizeClasses[i2+1]; 
                    } 
				   if(nextClass=='half'){
						newWidth = 470;
				   }else if(nextClass=='quarter'){
						newWidth = 225;
				   }else{
						newWidth = 960;
				   }
                   
                   $('.views',itemToResize).css('display','none');
				   
                   itemToResize.animate(
                    {'width':newWidth},
                    500,
                    'swing',function(){
                        
                        sendData = {_do:'set_widget_width',widget_name:$(itemToResize).attr('id'),new_size:nextClass,ajax:1};
                        $.post(request_uri,sendData,function(data){                                                        
                            $.fancybox.hideActivity();    
                        },'json');                        
                        $('.'+nextClass+'_view',itemToResize).css('display','block');      
                        
                        // input elementen etc ook de nieuwe klassee geven.
                        $('.'+currentClass,itemToResize).addClass(nextClass).removeClass(currentClass);
                        itemToResize.removeClass(currentClass);
                        itemToResize.addClass(nextClass);     
                        
                        
                    });
                   
                   
                                         

                }
            }
        }
        
    });
        

    $('.deletewidget').live('click',function(){
       console.log($(this).parent().parent().parent().attr('id')); 
       setWidgetVisibilityByName($(this).parent().parent().parent().attr('id'),0);
    });
      
    
    $('.showhidewidgets').live('click',function(){
        widgetSettings();
    });
    // initialiseerd alle widget specifieke code.
    $.widgetJsInit();
    
    // En hier aanroepen
    $('.toggleswitch').toggleOnOff();
    
    
	$("#sortable").sortable({
	   stop:function(){
	       $.fancybox.showActivity();
            var widgetOrder = new Array();
            $('.widget_content li').each(function(i,e){
                widgetOrder.push($(e).attr('id'));                
            });
            
            send = {ajax:1,_do:'set_widget_sorting',new_order:widgetOrder};
            $.post(request_uri,send,function(data){
                $.fancybox.hideActivity();                                    
            },'json');           	       
                                           
	   }
	});
    
    $('.delnote').live('click',function(e){
    $.fancybox.showActivity();
    e.preventDefault();
    $.post(root+'/home.php',{
        'ajax':1,
        '_do': 'delete_note',
        'id':$(this).attr('rel')
    },function(data){
        $.fancybox.hideActivity();
        $('#notes').html(data.notes);
    },'json')        
        
    })    

    $.updateChart('#sales_chart');       
});