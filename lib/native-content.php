<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'vmb_register_native_content_types', 20 );
add_action( 'after_switch_theme', 'vmb_flush_native_content_rewrites' );

function vmb_register_native_content_types() {
    $post_types = array(
        'vmb_room'         => array( 'Rooms', 'Room', 'dashicons-admin-home', array( 'title', 'editor', 'thumbnail', 'page-attributes' ) ),
        'vmb_gallery'      => array( 'Galleries', 'Gallery', 'dashicons-cover-image', array( 'title' ) ),
        'amenity'          => array( 'Amenities', 'Amenity', 'dashicons-palmtree', array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ) ),
        'area-information' => array( 'Area Information', 'Area Information', 'dashicons-location-alt', array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ) ),
        'faq'              => array( 'FAQs', 'FAQ', 'dashicons-editor-help', array( 'title', 'editor', 'page-attributes' ) ),
        'golf-course'      => array( 'Golf Courses', 'Golf Course', 'dashicons-flag', array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ) ),
        'group'            => array( 'Groups', 'Group', 'dashicons-groups', array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ) ),
    );

    foreach ( $post_types as $post_type => $config ) {
        list( $plural, $singular, $icon, $supports ) = $config;

        register_post_type(
            $post_type,
            array(
                'labels'             => vmb_native_post_type_labels( $plural, $singular ),
                'public'             => true,
                'publicly_queryable' => true,
                'exclude_from_search'=> false,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'show_in_admin_bar'  => true,
                'show_in_nav_menus'  => true,
                'show_in_rest'       => true,
                'has_archive'        => false,
                'hierarchical'       => false,
                'menu_icon'          => $icon,
                'supports'           => $supports,
                'rewrite'            => array(
                    'slug'       => $post_type,
                    'with_front' => true,
                    'feeds'      => false,
                    'pages'      => true,
                ),
                'query_var'          => true,
                'can_export'         => true,
                'delete_with_user'   => false,
            )
        );
    }

    vmb_register_native_taxonomy( 'room-category', 'Room Categories', 'Room Category', array( 'vmb_room' ) );
    vmb_register_native_taxonomy( 'amenity-category', 'Amenity Categories', 'Amenity Category', array( 'amenity' ) );
    vmb_register_native_taxonomy( 'amenity-type', 'Amenity Types', 'Amenity Type', array( 'amenity' ) );
    vmb_register_native_taxonomy( 'area-information-category', 'Area Information Categories', 'Area Information Category', array( 'area-information' ) );
    vmb_register_native_taxonomy( 'faq-category', 'FAQ Categories', 'FAQ Category', array( 'faq' ) );
}

function vmb_native_post_type_labels( $plural, $singular ) {
    return array(
        'name'               => $plural,
        'singular_name'      => $singular,
        'menu_name'          => $plural,
        'all_items'          => 'All ' . $plural,
        'add_new'            => 'Add New ' . $singular,
        'add_new_item'       => 'Add New ' . $singular,
        'edit_item'          => 'Edit ' . $singular,
        'new_item'           => 'New ' . $singular,
        'view_item'          => 'View ' . $singular,
        'view_items'         => 'View ' . $plural,
        'search_items'       => 'Search ' . $plural,
        'not_found'          => 'No ' . strtolower( $plural ) . ' found',
        'not_found_in_trash' => 'No ' . strtolower( $plural ) . ' found in Trash',
        'archives'           => $singular . ' Archives',
        'attributes'         => $singular . ' Attributes',
        'items_list'         => $plural . ' list',
        'item_updated'       => $singular . ' updated.',
    );
}

function vmb_register_native_taxonomy( $taxonomy, $plural, $singular, $object_types ) {
    register_taxonomy(
        $taxonomy,
        $object_types,
        array(
            'labels'            => array(
                'name'              => $plural,
                'singular_name'     => $singular,
                'menu_name'         => 'Categories',
                'all_items'         => 'All ' . $plural,
                'edit_item'         => 'Edit ' . $singular,
                'view_item'         => 'View ' . $singular,
                'update_item'       => 'Update ' . $singular,
                'add_new_item'      => 'Add New ' . $singular,
                'new_item_name'     => 'New ' . $singular . ' Name',
                'parent_item'       => 'Parent ' . $singular,
                'parent_item_colon' => 'Parent ' . $singular . ':',
                'search_items'      => 'Search ' . $plural,
                'not_found'         => 'No ' . strtolower( $plural ) . ' found',
            ),
            'public'            => true,
            'publicly_queryable'=> true,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_in_nav_menus' => true,
            'show_in_rest'      => true,
            'show_tagcloud'     => true,
            'show_in_quick_edit'=> true,
            'show_admin_column' => false,
            'rewrite'           => array(
                'slug'         => $taxonomy,
                'with_front'   => true,
                'hierarchical' => false,
            ),
            'query_var'         => true,
        )
    );
}

function vmb_flush_native_content_rewrites() {
    vmb_register_native_content_types();
    flush_rewrite_rules();
}
