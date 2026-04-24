<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme-owned replacement for the small subset of ACF field formatting this
 * theme uses on the frontend.
 */
function vmb_get_field( $key, $post_id = false, $default = '' ) {
    if ( 'option' === $post_id || 'options' === $post_id ) {
        return vmb_get_option_field( $key, $default );
    }

    $post_id = $post_id ? absint( $post_id ) : get_the_ID();
    if ( ! $post_id ) {
        return $default;
    }

    switch ( $key ) {
        case 'gallery':
        case 'gallery_images':
            return vmb_format_gallery_value( get_post_meta( $post_id, $key, true ) );

        case 'room_layout':
        case 'main_image':
        case 'featured_image':
            return vmb_format_image_value( get_post_meta( $post_id, $key, true ) );

        case 'amenities':
            return vmb_get_repeater_field( $post_id, 'amenities', array( 'icon', 'name' ) );

        case 'coordinates':
            return array(
                'latitude'  => get_post_meta( $post_id, 'coordinates_latitude', true ),
                'longitude' => get_post_meta( $post_id, 'coordinates_longitude', true ),
            );

        default:
            $value = get_post_meta( $post_id, $key, true );
            return '' === $value ? $default : $value;
    }
}

function vmb_get_option_field( $key, $default = '' ) {
    switch ( $key ) {
        case 'preview_image':
            return vmb_format_image_value( get_option( 'options_preview_image', '' ) );

        case 'geolocation':
            return array(
                'latitude'  => get_option( 'options_geolocation_latitude', '' ),
                'longitude' => get_option( 'options_geolocation_longitude', '' ),
            );

        case 'social_media':
            return vmb_get_option_repeater_field( 'social_media', array( 'icon', 'url' ) );

        case 'alchemer_settings':
            return array(
                'api_token'        => get_option( 'options_alchemer_settings_api_token', '' ),
                'api_secret'       => get_option( 'options_alchemer_settings_api_secret', '' ),
                'review_field_id'  => get_option( 'options_alchemer_settings_review_field_id', '' ),
                'sync_preference'  => array(
                    'minimum_rating'  => get_option( 'options_alchemer_settings_sync_preference_minimum_rating', '' ),
                    'reviews_to_pull' => get_option( 'options_alchemer_settings_sync_preference_reviews_to_pull', '' ),
                ),
            );

        case 'guestdesk_settings':
            return array(
                'username' => get_option( 'options_guestdesk_settings_username', '' ),
                'password' => get_option( 'options_guestdesk_settings_password', '' ),
            );

        default:
            $value = get_option( 'options_' . $key, $default );
            return '' === $value ? $default : $value;
    }
}

function vmb_update_post_field( $post_id, $key, $value ) {
    update_post_meta( $post_id, $key, $value );
}

function vmb_update_option_field( $key, $value ) {
    update_option( 'options_' . $key, $value, false );
}

function vmb_get_repeater_field( $post_id, $base_key, $subfields ) {
    $count = absint( get_post_meta( $post_id, $base_key, true ) );
    $rows  = array();

    for ( $i = 0; $i < $count; $i++ ) {
        $row = array();

        foreach ( $subfields as $subfield ) {
            $row[ $subfield ] = get_post_meta( $post_id, "{$base_key}_{$i}_{$subfield}", true );
        }

        if ( array_filter( $row, 'strlen' ) ) {
            $rows[] = $row;
        }
    }

    return $rows;
}

function vmb_update_repeater_field( $post_id, $base_key, $rows, $subfields ) {
    $old_count = absint( get_post_meta( $post_id, $base_key, true ) );
    $rows      = array_values( array_filter( $rows ) );

    update_post_meta( $post_id, $base_key, count( $rows ) );

    foreach ( $rows as $index => $row ) {
        foreach ( $subfields as $subfield ) {
            update_post_meta( $post_id, "{$base_key}_{$index}_{$subfield}", isset( $row[ $subfield ] ) ? $row[ $subfield ] : '' );
        }
    }

    for ( $index = count( $rows ); $index < $old_count; $index++ ) {
        foreach ( $subfields as $subfield ) {
            delete_post_meta( $post_id, "{$base_key}_{$index}_{$subfield}" );
        }
    }
}

function vmb_get_option_repeater_field( $base_key, $subfields ) {
    $count = absint( get_option( 'options_' . $base_key, 0 ) );
    $rows  = array();

    for ( $i = 0; $i < $count; $i++ ) {
        $row = array();

        foreach ( $subfields as $subfield ) {
            $row[ $subfield ] = get_option( "options_{$base_key}_{$i}_{$subfield}", '' );
        }

        if ( array_filter( $row, 'strlen' ) ) {
            $rows[] = $row;
        }
    }

    return $rows;
}

function vmb_update_option_repeater_field( $base_key, $rows, $subfields ) {
    $old_count = absint( get_option( 'options_' . $base_key, 0 ) );
    $rows      = array_values( array_filter( $rows ) );

    update_option( 'options_' . $base_key, count( $rows ), false );

    foreach ( $rows as $index => $row ) {
        foreach ( $subfields as $subfield ) {
            update_option( "options_{$base_key}_{$index}_{$subfield}", isset( $row[ $subfield ] ) ? $row[ $subfield ] : '', false );
        }
    }

    for ( $index = count( $rows ); $index < $old_count; $index++ ) {
        foreach ( $subfields as $subfield ) {
            delete_option( "options_{$base_key}_{$index}_{$subfield}" );
        }
    }
}

function vmb_format_gallery_value( $value ) {
    if ( empty( $value ) ) {
        return array();
    }

    if ( is_string( $value ) && false !== strpos( $value, ',' ) ) {
        $value = array_map( 'trim', explode( ',', $value ) );
    }

    if ( ! is_array( $value ) ) {
        $value = array( $value );
    }

    $images = array();

    foreach ( $value as $attachment_id ) {
        $image = vmb_format_image_value( $attachment_id );
        if ( $image ) {
            $images[] = $image;
        }
    }

    return $images;
}

function vmb_format_image_value( $value ) {
    if ( is_array( $value ) && ! empty( $value['url'] ) ) {
        return $value;
    }

    $attachment_id = absint( $value );
    if ( ! $attachment_id ) {
        return false;
    }

    $url = wp_get_attachment_url( $attachment_id );
    if ( ! $url ) {
        return false;
    }

    return array(
        'ID'    => $attachment_id,
        'id'    => $attachment_id,
        'url'   => $url,
        'alt'   => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
        'title' => get_the_title( $attachment_id ),
    );
}

function vmb_sanitize_gallery_ids( $value ) {
    if ( is_array( $value ) ) {
        $ids = $value;
    } else {
        $ids = preg_split( '/[,\s]+/', (string) $value );
    }

    return array_values( array_filter( array_map( 'absint', $ids ) ) );
}

if ( ! function_exists( 'get_field' ) ) {
    function get_field( $selector, $post_id = false ) {
        return vmb_get_field( $selector, $post_id );
    }
}

if ( ! function_exists( 'update_field' ) ) {
    function update_field( $selector, $value, $post_id = false ) {
        if ( 'option' === $post_id || 'options' === $post_id ) {
            if ( 'geolocation' === $selector && is_array( $value ) ) {
                vmb_update_option_field( 'geolocation_latitude', isset( $value['latitude'] ) ? $value['latitude'] : '' );
                vmb_update_option_field( 'geolocation_longitude', isset( $value['longitude'] ) ? $value['longitude'] : '' );
                return true;
            }

            vmb_update_option_field( $selector, $value );
            return true;
        }

        $post_id = $post_id ? absint( $post_id ) : get_the_ID();
        if ( ! $post_id ) {
            return false;
        }

        if ( 'coordinates' === $selector && is_array( $value ) ) {
            update_post_meta( $post_id, 'coordinates_latitude', isset( $value['latitude'] ) ? $value['latitude'] : '' );
            update_post_meta( $post_id, 'coordinates_longitude', isset( $value['longitude'] ) ? $value['longitude'] : '' );
            return true;
        }

        update_post_meta( $post_id, $selector, $value );
        return true;
    }
}
