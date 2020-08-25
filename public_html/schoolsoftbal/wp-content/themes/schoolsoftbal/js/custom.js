jQuery(document).ready(function() {	

	setBlockHeight();
	
	
	if($('body').hasClass('home')){
	 $('.homeimg').backstretch("http://www.allamericansports.nl/schoolsoftbal/wp-content/themes/schoolsoftbal/images/schoolsoftbal.jpg");
	 $('.groot-assortiment').backstretch("http://www.allamericansports.nl/schoolsoftbal/wp-content/themes/schoolsoftbal/images/groot-assortiment.jpg");
	 $('.block-large').backstretch("http://www.allamericansports.nl/schoolsoftbal/wp-content/themes/schoolsoftbal/images/austin-fasting.jpg");

	} else if($('body').hasClass('over-ons')) {
	 $('.backstretch').backstretch("http://www.allamericansports.nl/schoolsoftbal/wp-content/themes/schoolsoftbal/images/austin-fasting.jpg");
	} else if($('body').hasClass('contact')) {
	 $('.backstretch').backstretch("http://www.allamericansports.nl/schoolsoftbal/wp-content/themes/schoolsoftbal/images/showroom-schoolsoftbal.jpg",
	 {
		 centeredY: false,
	 });
	};
	
	 
	 
});
jQuery(window).resize(function() {	

	setBlockHeight();
  
});




/*
function setBlockHeight(){
	$('.block').height($('.block').width());
	$('.homeimg').height($('.block').width());
	$('.nieuwscontainer').height(($('.block').width()*2)+$('.marge-calc').width());
}
*/

function setBlockHeight(){
	$('.block').css('height','auto');
	i = 0;
	blockHeight = 0;
	
	if($(window).width()>1100 ) {
		
		while (i < $('.block').length ){
			
			if ($('.block').eq(i).height() > blockHeight){
				blockHeight = $('.block').eq(i).height();
			};
			i++;
		}
		
		if($('body').hasClass('home') && blockHeight < $('.block').width()){
			blockHeight = $('.block').width(); 
		}
		
		
		$('.block').height(blockHeight);
		$('.homeimg').height(blockHeight);
		$('.nieuwscontainer').height(blockHeight);
	}
}

/*
function setProductHeight(){
	$('.prod-image').css('height','auto');
	i = 0;
	blockHeight = 0;
	
	while (i < $('.prod-image').length ){
		
		if ($('.prod-image').eq(i).height() > blockHeight){
			blockHeight = $('.prod-image').eq(i).height();
		};
		i++;
	}
	
	$('.prod-image').height(blockHeight);
}
*/


function showThis(id){
	if(jQuery('#prod-desc'+id).is(":visible")){
	} else {		jQuery('.prod-description:visible').hide();
		jQuery('.prod-image').removeClass('activeClick');

		jQuery('.prod-image').removeClass('activeClick');
		jQuery('.prod-description:visible').hide();
		jQuery('#prod-desc'+id).show();
		
		jQuery('#prod-click'+id).addClass('activeClick');
	    $('html, body').animate({
	        scrollTop: $('#prod-desc'+id).offset().top
	    }, 500);
    
    	}
}


function toggleCta() {
  	$('.cta-container').toggleClass('cta-active');
}


