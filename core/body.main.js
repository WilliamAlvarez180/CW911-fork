jQuery(document).one("AssetsLoaded", function (e) {

	"use strict";

	[].slice.call( document.querySelectorAll( 'select.cs-select' ) ).forEach( function(el) {
		new SelectFx(el);
	});

	jQuery('.selectpicker').selectpicker;


	

	jQuery('.search-trigger').on('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		jQuery('.search-trigger').parent('.header-left').addClass('open');
	});

	jQuery('.search-close').on('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		jQuery('.search-trigger').parent('.header-left').removeClass('open');
	});

	jQuery('.equal-height').matchHeight({
		property: 'max-height'
	});

	// var chartsheight = jQuery('.flotRealtime2').height();
	// jQuery('.traffic-chart').css('height', chartsheight-122);


	// Counter Number
	jQuery('.count').each(function () {
		jQuery(this).prop('Counter',0).animate({
			Counter: jQuery(this).text()
		}, {
			duration: 3000,
			easing: 'swing',
			step: function (now) {
				jQuery(this).text(Math.ceil(now));
			}
		});
	});


	 
	 
	// Menu Trigger
	jQuery('#menuToggle').on('click', function(event) {
		var windowWidth = jQuery(window).width();   		 
		if (windowWidth<1010) { 
			jQuery('body').removeClass('open'); 
			if (windowWidth<760){ 
				jQuery('#left-panel').slideToggle(); 
			} else {
				jQuery('#left-panel').toggleClass('open-menu');  
			} 
		} else {
			jQuery('body').toggleClass('open');
			jQuery('#left-panel').removeClass('open-menu');  
		} 
			 
	}); 

	// Load Resize 
	jQuery(window).on("load resize", function(event) { 
		var windowWidth = jQuery(window).width();  		 
		if (windowWidth<1010) {
			jQuery('body').addClass('small-device'); 
		} else {
			jQuery('body').removeClass('small-device');  
		} 
		
	});
  
 
});

function initFullScreen() {
    jQuery('[data-bs-toggle="fullscreen"]').on("click", function (e) {
        e.preventDefault();
        jQuery('body').toggleClass('fullscreen-enable');
            if (!document.fullscreenElement && /* alternative standard method */ !document.mozFullScreenElement && !document.webkitFullscreenElement) {  // current working methods
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                }
            } else {
                if (document.cancelFullScreen) {
                    document.cancelFullScreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitCancelFullScreen) {
                    document.webkitCancelFullScreen();
                }
            }
        });
    document.addEventListener('fullscreenchange', exitHandler);
    document.addEventListener("webkitfullscreenchange", exitHandler);
    document.addEventListener("mozfullscreenchange", exitHandler);
    function exitHandler() {
        if (!document.webkitIsFullScreen && !document.mozFullScreen && !document.msFullscreenElement) {
            jQuery('body').removeClass('fullscreen-enable');
        }
    }
}

initFullScreen();