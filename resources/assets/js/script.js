/*------------------------------------------------------------------
[Master Scripts]

Project:    CropIt template
Version:    1.0

[Components]
	- Preloader
	- Full scren slider
	- Full screen section
	- Review slider
	- Instagram carousel
	- Info section
	- Header search
	- Fixed header
	- Mobile menu
	- Tabs
	- Icon box auto height
	- Team grig
	- Open side panel
	- Close side panel
	- Content filter
	- Height icon box 2
	- Pricing height 
	- Horisontal gallery
	- Animation
	
-------------------------------------------------------------------*/

/*------------------------------------------------------------------
[ Preloader ]
*/
jQuery(window).on('load', function () {
    var $preloader = jQuery('#page-preloader'),
        $spinner   = $preloader.find('.spinner');
    $spinner.fadeOut();
    $preloader.delay(350).fadeOut('slow');
});

jQuery( document ).ready(function() {
	"use strict";
	
	/*------------------------------------------------------------------
	[ equalHeight ]
	*/
	function equalHeight(group) {
        if(jQuery(window).width() >= '768') {
			var tallest = 0;
	       	jQuery(group).each(function() {
	            var thisHeight = jQuery(this).css('height', "").height();
	            if(thisHeight > tallest) {
	                tallest = thisHeight;
	            }
	        });
	        jQuery(group).height(tallest);
	    } else {
	    	jQuery(group).height('auto');
	    }
    }

    /*------------------------------------------------------------------
	[ Retina ready ]
	*/

	// if (window.devicePixelRatio > 1) {
	// 	var lowresImages = jQuery('.retina-img');

	// 	lowresImages.each(function(i) {
	// 		var lowres = jQuery(this).attr('src');
	// 		var highres = lowres.replace(".", "@2x.");
	// 		jQuery(this).attr('src', highres);
	// 	});
	// }

    /*------------------------------------------------------------------
	[ Full screen section ]
	*/

	jQuery(window).on("load resize", function(){
		jQuery('.full-screen:not(.fixed-height)').css('height', jQuery(window).outerHeight()-jQuery('.header').outerHeight());
	});

    /*------------------------------------------------------------------
	[ Fixed header wrap ]
	*/

	jQuery(window).on("load resize scroll", function(){
		if(jQuery(window).width() > '990') {
			if ( jQuery(document).scrollTop() > jQuery('.top-header').outerHeight() ) {
				jQuery('.header-wrap').addClass('fixed');
			} else {
				jQuery('.header-wrap').removeClass('fixed');
			}
		} else {
			jQuery('.header-wrap').removeClass('fixed');
		}
	});

    /*------------------------------------------------------------------
	[ Team carousel ]
	*/

	if(jQuery('.team-carousel').length > 0){
		jQuery('.team-carousel').owlCarousel({
			loop:true,
			items:3,
			margin: 30,
			nav: true,
			dots: true,
			autoplay: true,
			autoplayTimeout: 5000,
			autoplayHoverPause: true,
			navClass: ['owl-prev icon-font icon-left-arrow','owl-next icon-font icon-right-arrow'],
			navText: false,
			dotsEach: true,
			responsive:{
				0:{
					items:1
				},
				600:{
					items:2
				},
				1000:{
					items:3
				}
			}
		});
	}

    /*------------------------------------------------------------------
	[ Testimonials carousel ]
	*/

	if(jQuery('.testimonials-slider').length > 0){
		jQuery('.testimonials-slider').owlCarousel({
			loop:true,
			items:1,
			nav: true,
			dots: false,
			//autoplay: true,
			autoplayTimeout: 5000,
			autoplayHoverPause: true,
			navClass: ['owl-prev icon-font icon-left-arrow','owl-next icon-font icon-right-arrow'],
			navText: false,
			dotsEach: true
		});
	}

    /*------------------------------------------------------------------
	[ Animation ]
	*/

	jQuery(window).on("load scroll", function(){
		jQuery('.animateNumber').each(function(){
			var num = jQuery(this).attr('data-num');
			
			var top = jQuery(document).scrollTop()+(jQuery(window).height());
			var pos_top = jQuery(this).offset().top;
			if (top > pos_top && !jQuery(this).hasClass('active')) {
				jQuery(this).addClass('active').animateNumber({ number: num },2000);
			}
		});
		jQuery('.animateProcent').each(function(){
			var num = jQuery(this).attr('data-num');
			var percent_number_step = jQuery.animateNumber.numberStepFactories.append('%');
			var top = jQuery(document).scrollTop()+(jQuery(window).height());
			var pos_top = jQuery(this).offset().top;
			if (top > pos_top && !jQuery(this).hasClass('active')) {
				jQuery(this).addClass('active').animateNumber({ number: num, numberStep: percent_number_step },2000);
				jQuery(this).css('width', num+'%');
			}
		});
	});

    /*------------------------------------------------------------------
	[ Equal block height ]
	*/

	jQuery(window).on("load resize", function(){
		equalHeight(jQuery('.footer [class^="fw-col-"]'));
		equalHeight(jQuery('.icon-box-col'));
		equalHeight(jQuery('.doctor-col'));
	});

    /*------------------------------------------------------------------
	[ Open mobile side ]
	*/

	jQuery('.mobile-side-button').on("click", function(){
		if (jQuery(this).hasClass('active')) {
			jQuery(this).removeClass('active').parent().find('.mobile-side').removeClass('active');
		} else {
			jQuery(this).addClass('active').parent().find('.mobile-side').addClass('active');
		};
	});

    /*------------------------------------------------------------------
	[ Mobile menu ]
	*/

	jQuery(window).on("load resize", function(){
		if(jQuery(window).width() <= '990') {
			jQuery('.mobile-navigation .menu-item-has-children > a').on("click", function(){
				if(!jQuery(this).hasClass('active')) {
					jQuery(this).addClass('active').parent().children('.sub-nav').slideDown().siblings().children('.sub-nav').slideUp();
					return false;
				}
			});
		} else {
			jQuery('.mobile-side').removeClass('active');
		}
	});

    /*------------------------------------------------------------------
	[ Masonry gallery ]
	*/

	jQuery(window).on("load", function(){
		if(jQuery('.gallery-masonry').length > 0){
			jQuery('.gallery-masonry').isotope({
				itemSelector: '.gallery-item',
				masonry: {
					columnWidth: '.gallery-item'
				}
			});
		}
	});
});

