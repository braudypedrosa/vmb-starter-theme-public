<?php

function get_vmb_categories() {

    // Fetch categories from the server
    $categories = get_option('vmb_specials_category', true);

    if ($categories) {
        wp_send_json_success($categories);
    } else {
        wp_send_json_error('No categories found');
    }
}


add_action('wp_ajax_get_vmb_categories', 'get_vmb_categories');
add_action('wp_ajax_nopriv_get_vmb_categories', 'get_vmb_categories');

function get_vmb_specials() {

    $specials = get_option('vmb_api_cached_specials', true);

    if ($specials) {
        wp_send_json_success($specials);
    } else {
        wp_send_json_error('No specials found');
    }
}

add_action('wp_ajax_get_vmb_specials', 'get_vmb_specials');
add_action('wp_ajax_nopriv_get_vmb_specials', 'get_vmb_specials');

function save_specials_table() {

    // Get the posted data
    $jsonData = isset($_POST['jsonData']) ? sanitize_text_field(wp_unslash($_POST['jsonData'])) : '';
    $modifiedSpecial = isset($_POST['modifiedSpecial']) ? sanitize_text_field(wp_unslash($_POST['modifiedSpecial'])) : '';

    if (empty($jsonData)) {
        wp_send_json_error('No data provided');
    }

    // Decode the JSON data
    $specials = json_decode($jsonData, true);
    $modifiedSpecial = json_decode($modifiedSpecial, true);


    // If a modified special is provided, update it separately
    if (!empty($modifiedSpecial)) {

        $site_name = $modifiedSpecial['id'];

        error_log('Site Name: ' . $site_name);
        
        $specials = json_decode(get_option('vmb_api_cached_specials', true), true);

        foreach ($specials as $index => $special) {
            if($special['id'] == $modifiedSpecial['id']) {
                $specials[$index] = $modifiedSpecial;
                break;
            }	    
        }
        
        update_option('vmb_api_cached_specials', json_encode(array_values($specials), JSON_UNESCAPED_UNICODE));
        update_option('vmb_resort_specials', json_encode(array_values($specials), JSON_UNESCAPED_UNICODE));

        wp_send_json_success('Specials saved successfully!');
    } else {
        wp_send_json_error('No updates done!');
    }

    
}

add_action('wp_ajax_save_specials_table', 'save_specials_table');
add_action('wp_ajax_nopriv_save_specials_table', 'save_specials_table');
