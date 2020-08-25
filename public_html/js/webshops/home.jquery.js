
var searchTimeout;

$(document).ready(function(){        	
    $('.search').live('keyup',function(){
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function(){
            $.fancybox.showActivity();                    
            send = {query:$('#search').val(),ajax:1};
            $.post(request_uri,send,function(data){
                $.fancybox.hideActivity();                    
                $('#notebook_models_container').html(data.notebook_list);                
            },'json');        
        },200)
    });    
    if(!show_ribbon){
        $('.ribbon').remove();
    }

    $('#sliderx').nivoSlider({
            effect: banner_effect, // Specify sets like: 'fold,fade,sliceDown'
            slices: 11, // For slice animations
            boxCols: 8, // For box animations
            boxRows: 4, // For box animations
            animSpeed:1000, // Slide transition speed
            pauseTime:5000, // How long each slide will show
            startSlide:0, // Set starting Slide (0 index)
            directionNav:true, // Next & Prev navigation
            directionNavHide:false, // Only show on hover
            controlNav:true, // 1,2,3... navigation
            controlNavThumbs:false, // Use thumbnails for Control Nav
            controlNavThumbsFromRel:false, // Use image rel for thumbs
            controlNavThumbsSearch: '.jpg', // Replace this with...
            controlNavThumbsReplace: '_thumb.jpg', // ...this in thumb Image src
            keyboardNav:true, // Use left & right arrows
            pauseOnHover:true, // Stop animation while hovering
            manualAdvance:false, // Force manual transitions
            captionOpacity:0.8, // Universal caption opacity
            prevText: '', // Prev directionNav text
            nextText: '', // Next directionNav text
            beforeChange: function(){
                $('#banner_overlay .layer').fadeOut(200);             
            }, // Triggers before a slide transition
            afterChange: function(){
                currentSlide = parseInt($('#sliderx').data('nivo:vars').currentSlide);                                                
                $.skipTo(currentSlide+1);
               // currentSlide = $('#sliderx').data('nivo:vars').currentSlide;
                // $.skipTo(currentSlide); 
            }, // Triggers after a slide transition
            slideshowEnd: function(){}, // Triggers after all slides have been shown
            lastSlide: function(){}, // Triggers when last slide is shown
            afterLoad: function(){} // Triggers when slider has loaded
        }
    );
    

});
/*
* Teksten bij de Nivo slider
*/
$.skipTo = function(c){         
    $('#banner_overlay .layer').fadeOut(100);                        
    $('#banner_overlay .layer:nth-child('+c+')').fadeIn(100);                                        
}
$.current = null;
/*
$(document).ready(function(){
    setTimeout(function(){
        window.location = window.location; 
    },10000)
   */ 
      
    //$('#banner_overlay .layer:nth-child(0)').fadeIn(400);
    /*    
  
    $.current = $('a.nivo-control.active').html();
    $.skipTo($.current);
    setInterval(function(){
        newCurrent = $('a.nivo-control.active').html();
        if(newCurrent!=$.current){
            $.current = newCurrent;
            $.skipTo(newCurrent);
        }
    },100);
    
});
*/
/*
* Einde teksten bij de Nivo slider
*/
