jQuery(document).ready(($) => {
    $('.vmb-reviews').slick({
        dots: true,
        infinite: true,
        speed: 300,
        slidesToShow: 2,
        slidesToScroll: 1,
        adaptiveHeight: true,
        responsive: [
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                }
            }
        ],
        nextArrow: '<i class="fa fa-solid fa-chevron-right slick-next"></i>',
        prevArrow: '<i class="fa fa-solid fa-chevron-left slick-prev"></i>'
    });

    $('.vmb-specials').slick({
        dots: true,
        infinite: true,
        speed: 300,
        slidesToShow: 3,
        slidesToScroll: 1,
        adaptiveHeight: true,
        responsive: [
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ],
        nextArrow: '<i class="fa fa-solid fa-chevron-right slick-next"></i>',
        prevArrow: '<i class="fa fa-solid fa-chevron-left slick-prev"></i>'
    });

    $('.specials-preview').each(function() {
        let list = $(this).find('.specials-list');

        $(this).find('.show-trigger').click(function() {
            let href = $(this).data('href');
            let btn = $(this);

            list.slideDown('fast');
            btn.removeClass('show-trigger').html('View All Specials');

            setTimeout(function() {
                btn.attr('href', href);
            }, 1000);
        });
    });

    if(jQuery('.filters')) {

        /**
         * Handle URL parameter-based filtering
         * 
         * This code checks for a 'category' parameter in the URL and activates
         * the corresponding filter if found. It works with URLs containing hash fragments
         * (e.g., /page/#section?category=value) or without (e.g., /page?category=value).
         * Includes validation and debugging to help troubleshoot issues.
         */
        let urlSearch = window.location.search;
        let hashSection = '';
        
        // Handle URLs with hash fragments like /page/#section?category=value
        if (window.location.hash) {
            if (window.location.hash.includes('?')) {
                const hashParts = window.location.hash.split('?');
                hashSection = hashParts[0]; // Store the section part (e.g., #section)
                if (hashParts.length > 1) {
                    urlSearch = '?' + hashParts[1];
                }
            } else {
                // If hash exists but no parameters, just store the hash for scrolling
                hashSection = window.location.hash;
            }
        }
        
        const urlParams = new URLSearchParams(urlSearch);
        const categoryParam = urlParams.get('category');
        
        console.log('URL:', window.location.href);
        console.log('URL Search Part:', urlSearch);
        console.log('Hash Section:', hashSection);
        console.log('URL Parameters:', urlParams.toString());
        console.log('Category Parameter:', categoryParam);
        
        // Only proceed if categoryParam exists and is not null or empty
        if (categoryParam && categoryParam.trim() !== '') {
            // Remove active class from all filter items
            jQuery('.filters li').removeClass('active');
            console.log('Removed active class from all filters');
            
            // Generate a valid selector using the slugified category name
            const categorySlug = window.generateSlug ? window.generateSlug(categoryParam) : categoryParam.toLowerCase().replace(/\s+/g, '-');
            const filterSelector = '#category-' + categorySlug;
            
            console.log('Processed category slug:', categorySlug);
            console.log('Looking for filter:', filterSelector);
            
            // Find the filter with matching category
            const targetFilter = jQuery(filterSelector);
            console.log('Filter found:', targetFilter.length > 0);
            
            // If the filter exists, add active class and trigger click
            if (targetFilter.length) {             
                targetFilter.addClass('active');
                console.log('Added active class to', filterSelector);
                targetFilter.trigger('click');
                console.log('Triggered click on', filterSelector);
            } else {
                console.log('Filter not found. Falling back to default filter.');
                // Optionally activate the default filter (first one)
                jQuery('.filters li:first-child').addClass('active');
            }
        } else {
            console.log('No valid category parameter found in URL');
        }
        
        // Scroll to the hash section if it exists
        if (hashSection) {
            setTimeout(function() {
                // Use jQuery's animate to smoothly scroll to the element
                jQuery('html, body').animate({
                    scrollTop: jQuery(hashSection).offset().top - 100 // Offset by 100px to account for fixed headers
                }, 400);
                console.log('Scrolling to section:', hashSection);
            }, 300); // Delay slightly to ensure filters have been applied
        }

        jQuery('.filters li').click(function(){
            jQuery('.filters li').removeClass('active');
            
            jQuery(this).addClass('active');
            var slug = jQuery(this).data('slug');
    
            if(slug == '*') {
              jQuery('.vmb-filter-item').each(function(){
                jQuery(this).fadeIn(800);
              });
            } else {
              jQuery('.vmb-filter-item').filter(function(){
                  if(jQuery(this).data('category') == slug) {
                    jQuery(this).fadeIn(800);
                  } else {
                    jQuery(this).fadeOut(200);
                  }
                });
            } 
        }); 
    
        setTimeout(function() {
          jQuery('.filters li.active').click();
        },500);
    }
});
