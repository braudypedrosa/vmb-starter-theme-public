<?php

// add_shortcode( 'booking_engine', 'booking_engine_func' );
add_shortcode( 'vmb_gallery', 'vmb_gallery_func' );
add_shortcode( 'vmb_acf_gallery', 'vmb_acf_gallery_func' );
add_shortcode( 'vmb_rooms', 'vmb_rooms_func' );
add_shortcode( 'vmb_room_category', 'vmb_room_category_func' );
add_shortcode( 'vmb_room_amenities', 'vmb_room_amenities_func' );
add_shortcode( 'vmb_specials', 'vmb_specials_func' );
add_shortcode( 'vmb_reviews', 'vmb_reviews_func' );
add_shortcode( 'display_category', 'display_category_func' );
add_shortcode( 'vmb_mapbox_map', 'vmb_mapbox_map_func' );

// New shortcodes
add_shortcode( 'vmb_amenities', 'vmb_amenities_func' );
add_shortcode( 'vmb_amenities_category', 'vmb_amenities_category_func' );
add_shortcode( 'vmb_area_information', 'vmb_area_information_func' );
add_shortcode( 'vmb_accordion', 'vmb_faq_accordion_shortcode' );
add_shortcode( 'vmb_guestdesk_calendar', 'vmb_guest_calendar_func' );


// Booking Engine
/**
 * Shortcode function for displaying the booking engine.
 *
 * @return string HTML content for the booking engine.
 */
function booking_engine_func() {

	$sitename = vmb_get_field('site_name', 'option') ? vmb_get_field('site_name', 'option') : '';

    if (empty($sitename)) {
        return 'Site Name is required to display the booking engine. Please add a "Site Name" in the Site Settings.';
    }

	return '<guestdesk><img class="guestdesk-loading-icon" src="//media.guestdesk.com/sites/guestdesk/loading_icon.gif" alt="Loading Guestdesk" style="margin-left: auto; margin-right: auto;"/></guestdesk><script type="text/javascript" src="//media.guestdesk.com/sites/guestdesk/bundle.js"></script><link href="//media.guestdesk.com/sites/vacationmyrtlebeach_com/css/gd5-screen-new.css" rel="stylesheet"><script type="text/javascript">var domReady = function(callback) {document.readyState === "interactive" || document.readyState === "complete" ? callback() : document.addEventListener("DOMContentLoaded", callback);};var baseConfig = {"SiteName": 
"'.$sitename.'"};domReady(function() {new Guestdesk(baseConfig).loadBooking();});</script>';

}

// Gallery
/**
 * Shortcode function for displaying a gallery.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML content for the gallery.
 */
function vmb_gallery_func($atts) {

    $atts = shortcode_atts(array(
        'id' => null,
        'gallery_key' => 'gallery_images',
        'show_thumbnails' => true,
    ), $atts, 'vmb_gallery');

    global $post;
    $post_id = $post->ID;
    
    $gallery_key = $atts['gallery_key'];

    if ( isset($post_id) && get_post_type($post_id) === 'vmb_room') {
        $gallery_key = 'gallery';
    } else if (empty($atts['id'])) {
        return 'Gallery ID is required to display the gallery.';
    } else {
        $post_id = $atts['id'];
    }

    $gallery = vmb_get_field($gallery_key, $post_id);
    $name = get_the_title($post_id);
    $output = '';

    if (empty($gallery)) {
        return 'Gallery not found.';
    }

    $images_output = '';
    if (!empty($gallery)) {
        foreach ($gallery as $image) {
            $images_output .= '<div class="gallery-image"><img src="' . $image['url'] . '" alt="' . $name . '"/></div>';
        }
    }

    $output .= '<div class="gallery main-gallery">';
    $output .= $images_output;
    $output .= '</div>';

    $output .= '<div class="gallery main-gallery-nav">';
    $output .= $images_output;
    $output .= '</div>';

    $script = "<script>
        jQuery(document).ready(function() {
            jQuery('.main-gallery').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                fade: true,
                nextArrow: '<i class=\"fas fa-chevron-right slick-next\"></i>',
                prevArrow: '<i class=\"fas fa-chevron-left slick-prev\"></i>',
                asNavFor: '.main-gallery-nav'
            });
            " . ($atts['show_thumbnails'] ? "
            jQuery('.main-gallery-nav').slick({
                slidesToShow: 9,
                slidesToScroll: 1,
                asNavFor: '.main-gallery',
                    arrows: false,
                    dots: false,
                    centerMode: false,
                    focusOnSelect: true,
                });
            " : "") . "
        });
    </script>";

    return $output . $script;
}

// Gallery
/**
 * Shortcode function for displaying a gallery.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML content for the gallery.
 */
function vmb_acf_gallery_func($atts) {

    $atts = shortcode_atts(array(
        'post_id' => null,
        'gallery_key' => 'gallery',
    ), $atts, 'vmb_gallery');

    // Get the post ID from the current post in the loop
    // If we're in a loop, $post will be available
    global $post;
    
    // If we're in a loop, use the current post ID
    // Otherwise, try to get the post ID from the current queried object
    $post_id = $atts['post_id'];
    $gallery_key = $atts['gallery_key'];

    if(empty($gallery_key)) {
        return 'Gallery Key is required to display the gallery.';
    }

    if(empty($post_id)) {
        return 'Post ID is required to display the gallery.';
    }

    $gallery = vmb_get_field($gallery_key, $post_id);
    $name = get_the_title($post_id);
    $output = '';

    $images_output = '';
    if (!empty($gallery)) {
        foreach ($gallery as $image) {
            $images_output .= '<div class="gallery-image"><img src="' . $image['url'] . '" alt="' . $name . '"/></div>';
        }
    }

    $random_id = 'gallery-' . wp_rand(1000, 9999);

    $output .= '<div class="gallery main-gallery" id="' . $random_id . '">';
    $output .= $images_output;
    $output .= '</div>';

    $script = "<script>
        jQuery(document).ready(function() {
            jQuery('#" . $random_id . "').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                adaptiveHeight: true,
                fade: true,
                nextArrow: '<i class=\"fas fa-chevron-right slick-next\"></i>',
                prevArrow: '<i class=\"fas fa-chevron-left slick-prev\"></i>',
            });
        });
    </script>";

    return $output . $script;
}

// Rooms
/**
 * Shortcode function for displaying rooms.
 *
 * @return string HTML content for the rooms.
 */
function vmb_rooms_func() {

    $filters = modules\rooms\get_room_filters();
    $rooms = modules\rooms\get_rooms(array('demo' => true));

    if (empty($filters) || empty($rooms)) {
        return 'No rooms available.';
    }

    $output = '<div class="vmb-rooms-wrapper">';
    $output .= '<ul class="vmb-room-filters">' . $filters . '</ul>';
    $output .= '<div class="vmb-rooms">' .$rooms. '</div>';
    $output .= '</div>';

    return $output;
}

function vmb_room_category_func($atts) {

    $atts = shortcode_atts(array(
        'category' => '',
    ), $atts, 'vmb_room_category');

    

    $rooms = modules\rooms\get_rooms(array('category' => $atts['category']));

    if (empty($rooms)) {
        return 'No rooms available.';
    }

    $output = '<div class="vmb-rooms-wrapper">';
    $output .= '<div class="vmb-rooms">' .$rooms. '</div>';
    $output .= '</div>';

    return $output;

}

// Specials
/**
 * Shortcode function for displaying specials.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML content for the specials.
 */
function vmb_specials_func($atts) {

    $output = '';
    $helper = new VMB_HELPER();
    
    $atts = shortcode_atts(
		array(
			'id' => '',
		), $atts, 
        'specials' );

        $specials = get_option('vmb_api_cached_specials') ? json_decode(get_option('vmb_api_cached_specials'), true) : array();
        
        if(!empty($specials)) {

            foreach($specials as $special) {
           
                if( !$special['disable'] ) {
    
                    $packageID = $special['id'];
                    $name = $special['name'];
    
                    $description = $special['description'];
                    $expiration = $special['expiration'];
    
    
                    $output .= '<div class="vmb-special" id="special-'.$packageID.'">
                                    <img src="' . get_stylesheet_directory_uri() . '/assets/specials-icon.png">
                                    <div class="special-details">
                                        <h3 class="package-name">'.$name.'</h3>
                                        <p class="description">'.$description.'</p>
                                        <div class="validity">
                                            <span>Valid:</span>
                                            <span>'.date('n/j/Y').' - '.date_format(date_create($expiration), 'n/j/Y').'</span>
                                        </div>
                                        <a class="theme-button" href="/reservation?packageId='.$packageID.'" tabindex="0">Book Now</a>
                                    </div>
                                </div>';
                }
            }

        } else {
            return 'No specials available.';
        }
    
        // pretty_print_array($specials);
        return '<div class="vmb-widget vmb-specials">'.$output.'</div>';

}

// Display Special Category
/**
 * Shortcode function for displaying specials by category.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML content for the specials by category.
 */
function display_category_func($atts) {
    $atts = shortcode_atts(
        array(
            'category' => '',
        ),
        $atts,
        'display_category'
    );

    $helper = new VMB_HELPER();

    $specials = $helper->filter_specials_by_category($atts['category']);

    error_log('FilteredSpecials: ' . print_r($specials, true));

    $output = '';

    if(count($specials) > 0) {

        foreach($specials as $special) {
            
            $image = vmb_get_field('preview_image', 'option');

            $output .= '<div class="specialcode-item" id="special-'.$special['id'].'">
                <img src="'. (($image) ? $image['url'] : 'https://via.placeholder.com/600x400') .'" alt="'.$special['site_name'].'">
                <div class="specials-content">
                    <div class="specials-title">'.$special['name'].'</div>
                    '. (($special['description']) ? '<div class="specials-description">'. $special['description'] .'</div>' : '') .'
                    <div class="specials-buttons mt-3">
                        <a href="/reservation?packageId='.$special['id'].'" class="btn">View Deal Info</a>
                    </div>
                </div>
            </div>';

        }

        return $output;

    } else {
        return $helper->displayMessage(['code' => 'error', 'message' => 'No specials found under category: ' . $atts['category']]);
    }
}

// Reviews
/**
 * Shortcode function for displaying reviews. 
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML content for the reviews.
 */
function vmb_reviews_func($atts) {

    global $post;
    $post_id = $post->ID;
    $output = '';

    $atts = shortcode_atts(
		array(
			'resort_id' => '',
            'limit' => -1,
		), $atts, 
        'reviews' );

    $name = get_bloginfo('name');

    $reviews = get_posts(
        array(
            'numberposts' => $atts['limit'],
            'post_type' => 'vmb_reviews',
        )
    );
	
    
    foreach($reviews as $review) {
        $id = $review->ID;
        $hide = vmb_get_field('hide_from_query', $id);

        if(!$hide) {
            $firstname = get_post_meta($id, 'vmb_review_firstname', true);
            $rating = get_post_meta($id, 'vmb_review_rating', true);
            $comment = (get_post_field('post_content', $id) != '') ? get_post_field('post_content', $id) : get_post_meta($id, 'vmb_review_comment', true);


            $output .= '<div class="vmb-review" id="review-'.$id.'">
                            <i class="fa fa-solid fa-quote-left"></i>
                            <div class="review-details">
                                <div class="comment">
                                    '.$comment.'
                                </div>
                                <div class="rating">'.print_star_rating($rating).'</div>
                                <span class="author">'.$firstname.'</span>
                            </div>
                        </div>';

        }
    }

    return '<div class="vmb-widget vmb-reviews">'.$output.'</div>';

}


function vmb_mapbox_map_func() {
    
    $coordinates = vmb_get_field('geolocation', 'option') ? vmb_get_field('geolocation', 'option') : '';
    $address = vmb_get_field('address', 'option') ? vmb_get_field('address', 'option') : '';
    
    // Check if lat and lng post meta exist
	$lat = $coordinates['latitude'];
	$lng = $coordinates['longitude'];
	
	if (empty($lat) || empty($lng)) {
		echo 'Cannot create map. Latitude and Longitude is required!';
	}
	
	
    $output = '<div class="mapbox-solo" data-address="'.$address.'" data-lat="'.$lat.'" data-lng="'.$lng.'" data-zoom="16" style="width: 100%; height: 350px;"></div>';
    
    return $output;
}

function vmb_amenities_func() {
    
    $args = array(
        'post_type' => 'amenity',
        'posts_per_page' => -1,
        'post_status' => 'publish',
		'orderby' => 'menu_order',
    	'order' => 'ASC',
    );

    $query = new WP_Query($args);

    $output = '';

    if($query->have_posts()) {
        while($query->have_posts()) {
            $query->the_post();

            $image = vmb_get_field('gallery', get_the_ID());

            // Get the first amenity category's parent name (if it has one)
            $amenity_terms = get_the_terms(get_the_ID(), 'amenity-category');
            $category_name = '';
            
            if (!is_wp_error($amenity_terms) && !empty($amenity_terms)) {
                $term = $amenity_terms[0];
                $category_name = ($term->parent !== 0) ? 
                    get_term($term->parent, 'amenity-category')->name : 
                    $term->name;
            }

            $featured_image = (isset($image[0])) ? $image[0] : '';

            $output .= '<div class="vmb-amenity vmb-filter-item" data-category="'.$category_name.'">';
            if (!empty($featured_image)) {
                $output .= '<div class="amenity-image">';
                $output .= '<img src="' . $featured_image['url'] . '" alt="' . $featured_image['alt'] . '">';
                $output .= '</div>';
            }
            $output .= '<div class="amenity-details">';
            $output .= '<h3>' . get_the_title() . '</h3>';
            $content = get_the_excerpt();
            $output .= '<div class="amenity-description">' . $content . '</div>';
            $output .= '<a href="' . get_the_permalink() . '" class="theme-button">Learn More</a>';
            $output .= '</div>';
            $output .= '</div>';
        }
    }

    // Get all amenity categories
    $amenity_categories = array();
    $query = array(
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'amenity_order',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key' => 'amenity_order',
                'value' => 0,
                'compare' => '>='
            )
        ),
        'hide_empty' => true,
        'parent' => 0
    );
    $terms = get_terms( 'amenity-category', $query );
    
    // Check if there are any terms and add them to the array
    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $amenity_categories[] = $term->name;
        }
    }

    $count = 0;

    $filters = '';

    foreach ($amenity_categories as $category){
        $filters .= '<li id="category-'.vmbt_slugify($category).'" data-slug="'.$category.'">'.$category.'</li>';
        $count++;
    }

    wp_reset_postdata();

    return '<ul class="filters amenity-filters"><span>View: </span><li class="active" data-slug="*">All</li> '.$filters.'</ul><div class="vmb-amenities">'.$output.'</div>';
    
}

function vmb_amenities_category_func($atts) {
    
    $atts = shortcode_atts(array(
        'category' => '',
    ), $atts, 'vmb_amenities_category');

    $args = array(
        'post_type' => 'amenity',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'menu_order',
        'order' => 'ASC'
    );

    // Only add tax_query if category is not empty
    if (!empty($atts['category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'amenity-category',
                'field' => 'term_id',
                'terms' => $atts['category'],
            )
        );
    }

    $query = new WP_Query($args);

    $output = '';

    if($query->have_posts()) {
        while($query->have_posts()) {
            $query->the_post();

            $image = vmb_get_field('gallery', get_the_ID());

            // Get the first amenity category's parent name (if it has one)
            $amenity_terms = get_the_terms(get_the_ID(), 'amenity-category');
            $category_name = '';
            
            if (!is_wp_error($amenity_terms) && !empty($amenity_terms)) {
                $term = $amenity_terms[0];
                $category_name = ($term->parent !== 0) ? 
                    get_term($term->parent, 'amenity-category')->name : 
                    $term->name;
            }

            $featured_image = (isset($image[0])) ? $image[0] : '';

            $output .= '<div class="vmb-amenity" data-category="'.$category_name.'">';
            if (!empty($featured_image)) {
                $output .= '<div class="amenity-image">';
                $output .= '<img src="' . $featured_image['url'] . '" alt="' . $featured_image['alt'] . '">';
                $output .= '</div>';
            }
            $output .= '<div class="amenity-details">';
            $output .= '<h3>' . get_the_title() . '</h3>';
            $content = get_the_excerpt();
            $output .= '<div class="amenity-description">' . $content . '</div>';
            $output .= '<a href="' . get_the_permalink() . '" class="theme-button">Learn More</a>';
            $output .= '</div>';
            $output .= '</div>';
        }
    }

    wp_reset_postdata();

    return '<div class="vmb-amenities">'.$output.'</div>';
    
}

function vmb_area_information_func() {
    
    $args = array(
        'post_type' => 'area-information',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );

    $query = new WP_Query($args);

    $output = '';

    if($query->have_posts()) {
        while($query->have_posts()) {
            $query->the_post();

            // Get the first amenity category's parent name (if it has one)
            $amenity_terms = get_the_terms(get_the_ID(), 'area-information-category');
            $category_name = '';
            
            if (!is_wp_error($amenity_terms) && !empty($amenity_terms)) {
                $term = $amenity_terms[0];
                $category_name = ($term->parent !== 0) ? 
                    get_term($term->parent, 'area-information-category')->name : 
                    $term->name;
            }

            $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'full') ? get_the_post_thumbnail_url(get_the_ID(), 'full') : get_stylesheet_directory_uri() . '/assets/placeholder.jpg';

            $output .= '<div class="vmb-area-information vmb-filter-item" data-category="'.$category_name.'">';
            if (!empty($featured_image)) {
                $output .= '<div class="area-information-image">';
                $output .= '<img src="' . $featured_image . '" alt="' . $category_name . '">';
                $output .= '</div>';
            }
            $output .= '<div class="area-information-details">';
            $output .= '<h3>' . get_the_title() . '</h3>';
            $content = get_the_content();
            $output .= '<div class="area-information-description">' . $content . '</div>';
            // $output .= '<a href="' . get_the_permalink() . '" class="theme-button">Learn More</a>';
            $output .= '</div>';
            $output .= '</div>';
        }
    }

    // Get all amenity categories
    $area_information_categories = array();
    $terms = get_terms(array(
        'taxonomy' => 'area-information-category',
        'hide_empty' => true,
    ));
    
    // Check if there are any terms and add them to the array
    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $area_information_categories[] = $term->name;
        }
    }

    $count = 0;

    $filters = '';

    foreach ($area_information_categories as $category){
        $filters .= '<li class="'.(($count == 0) ? "active" : "").'" data-slug="'.$category.'">'.$category.'</li>';
        $count++;
    }

    wp_reset_postdata();

    return '<ul class="filters area-information-filters"><span>View: </span><li data-slug="*">All</li> '.$filters.'</ul><div class="vmb-area-information-container">'.$output.'</div>';
    
}


function vmb_faq_accordion_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'category' => '',
    ), $atts, 'vmb_accordion' );

    if ( empty( $atts['category'] ) ) {
        return '<p><strong>Error:</strong> Please provide a valid category ID.</p>';
    }

    $args = array(
        'post_type'      => 'faq',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'faq-category',
                'field'    => 'term_id',
                'terms'    => intval( $atts['category'] ),
            ),
        ),
    );

    $faqs = new WP_Query( $args );

    if ( ! $faqs->have_posts() ) {
        return '<p>No FAQs found for this category.</p>';
    }

    ob_start();
    ?>
    <div class="vmb-faq-accordion">
        <?php while ( $faqs->have_posts() ) : $faqs->the_post(); ?>
            <div class="faq-item">
                <div class="faq-question" role="button" tabindex="0">
                    <h3><?php the_title(); ?></h3>
                    <span class="faq-toggle-icon">+</span>
                </div>
                <div class="faq-answer" style="display: none;">
                    <div><?php the_content(); ?></div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
function vmb_guest_calendar_func($atts) {

    $atts = shortcode_atts(array(
        'unit_id' => '',
    ), $atts, 'vmb_guest_calendar');

    $unit_id = $atts['unit_id'];

    global $post;
    $post_id = $post->ID;

    // If no unit_id is provided in the shortcode, check if we're on a room post type
    if (empty($unit_id) && isset($post_id) && get_post_type($post_id) === 'vmb_room') {
        // Get the unit_id from ACF field for room post type
        $unit_id = vmb_get_field('unit_id', $post_id);
        
        if (empty($unit_id)) {
            return 'Unit ID is required to display the guest calendar. Please add a "unit_id" in the shortcode or set it in the room settings.';
        }
    } elseif (empty($unit_id)) {
        return 'Unit ID is required to display the guest calendar. Please add a "unit_id" in the shortcode.';
    }


    $site_name = vmb_get_field('site_name', 'option') ? vmb_get_field('site_name', 'option') : '';
    

    echo '<script type="text/javascript" src="https://widgets.guestdesk.com/sites/guestdesk/widgets/bundle.en.js"></script>
                <guestdesk-widget-calendar></guestdesk-widget-calendar>
                <script type="text/javascript">
                  var domReady = function(callback) {
                      document.readyState === "interactive" || document.readyState === "complete" ? callback() : document.addEventListener("DOMContentLoaded", callback);
                  };

                  domReady(function() {
                      var baseConfig = {
                          "SiteName": "'.$site_name.'"
                      };

                      new Guestdesk(baseConfig).loadWidgets({"unitTypeId": '.$unit_id.', "availabilityCalendarMonths": 1});
                  });
    </script>';
}


function vmb_room_amenities_func($atts) {

    $atts = shortcode_atts(array(
        'room_id' => '',
    ), $atts, 'vmb_room_amenities');

    $room_id = $atts['room_id'];

    global $post;
    $post_id = $post->ID;

    if (empty($room_id) && isset($post_id) && get_post_type($post_id) === 'vmb_room') {
        $room_id = $post_id;
        $amenities = vmb_get_field('amenities', $room_id);
    } elseif (empty($room_id)) {
        return 'Room ID is required to display the room amenities. Please add a "room_id" in the shortcode.';
    }
    
    $output = '';

    // Check if amenities are set, default to empty array if not
    $amenities = isset($amenities) ? $amenities : [];
    
    if (!empty($amenities)) {
        $output .= '<div class="vmb-room-amenities-container">';
        $output .= '<ul class="vmb-room-amenities">';
        
        foreach ($amenities as $amenity) {
            $icon_class = !empty($amenity['icon']) ? $amenity['icon'] : 'fa-check';
            $name = !empty($amenity['name']) ? $amenity['name'] : (isset($amenity['label']) ? $amenity['label'] : '');
            $output .= '<li><i class="fa ' . esc_attr($icon_class) . '"></i> ' . esc_html($name) . '</li>';
        }
        
        $output .= '</ul>';
        $output .= '</div>';
    }

    return $output;
}
