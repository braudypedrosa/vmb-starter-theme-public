<?php

namespace modules\rooms;

/**
 * Get room category filters as HTML list items.
 *
 * @return string HTML for room category filters.
 */
function get_room_filters() {
    $query = array(
        'orderby'    => 'meta_value_num',
        'order'      => 'ASC',
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key'     => 'vmb_room_order',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key'     => 'vmb_room_order',
                'value'   => 0,
                'compare' => '>=',
            ),
        ),
        'hide_empty' => true,
        'parent'     => 0,
    );

    $categories = get_terms('room-category', $query);

    $filters = '';
    $filters .= '<li><span data-filter="all">All</span></li>';

    if (!empty($categories) && !is_wp_error($categories)) {
        foreach ($categories as $category) {
            $filters .= sprintf(
                '<li><span data-filter=".%s">%s</span></li>',
                esc_attr($category->slug),
                esc_html($category->name)
            );
        }
    }

    return $filters;
}

/**
 * Get rooms as HTML output.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output for rooms.
 */
function get_rooms($atts) {
    $atts = shortcode_atts(
        array(
            'demo'    => false,
            'category'=> '',
        ),
        $atts,
        'vmb_rooms'
    );

    $args = array(
        'post_type'   => 'vmb_room',
        'numberposts' => -1,
        'orderby'     => 'menu_order',
        'order'       => 'ASC',
    );

    if (!empty($atts['category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'room-category',
                'field'    => 'term_id',
                'terms'    => $atts['category'],
            ),
        );
    }

    $rooms  = get_posts($args);
    $output = '';

    error_log('Rooms: ' . print_r($rooms, true));

    if (!empty($rooms) && !is_wp_error($rooms)) {
        foreach ($rooms as $room) {
            $categories       = get_the_terms($room->ID, 'room-category');
            $category_classes = '';

            if (!empty($categories) && !is_wp_error($categories)) {
                foreach ($categories as $category) {
                    $category_classes .= ' ' . esc_attr($category->slug);
                }
            }

            $demo_content    = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
            $demo_amenities  = array(
                array('icon' => '', 'name' => 'Amenity 1'),
                array('icon' => '', 'name' => 'Amenity 2'),
                array('icon' => '', 'name' => 'Amenity 3'),
                array('icon' => '', 'name' => 'Amenity 4'),
                array('icon' => '', 'name' => 'Amenity 5'),
                array('icon' => '', 'name' => 'Amenity 6'),
            );
            $demo_image      = 'https://via.placeholder.com/600';

            $title           = $room->post_title;
            $unit_id         = get_field('unit_id', $room->ID);
            $content         = $room->post_content;
            $custom_link     = get_field('custom_external_url', $room->ID);

            $featured_image  = has_post_thumbnail($room->ID)
                ? get_the_post_thumbnail_url($room->ID, 'full')
                : ($atts['demo'] ? $demo_image : '');

            $amenities = get_field('amenities', $room->ID);
            if ($atts['demo'] && empty($amenities)) {
                $amenities = $demo_amenities;
            }

            $short_description = get_field('short_description', $room->ID);
            if (empty($short_description)) {
                $short_description = !empty($content) ? substr($content, 0, 200) . ' ...' : '';
            }

            $amenities_output = '';
            if (!empty($amenities) && is_array($amenities)) {
                $amenities_output = '<ul class="vmb-room-amenities-list">';
                $count = 0;
                foreach ($amenities as $amenity) {
                    if ($count <= 9) {
                        $icon = !empty($amenity['icon']) ? esc_attr($amenity['icon']) : 'fa-check';
                        $name = !empty($amenity['name']) ? esc_html($amenity['name']) : '';
                        $amenities_output .= sprintf(
                            '<li><i class="fa-solid %s"></i>%s</li>',
                            $icon,
                            $name
                        );
                    }
                    $count++;
                }
                $amenities_output .= '</ul>';
            }

            $clean_description = strip_tags($short_description);


            // Determine booking button based on custom link
            if ($custom_link) {
                $book_button = sprintf(
                    '<a href="%s" class="vmb-room-link theme-button" target="_blank" rel="noopener noreferrer">Book On Ocean Escape</a>',
                    esc_url($custom_link)
                );
            } else {
                $book_button = sprintf(
                    '<a href="/reservations?unitTypeId=%s" class="vmb-room-link theme-button">Book Now</a>',
                    esc_attr($unit_id)
                );
            }

            $output .= sprintf(
                '<div class="vmb-room mix%s" data-category="%s">
                    <img class="vmb-room-image" src="%s" alt="%s">
                    <div class="vmb-room-content-wrapper">
                        <div class="vmb-room-content mod">
                            %s
                            %s
                            %s
                        </div>
                        <div class="vmb-button-group">
                            <a href="%s" class="vmb-room-link theme-button">View Details</a>
                            %s
                        </div>
                    </div>
                </div>',
                esc_attr($category_classes),
                esc_attr($room->post_name),
                esc_url($featured_image),
                esc_attr($title),
                !empty($title) ? '<h3 class="vmb-room-title">' . esc_html($title) . '</h3>' : '',
                !empty($short_description) ? '<div class="vmb-room-short-description">' . esc_html($clean_description) . '</div>' : '',
                !empty($amenities_output) ? '<div class="vmb-room-amenities">' . $amenities_output . '</div>' : '',
                esc_url(get_the_permalink($room->ID)),
                $book_button
            );
        }
    }

    return $output;
}