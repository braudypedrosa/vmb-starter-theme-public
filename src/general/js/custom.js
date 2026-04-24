jQuery(document).ready(function($) {

    function generateMapId() {
        return Math.floor(1000 + Math.random() * 9000);
    }

    // Access localized Mapbox settings
    const mapboxToken = vmb_public_scripts_localize.mapbox_token;
    const mapboxStyle = vmb_public_scripts_localize.mapbox_style;
    const mapboxMarkerColor = vmb_public_scripts_localize.mapbox_marker_color;
    
    const zoom = 16;

    // Mapbox API init
    mapboxgl.accessToken = mapboxToken;

    // Add map controls
    const zoomControl = new mapboxgl.NavigationControl({
        showZoom: true,
    });

    const screenWidth = window.innerWidth;

    console.log('Screen Width: ' + screenWidth);

    // Mapbox Solo
    setTimeout(function() {
        jQuery('.mapbox-solo').each(function() {
            const mapID = generateMapId();
            console.log('Map ID: ' + mapID);
    
            let longitude = jQuery(this).data('lng') ? jQuery(this).data('lng') : -78.829557;
            let latitude = jQuery(this).data('lat') ? jQuery(this).data('lat') : 33.732044;
            let address = jQuery(this).data('address') ? jQuery(this).data('address') : '';

            jQuery(this).attr('id', 'vmbmap-' + mapID);
    
            console.log('Map Longitude: ' + longitude);
            console.log('Map Latitude: ' + latitude);
            console.log('Map Zoom: ' + zoom);
    
            // initialize map
            var vmbmap = new mapboxgl.Map({
                container: 'vmbmap-' + mapID,
                style: mapboxStyle,
                center: [longitude, latitude],
                zoom: zoom
            });
    
            // add map controls
            vmbmap.addControl(zoomControl);
            vmbmap.addControl(new mapboxgl.FullscreenControl());
    
            // assign marker
            const marker = new mapboxgl.Marker()
                .setLngLat([longitude, latitude])
                .addTo(vmbmap);
        });
    }, 1000);

    console.log('Custom.js loaded');
    	// book now button
    $('.book-now-btn').each(function(){
        const unitID = $(this).attr("unittypeid");
        let buttonLink = $(this).find(".elementor-button-link");

        buttonLink.attr('href', buttonLink.attr('href') + "?unitTypeId=" + unitID);
    });
    
    // dynamic phone numbers
    const params = new URLSearchParams(window.location.search);
    const nck = params.get('NCK');

    // Check if nck is valid before proceeding
    if (!nck || !window.dynamicPhoneNumbers || !window.dynamicPhoneNumbers.includes(nck)) {
        console.log('NCK parameter is missing, empty, or not in the allowed list');
    } else {
        console.log('NCK parameter is valid');
        // Format the phone number
        const formattedPhone = nck.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        console.log('Dynamic Phone:', nck);
        console.log('Formatted phone number:', formattedPhone);

        setTimeout(function() {
			// Check if any elements with the class exist
			const phoneElements = $('.dynamic-phone-number');
			console.log('Found dynamic phone elements:', phoneElements.length);

			if (phoneElements.length === 0) {
				console.warn('No dynamic-phone-number elements found in the DOM');
			}

			phoneElements.each(function(index) {
				console.log(`Updating phone element ${index}:`, this);
				const element = $(this);

				// Store original values for debugging
				const originalHref = element.attr('href');
				const originalText = element.text();

				// Update the element
				element.attr('href', 'tel:' + nck);
				element.text(formattedPhone);

				console.log(`Element ${index} updated:`, {
					from: { href: originalHref, text: originalText },
					to: { href: 'tel:' + nck, text: formattedPhone }
				});
			});

			console.log('Dynamic phone numbers update attempt completed');
		}, 1000); // 1 second delay to ensure elements are ready
    }

    
    // See More Button
    $('.see-more-btn').each(function() {
        console.log('See More Button');
        
        // Add click event listener to each 'see-more-btn'
        $(this).click(function() {
            // Get the target ID, toggle text, and initial text from data attributes
            var target = $(this).data('target_id');
            var toggle_text = $(this).data('toggle_text');
            var initial_text = $(this).data('initial_text');

            // Toggle the visibility of the target element
            $('#' + target).slideToggle();
            // Toggle the class 'hidden-content-open' on the button
            $(this).toggleClass('hidden-content-open');
            
            // Change the button text based on its state
            if ($(this).hasClass('hidden-content-open')) {
                $(this).html(toggle_text + ' <i class="fa-solid fa-chevron-up">');
            } else {
                $(this).html(initial_text + ' <i class="fa-solid fa-chevron-down">');
            }
        });
    });
    // See More Button End
});
