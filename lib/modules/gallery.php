<?php

// Add a new column to the "gallery" post type 'vmb_gallery' called Shortcode
function add_vmb_gallery_shortcode_column($columns) {
    $columns['shortcode'] = __('Shortcode', 'text_domain');
    return $columns;
}
add_filter('manage_vmb_gallery_posts_columns', 'add_vmb_gallery_shortcode_column');

// Populate the new column with the shortcode
function populate_vmb_gallery_shortcode_column($column, $post_id) {
    if ($column == 'shortcode') {
        echo '[vmb_gallery id="' . $post_id . '"]';
    }
}
add_action('manage_vmb_gallery_posts_custom_column', 'populate_vmb_gallery_shortcode_column', 10, 2);

// Make the new column sortable
function sortable_vmb_gallery_shortcode_column($columns) {
    $columns['shortcode'] = 'shortcode';
    return $columns;
}
add_filter('manage_edit-vmb_gallery_sortable_columns', 'sortable_vmb_gallery_shortcode_column');

// Add a new column to the "gallery" post type 'vmb_gallery' called ID
function add_vmb_gallery_id_column($columns) {
    $columns['id'] = __('ID', 'text_domain');
    return $columns;
}
add_filter('manage_vmb_gallery_posts_columns', 'add_vmb_gallery_id_column');

// Populate the new column with the post ID
function populate_vmb_gallery_id_column($column, $post_id) {
    if ($column == 'id') {
        echo $post_id;
    }
}
add_action('manage_vmb_gallery_posts_custom_column', 'populate_vmb_gallery_id_column', 10, 2);

// Make the new column sortable
function sortable_vmb_gallery_id_column($columns) {
    $columns['id'] = 'id';
    return $columns;
}
add_filter('manage_edit-vmb_gallery_sortable_columns', 'sortable_vmb_gallery_id_column');
