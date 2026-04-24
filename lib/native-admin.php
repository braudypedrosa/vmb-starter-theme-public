<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'vmb_native_register_site_settings_page', 99 );
add_action( 'admin_post_vmb_save_site_settings', 'vmb_native_save_site_settings' );
add_action( 'add_meta_boxes', 'vmb_native_register_meta_boxes' );
add_action( 'save_post', 'vmb_native_save_post_meta' );
add_action( 'admin_enqueue_scripts', 'vmb_native_enqueue_admin_assets' );
add_action( 'admin_head', 'vmb_native_admin_styles' );
add_action( 'admin_footer', 'vmb_native_admin_media_script' );

foreach ( array( 'room-category', 'amenity-category' ) as $taxonomy ) {
    add_action( "{$taxonomy}_add_form_fields", 'vmb_native_render_term_add_fields' );
    add_action( "{$taxonomy}_edit_form_fields", 'vmb_native_render_term_edit_fields' );
    add_action( "created_{$taxonomy}", 'vmb_native_save_term_fields' );
    add_action( "edited_{$taxonomy}", 'vmb_native_save_term_fields' );
}

function vmb_native_register_site_settings_page() {
    remove_menu_page( 'site-settings' );

    add_menu_page(
        'Site Settings',
        'Site Settings',
        'edit_theme_options',
        'site-settings',
        'vmb_native_render_site_settings_page',
        'dashicons-admin-settings',
        59
    );
}

function vmb_native_enqueue_admin_assets( $hook ) {
    if ( 'post.php' === $hook || 'post-new.php' === $hook || 'toplevel_page_site-settings' === $hook || false !== strpos( $hook, 'edit-tags.php' ) || false !== strpos( $hook, 'term.php' ) ) {
        wp_enqueue_media();
    }
}

function vmb_native_admin_styles() {
    ?>
    <style>
        .vmb-native-tabs { margin-top: 12px; }
        .vmb-native-tab-nav { display: flex; gap: 6px; margin: 0 0 16px; border-bottom: 1px solid #ccd0d4; }
        .vmb-native-tab-nav button { border: 1px solid #ccd0d4; border-bottom: 0; background: #f6f7f7; padding: 8px 12px; cursor: pointer; border-radius: 4px 4px 0 0; }
        .vmb-native-tab-nav button.is-active { background: #fff; color: #1d2327; margin-bottom: -1px; padding-bottom: 9px; }
        .vmb-native-panel { display: none; }
        .vmb-native-panel.is-active { display: block; }
        .vmb-native-field { margin: 0 0 16px; max-width: 860px; }
        .vmb-native-field label { display: block; font-weight: 600; margin-bottom: 5px; }
        .vmb-native-field input[type="text"],
        .vmb-native-field input[type="url"],
        .vmb-native-field input[type="email"],
        .vmb-native-field input[type="password"],
        .vmb-native-field input[type="number"],
        .vmb-native-field textarea { width: 100%; max-width: 860px; }
        .vmb-native-field textarea { min-height: 110px; }
        .vmb-native-row-table { width: 100%; max-width: 860px; border-collapse: collapse; margin-bottom: 10px; }
        .vmb-native-row-table th,
        .vmb-native-row-table td { padding: 6px; border-bottom: 1px solid #dcdcde; text-align: left; vertical-align: top; }
        .vmb-native-row-table input { width: 100%; }
        .vmb-native-image-preview img,
        .vmb-native-gallery-preview img { width: 72px; height: 72px; object-fit: cover; margin: 0 6px 6px 0; border: 1px solid #dcdcde; background: #fff; }
        .vmb-native-settings .form-table th { width: 220px; }
    </style>
    <?php
}

function vmb_native_admin_media_script() {
    ?>
    <script>
    (function($) {
        function setPreview($field, ids) {
            var $preview = $field.closest('.vmb-native-field').find('.vmb-native-gallery-preview, .vmb-native-image-preview');
            if (!$preview.length) {
                return;
            }

            $preview.empty();
            ids.filter(Boolean).slice(0, 12).forEach(function(id) {
                var attachment = wp.media.attachment(id);
                attachment.fetch().then(function() {
                    var url = attachment.get('sizes') && attachment.get('sizes').thumbnail ? attachment.get('sizes').thumbnail.url : attachment.get('url');
                    if (url) {
                        $preview.append($('<img>').attr('src', url));
                    }
                });
            });
        }

        $(document).on('click', '.vmb-native-select-image', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $input = $button.closest('.vmb-native-field').find('input[type="text"], input[type="hidden"]').first();
            var frame = wp.media({ title: 'Select Image', multiple: false, library: { type: 'image' } });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.id).trigger('change');
                setPreview($input, [attachment.id]);
            });

            frame.open();
        });

        $(document).on('click', '.vmb-native-select-gallery', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $input = $button.closest('.vmb-native-field').find('input[type="text"], input[type="hidden"]').first();
            var frame = wp.media({ title: 'Select Images', multiple: true, library: { type: 'image' } });

            frame.on('select', function() {
                var ids = frame.state().get('selection').map(function(attachment) {
                    return attachment.toJSON().id;
                });
                $input.val(ids.join(',')).trigger('change');
                setPreview($input, ids);
            });

            frame.open();
        });

        $(document).on('click', '.vmb-native-tab-nav button', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            var $tabs = $(this).closest('.vmb-native-tabs');

            $tabs.find('.vmb-native-tab-nav button').removeClass('is-active');
            $tabs.find('.vmb-native-panel').removeClass('is-active');
            $(this).addClass('is-active');
            $tabs.find('#' + target).addClass('is-active');
        });
    })(jQuery);
    </script>
    <?php
}

function vmb_native_register_meta_boxes() {
    add_meta_box( 'vmb_room_settings', 'Room Settings', 'vmb_native_render_room_meta_box', 'vmb_room', 'normal', 'high' );
    add_meta_box( 'vmb_display_options', 'Display Options', 'vmb_native_render_display_options_meta_box', array( 'page', 'vmb_room' ), 'side', 'default' );
    add_meta_box( 'vmb_gallery_settings', 'Gallery Images', 'vmb_native_render_gallery_meta_box', 'vmb_gallery', 'normal', 'high' );
    add_meta_box( 'vmb_amenity_settings', 'Amenity Details', 'vmb_native_render_amenity_meta_box', 'amenity', 'normal', 'default' );
    add_meta_box( 'vmb_golf_course_settings', 'Golf Course Data', 'vmb_native_render_golf_course_meta_box', 'golf-course', 'normal', 'default' );
    add_meta_box( 'vmb_post_icon_settings', 'Display Icon', 'vmb_native_render_post_icon_meta_box', 'post', 'side', 'default' );

    if ( post_type_exists( 'vmb_reviews' ) ) {
        add_meta_box( 'vmb_review_settings', 'Review Display', 'vmb_native_render_review_meta_box', 'vmb_reviews', 'side', 'default' );
    }
}

function vmb_native_render_room_meta_box( $post ) {
    wp_nonce_field( 'vmb_native_save_post_meta', 'vmb_native_fields_nonce' );

    vmb_native_tabs(
        array(
            'details'   => array(
                'label'    => 'Details',
                'callback' => function () use ( $post ) {
                    vmb_native_textarea_field( 'vmb_short_description', 'Short Description', vmb_get_field( 'short_description', $post->ID ) );
                    vmb_native_image_field( 'vmb_room_layout', 'Room Layout Image', get_post_meta( $post->ID, 'room_layout', true ) );
                },
            ),
            'booking'   => array(
                'label'    => 'Booking',
                'callback' => function () use ( $post ) {
                    vmb_native_text_field( 'vmb_unit_id', 'Unit ID', vmb_get_field( 'unit_id', $post->ID ) );
                    vmb_native_text_field( 'vmb_custom_external_url', 'Custom External Booking URL', vmb_get_field( 'custom_external_url', $post->ID ), 'url' );
                },
            ),
            'amenities' => array(
                'label'    => 'Amenities',
                'callback' => function () use ( $post ) {
                    vmb_native_repeater_table( 'vmb_amenities', array( 'icon' => 'Icon Class', 'name' => 'Amenity Name' ), vmb_get_field( 'amenities', $post->ID ), 20 );
                },
            ),
            'gallery'   => array(
                'label'    => 'Gallery',
                'callback' => function () use ( $post ) {
                    vmb_native_gallery_field( 'vmb_gallery', 'Gallery Images', get_post_meta( $post->ID, 'gallery', true ) );
                },
            ),
        )
    );
}

function vmb_native_render_display_options_meta_box( $post ) {
    wp_nonce_field( 'vmb_native_save_post_meta', 'vmb_native_fields_nonce' );
    echo '<input type="hidden" name="vmb_display_options_present" value="1">';
    vmb_native_checkbox_field( 'vmb_show_booking_widget', 'Show Booking Widget', vmb_get_field( 'show_booking_widget', $post->ID ) );
    vmb_native_checkbox_field( 'vmb_show_countdown_timer', 'Show Countdown Timer', vmb_get_field( 'show_countdown_timer', $post->ID ) );
    vmb_native_checkbox_field( 'vmb_show_availability_calendar', 'Show Availability Calendar', vmb_get_field( 'show_availability_calendar', $post->ID ) );
    vmb_native_checkbox_field( 'vmb_hide_from_sitemap', 'Hide from Sitemap', vmb_get_field( 'hide_from_sitemap', $post->ID ) );
}

function vmb_native_render_gallery_meta_box( $post ) {
    wp_nonce_field( 'vmb_native_save_post_meta', 'vmb_native_fields_nonce' );
    vmb_native_gallery_field( 'vmb_gallery_images', 'Images', get_post_meta( $post->ID, 'gallery_images', true ) );
}

function vmb_native_render_amenity_meta_box( $post ) {
    wp_nonce_field( 'vmb_native_save_post_meta', 'vmb_native_fields_nonce' );
    vmb_native_gallery_field( 'vmb_gallery', 'Gallery', get_post_meta( $post->ID, 'gallery', true ) );
}

function vmb_native_render_golf_course_meta_box( $post ) {
    wp_nonce_field( 'vmb_native_save_post_meta', 'vmb_native_fields_nonce' );

    vmb_native_tabs(
        array(
            'course-details' => array(
                'label'    => 'Details',
                'callback' => function () use ( $post ) {
                    vmb_native_text_field( 'vmb_course_name', 'Course Name', vmb_get_field( 'course_name', $post->ID ) );
                    vmb_native_textarea_field( 'vmb_description', 'Description', vmb_get_field( 'description', $post->ID ) );
                    vmb_native_textarea_field( 'vmb_course_details', 'Course Details', vmb_get_field( 'course_details', $post->ID ) );
                },
            ),
            'course-media'   => array(
                'label'    => 'Media',
                'callback' => function () use ( $post ) {
                    vmb_native_image_field( 'vmb_main_image', 'Main Image', get_post_meta( $post->ID, 'main_image', true ) );
                },
            ),
            'course-amenities' => array(
                'label'    => 'Amenities',
                'callback' => function () use ( $post ) {
                    vmb_native_textarea_field( 'vmb_golf_amenities', 'Amenities', get_post_meta( $post->ID, 'amenities', true ) );
                },
            ),
        )
    );
}

function vmb_native_render_post_icon_meta_box( $post ) {
    wp_nonce_field( 'vmb_native_save_post_meta', 'vmb_native_fields_nonce' );
    vmb_native_text_field( 'vmb_icon', 'Icon Class', vmb_get_field( 'icon', $post->ID ) );
}

function vmb_native_render_review_meta_box( $post ) {
    wp_nonce_field( 'vmb_native_save_post_meta', 'vmb_native_fields_nonce' );
    vmb_native_checkbox_field( 'vmb_hide_from_query', 'Hide from Query', vmb_get_field( 'hide_from_query', $post->ID ) );
}

function vmb_native_save_post_meta( $post_id ) {
    if ( ! isset( $_POST['vmb_native_fields_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vmb_native_fields_nonce'] ) ), 'vmb_native_save_post_meta' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $post_type = get_post_type( $post_id );

    if ( 'vmb_room' === $post_type ) {
        vmb_update_post_field( $post_id, 'short_description', isset( $_POST['vmb_short_description'] ) ? wp_kses_post( wp_unslash( $_POST['vmb_short_description'] ) ) : '' );
        vmb_update_post_field( $post_id, 'room_layout', isset( $_POST['vmb_room_layout'] ) ? absint( $_POST['vmb_room_layout'] ) : '' );
        vmb_update_post_field( $post_id, 'unit_id', isset( $_POST['vmb_unit_id'] ) ? sanitize_text_field( wp_unslash( $_POST['vmb_unit_id'] ) ) : '' );
        vmb_update_post_field( $post_id, 'custom_external_url', isset( $_POST['vmb_custom_external_url'] ) ? esc_url_raw( wp_unslash( $_POST['vmb_custom_external_url'] ) ) : '' );
        vmb_update_post_field( $post_id, 'gallery', isset( $_POST['vmb_gallery'] ) ? vmb_sanitize_gallery_ids( wp_unslash( $_POST['vmb_gallery'] ) ) : array() );
        vmb_update_repeater_field( $post_id, 'amenities', vmb_native_sanitize_repeater_rows( 'vmb_amenities', array( 'icon', 'name' ) ), array( 'icon', 'name' ) );
    }

    if ( isset( $_POST['vmb_display_options_present'] ) ) {
        foreach ( array(
            'show_booking_widget'        => 'vmb_show_booking_widget',
            'show_countdown_timer'       => 'vmb_show_countdown_timer',
            'show_availability_calendar'=> 'vmb_show_availability_calendar',
            'hide_from_sitemap'          => 'vmb_hide_from_sitemap',
        ) as $meta_key => $input_name ) {
            vmb_update_post_field( $post_id, $meta_key, isset( $_POST[ $input_name ] ) ? '1' : '0' );
        }
    }

    if ( 'vmb_gallery' === $post_type ) {
        vmb_update_post_field( $post_id, 'gallery_images', isset( $_POST['vmb_gallery_images'] ) ? vmb_sanitize_gallery_ids( wp_unslash( $_POST['vmb_gallery_images'] ) ) : array() );
    }

    if ( 'amenity' === $post_type ) {
        vmb_update_post_field( $post_id, 'gallery', isset( $_POST['vmb_gallery'] ) ? vmb_sanitize_gallery_ids( wp_unslash( $_POST['vmb_gallery'] ) ) : array() );
    }

    if ( 'golf-course' === $post_type ) {
        vmb_update_post_field( $post_id, 'course_name', isset( $_POST['vmb_course_name'] ) ? sanitize_text_field( wp_unslash( $_POST['vmb_course_name'] ) ) : '' );
        vmb_update_post_field( $post_id, 'description', isset( $_POST['vmb_description'] ) ? wp_kses_post( wp_unslash( $_POST['vmb_description'] ) ) : '' );
        vmb_update_post_field( $post_id, 'course_details', isset( $_POST['vmb_course_details'] ) ? wp_kses_post( wp_unslash( $_POST['vmb_course_details'] ) ) : '' );
        vmb_update_post_field( $post_id, 'main_image', isset( $_POST['vmb_main_image'] ) ? absint( $_POST['vmb_main_image'] ) : '' );
        vmb_update_post_field( $post_id, 'amenities', isset( $_POST['vmb_golf_amenities'] ) ? wp_kses_post( wp_unslash( $_POST['vmb_golf_amenities'] ) ) : '' );
    }

    if ( 'post' === $post_type && isset( $_POST['vmb_icon'] ) ) {
        vmb_update_post_field( $post_id, 'icon', sanitize_text_field( wp_unslash( $_POST['vmb_icon'] ) ) );
    }

    if ( 'vmb_reviews' === $post_type && isset( $_POST['vmb_hide_from_query'] ) ) {
        vmb_update_post_field( $post_id, 'hide_from_query', '1' );
    } elseif ( 'vmb_reviews' === $post_type ) {
        vmb_update_post_field( $post_id, 'hide_from_query', '0' );
    }
}

function vmb_native_render_site_settings_page() {
    ?>
    <div class="wrap vmb-native-settings">
        <h1>Site Settings</h1>
        <?php if ( isset( $_GET['settings-updated'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="vmb_save_site_settings">
            <?php wp_nonce_field( 'vmb_native_save_site_settings', 'vmb_native_settings_nonce' ); ?>
            <?php
            vmb_native_tabs(
                array(
                    'settings-general' => array(
                        'label'    => 'General',
                        'callback' => 'vmb_native_render_site_settings_general',
                    ),
                    'settings-contact' => array(
                        'label'    => 'Contact',
                        'callback' => 'vmb_native_render_site_settings_contact',
                    ),
                    'settings-booking' => array(
                        'label'    => 'Booking',
                        'callback' => 'vmb_native_render_site_settings_booking',
                    ),
                    'settings-announcement' => array(
                        'label'    => 'Announcement',
                        'callback' => 'vmb_native_render_site_settings_announcement',
                    ),
                    'settings-integrations' => array(
                        'label'    => 'Integrations',
                        'callback' => 'vmb_native_render_site_settings_integrations',
                    ),
                    'settings-advanced' => array(
                        'label'    => 'Advanced',
                        'callback' => 'vmb_native_render_site_settings_advanced',
                    ),
                )
            );
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}

function vmb_native_render_site_settings_general() {
    echo '<table class="form-table" role="presentation"><tbody>';
    vmb_native_settings_image_row( 'vmb_preview_image', 'Preview Image', get_option( 'options_preview_image', '' ) );
    vmb_native_settings_text_row( 'vmb_site_id', 'Site ID', vmb_get_field( 'site_id', 'option' ) );
    vmb_native_settings_text_row( 'vmb_site_name', 'Site Name', vmb_get_field( 'site_name', 'option' ) );
    vmb_native_settings_textarea_row( 'vmb_address', 'Address', vmb_get_field( 'address', 'option' ) );
    vmb_native_settings_text_row( 'vmb_geolocation_latitude', 'Latitude', get_option( 'options_geolocation_latitude', '' ) );
    vmb_native_settings_text_row( 'vmb_geolocation_longitude', 'Longitude', get_option( 'options_geolocation_longitude', '' ) );
    echo '</tbody></table>';
}

function vmb_native_render_site_settings_contact() {
    echo '<table class="form-table" role="presentation"><tbody>';
    vmb_native_settings_text_row( 'vmb_email_address', 'Email Address', vmb_get_field( 'email_address', 'option' ), 'email' );
    vmb_native_settings_text_row( 'vmb_phone_number', 'Phone Number', vmb_get_field( 'phone_number', 'option' ) );
    vmb_native_settings_text_row( 'vmb_groups_phone_number', 'Groups Phone Number', vmb_get_field( 'groups_phone_number', 'option' ) );
    echo '<tr><th scope="row">Social Media</th><td>';
    vmb_native_repeater_table( 'vmb_social_media', array( 'icon' => 'Icon Class', 'url' => 'URL' ), vmb_get_field( 'social_media', 'option' ), 8 );
    echo '</td></tr>';
    echo '</tbody></table>';
}

function vmb_native_render_site_settings_booking() {
    echo '<table class="form-table" role="presentation"><tbody>';
    vmb_native_settings_textarea_row( 'vmb_dynamic_phone_numbers', 'Dynamic Phone Numbers', vmb_get_field( 'dynamic_phone_numbers', 'option' ) );
    echo '</tbody></table>';
}

function vmb_native_render_site_settings_announcement() {
    echo '<table class="form-table" role="presentation"><tbody>';
    echo '<tr><th scope="row">Show Announcement Banner</th><td>';
    vmb_native_checkbox_field( 'vmb_show_announcement_banner', 'Enabled', vmb_get_field( 'show_announcement_banner', 'option' ) );
    echo '</td></tr>';
    vmb_native_settings_textarea_row( 'vmb_announcement_banner_message', 'Banner Message', vmb_get_field( 'announcement_banner_message', 'option' ) );
    vmb_native_settings_text_row( 'vmb_announcement_banner_url', 'Banner URL', vmb_get_field( 'announcement_banner_url', 'option' ), 'url' );
    echo '</tbody></table>';
}

function vmb_native_render_site_settings_integrations() {
    $alchemer  = vmb_get_field( 'alchemer_settings', 'option' );
    $guestdesk = vmb_get_field( 'guestdesk_settings', 'option' );

    echo '<table class="form-table" role="presentation"><tbody>';
    vmb_native_settings_text_row( 'vmb_guestdesk_username', 'Guestdesk Username', $guestdesk['username'] );
    vmb_native_settings_text_row( 'vmb_guestdesk_password', 'Guestdesk Password', $guestdesk['password'], 'password' );
    vmb_native_settings_text_row( 'vmb_alchemer_api_token', 'Alchemer API Token', $alchemer['api_token'], 'password' );
    vmb_native_settings_text_row( 'vmb_alchemer_api_secret', 'Alchemer API Secret', $alchemer['api_secret'], 'password' );
    vmb_native_settings_text_row( 'vmb_alchemer_review_field_id', 'Alchemer Review Field ID', $alchemer['review_field_id'] );
    vmb_native_settings_text_row( 'vmb_alchemer_minimum_rating', 'Minimum Rating', $alchemer['sync_preference']['minimum_rating'], 'number' );
    vmb_native_settings_text_row( 'vmb_alchemer_reviews_to_pull', 'Reviews to Pull', $alchemer['sync_preference']['reviews_to_pull'], 'number' );
    echo '</tbody></table>';
}

function vmb_native_render_site_settings_advanced() {
    echo '<p>Plugin installation and theme update settings remain handled by the existing theme tools.</p>';
}

function vmb_native_save_site_settings() {
    if ( ! current_user_can( 'edit_theme_options' ) || ! isset( $_POST['vmb_native_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vmb_native_settings_nonce'] ) ), 'vmb_native_save_site_settings' ) ) {
        wp_die( 'You are not allowed to save these settings.' );
    }

    $text_fields = array(
        'preview_image'                                            => array( 'vmb_preview_image', 'absint' ),
        'site_id'                                                  => array( 'vmb_site_id', 'sanitize_text_field' ),
        'site_name'                                                => array( 'vmb_site_name', 'sanitize_text_field' ),
        'email_address'                                            => array( 'vmb_email_address', 'sanitize_email' ),
        'phone_number'                                             => array( 'vmb_phone_number', 'sanitize_text_field' ),
        'groups_phone_number'                                      => array( 'vmb_groups_phone_number', 'sanitize_text_field' ),
        'announcement_banner_url'                                  => array( 'vmb_announcement_banner_url', 'esc_url_raw' ),
        'guestdesk_settings_username'                              => array( 'vmb_guestdesk_username', 'sanitize_text_field' ),
        'guestdesk_settings_password'                              => array( 'vmb_guestdesk_password', 'sanitize_text_field' ),
        'alchemer_settings_api_token'                              => array( 'vmb_alchemer_api_token', 'sanitize_text_field' ),
        'alchemer_settings_api_secret'                             => array( 'vmb_alchemer_api_secret', 'sanitize_text_field' ),
        'alchemer_settings_review_field_id'                        => array( 'vmb_alchemer_review_field_id', 'sanitize_text_field' ),
        'alchemer_settings_sync_preference_minimum_rating'         => array( 'vmb_alchemer_minimum_rating', 'absint' ),
        'alchemer_settings_sync_preference_reviews_to_pull'        => array( 'vmb_alchemer_reviews_to_pull', 'absint' ),
        'geolocation_latitude'                                     => array( 'vmb_geolocation_latitude', 'sanitize_text_field' ),
        'geolocation_longitude'                                    => array( 'vmb_geolocation_longitude', 'sanitize_text_field' ),
    );

    foreach ( $text_fields as $option_key => $config ) {
        list( $input_name, $sanitize_callback ) = $config;
        $value = isset( $_POST[ $input_name ] ) ? wp_unslash( $_POST[ $input_name ] ) : '';
        vmb_update_option_field( $option_key, call_user_func( $sanitize_callback, $value ) );
    }

    vmb_update_option_field( 'address', isset( $_POST['vmb_address'] ) ? wp_kses_post( wp_unslash( $_POST['vmb_address'] ) ) : '' );
    vmb_update_option_field( 'dynamic_phone_numbers', isset( $_POST['vmb_dynamic_phone_numbers'] ) ? sanitize_textarea_field( wp_unslash( $_POST['vmb_dynamic_phone_numbers'] ) ) : '' );
    vmb_update_option_field( 'announcement_banner_message', isset( $_POST['vmb_announcement_banner_message'] ) ? wp_kses_post( wp_unslash( $_POST['vmb_announcement_banner_message'] ) ) : '' );
    vmb_update_option_field( 'show_announcement_banner', isset( $_POST['vmb_show_announcement_banner'] ) ? '1' : '0' );
    vmb_update_option_repeater_field( 'social_media', vmb_native_sanitize_repeater_rows( 'vmb_social_media', array( 'icon', 'url' ) ), array( 'icon', 'url' ) );

    wp_safe_redirect( add_query_arg( 'settings-updated', 'true', admin_url( 'admin.php?page=site-settings' ) ) );
    exit;
}

function vmb_native_render_term_add_fields() {
    wp_nonce_field( 'vmb_native_save_term_fields', 'vmb_native_term_nonce' );
    ?>
    <div class="form-field">
        <label for="vmb_term_featured_image">Featured Image</label>
        <?php vmb_native_image_field( 'vmb_term_featured_image', '', '' ); ?>
    </div>
    <div class="form-field">
        <label for="vmb_term_heading">Heading</label>
        <input type="text" name="vmb_term_heading" id="vmb_term_heading" value="">
    </div>
    <div class="form-field">
        <label for="vmb_term_content">Content</label>
        <textarea name="vmb_term_content" id="vmb_term_content" rows="5"></textarea>
    </div>
    <?php
}

function vmb_native_render_term_edit_fields( $term ) {
    wp_nonce_field( 'vmb_native_save_term_fields', 'vmb_native_term_nonce' );
    ?>
    <tr class="form-field">
        <th scope="row"><label for="vmb_term_featured_image">Featured Image</label></th>
        <td><?php vmb_native_image_field( 'vmb_term_featured_image', '', get_term_meta( $term->term_id, 'featured_image', true ) ); ?></td>
    </tr>
    <tr class="form-field">
        <th scope="row"><label for="vmb_term_heading">Heading</label></th>
        <td><input type="text" name="vmb_term_heading" id="vmb_term_heading" value="<?php echo esc_attr( get_term_meta( $term->term_id, 'heading', true ) ); ?>"></td>
    </tr>
    <tr class="form-field">
        <th scope="row"><label for="vmb_term_content">Content</label></th>
        <td><textarea name="vmb_term_content" id="vmb_term_content" rows="5"><?php echo esc_textarea( get_term_meta( $term->term_id, 'content', true ) ); ?></textarea></td>
    </tr>
    <?php
}

function vmb_native_save_term_fields( $term_id ) {
    if ( ! isset( $_POST['vmb_native_term_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vmb_native_term_nonce'] ) ), 'vmb_native_save_term_fields' ) ) {
        return;
    }

    update_term_meta( $term_id, 'featured_image', isset( $_POST['vmb_term_featured_image'] ) ? absint( $_POST['vmb_term_featured_image'] ) : '' );
    update_term_meta( $term_id, 'heading', isset( $_POST['vmb_term_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['vmb_term_heading'] ) ) : '' );
    update_term_meta( $term_id, 'content', isset( $_POST['vmb_term_content'] ) ? wp_kses_post( wp_unslash( $_POST['vmb_term_content'] ) ) : '' );
}

function vmb_native_tabs( $tabs ) {
    $first_key = array_key_first( $tabs );

    echo '<div class="vmb-native-tabs">';
    echo '<div class="vmb-native-tab-nav">';
    foreach ( $tabs as $key => $tab ) {
        printf( '<button type="button" class="%s" data-target="%s">%s</button>', $key === $first_key ? 'is-active' : '', esc_attr( $key ), esc_html( $tab['label'] ) );
    }
    echo '</div>';

    foreach ( $tabs as $key => $tab ) {
        printf( '<div id="%s" class="vmb-native-panel %s">', esc_attr( $key ), $key === $first_key ? 'is-active' : '' );
        call_user_func( $tab['callback'] );
        echo '</div>';
    }

    echo '</div>';
}

function vmb_native_text_field( $name, $label, $value, $type = 'text' ) {
    printf(
        '<p class="vmb-native-field"><label for="%1$s">%2$s</label><input type="%3$s" id="%1$s" name="%1$s" value="%4$s"></p>',
        esc_attr( $name ),
        esc_html( $label ),
        esc_attr( $type ),
        esc_attr( $value )
    );
}

function vmb_native_textarea_field( $name, $label, $value ) {
    printf(
        '<p class="vmb-native-field"><label for="%1$s">%2$s</label><textarea id="%1$s" name="%1$s" rows="6">%3$s</textarea></p>',
        esc_attr( $name ),
        esc_html( $label ),
        esc_textarea( is_array( $value ) ? implode( "\n", $value ) : $value )
    );
}

function vmb_native_checkbox_field( $name, $label, $value ) {
    printf(
        '<label><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label>',
        esc_attr( $name ),
        checked( (bool) $value, true, false ),
        esc_html( $label )
    );
}

function vmb_native_image_field( $name, $label, $attachment_id ) {
    $attachment_id = absint( $attachment_id );
    $image         = $attachment_id ? vmb_format_image_value( $attachment_id ) : false;

    echo '<div class="vmb-native-field">';
    if ( $label ) {
        printf( '<label for="%s">%s</label>', esc_attr( $name ), esc_html( $label ) );
    }
    printf( '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text"> ', esc_attr( $name ), esc_attr( $attachment_id ) );
    echo '<button type="button" class="button vmb-native-select-image">Select Image</button>';
    echo '<div class="vmb-native-image-preview">';
    if ( $image ) {
        printf( '<img src="%s" alt="">', esc_url( $image['url'] ) );
    }
    echo '</div></div>';
}

function vmb_native_gallery_field( $name, $label, $value ) {
    $ids    = vmb_sanitize_gallery_ids( $value );
    $images = vmb_format_gallery_value( $ids );

    echo '<div class="vmb-native-field">';
    printf( '<label for="%1$s">%2$s</label>', esc_attr( $name ), esc_html( $label ) );
    printf( '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="large-text"> ', esc_attr( $name ), esc_attr( implode( ',', $ids ) ) );
    echo '<button type="button" class="button vmb-native-select-gallery">Select Images</button>';
    echo '<div class="vmb-native-gallery-preview">';
    foreach ( $images as $image ) {
        printf( '<img src="%s" alt="">', esc_url( $image['url'] ) );
    }
    echo '</div></div>';
}

function vmb_native_repeater_table( $name, $columns, $rows, $max_rows = 10 ) {
    $rows = is_array( $rows ) ? array_values( $rows ) : array();

    echo '<table class="vmb-native-row-table"><thead><tr>';
    foreach ( $columns as $label ) {
        printf( '<th>%s</th>', esc_html( $label ) );
    }
    echo '</tr></thead><tbody>';

    for ( $i = 0; $i < $max_rows; $i++ ) {
        $row = isset( $rows[ $i ] ) && is_array( $rows[ $i ] ) ? $rows[ $i ] : array();
        echo '<tr>';
        foreach ( $columns as $key => $label ) {
            printf(
                '<td><input type="text" name="%1$s[%2$d][%3$s]" value="%4$s" placeholder="%5$s"></td>',
                esc_attr( $name ),
                absint( $i ),
                esc_attr( $key ),
                esc_attr( isset( $row[ $key ] ) ? $row[ $key ] : '' ),
                esc_attr( $label )
            );
        }
        echo '</tr>';
    }

    echo '</tbody></table>';
}

function vmb_native_sanitize_repeater_rows( $input_name, $subfields ) {
    if ( empty( $_POST[ $input_name ] ) || ! is_array( $_POST[ $input_name ] ) ) {
        return array();
    }

    $rows = array();

    foreach ( wp_unslash( $_POST[ $input_name ] ) as $row ) {
        if ( ! is_array( $row ) ) {
            continue;
        }

        $clean = array();
        foreach ( $subfields as $subfield ) {
            $value = isset( $row[ $subfield ] ) ? $row[ $subfield ] : '';
            $clean[ $subfield ] = 'url' === $subfield ? esc_url_raw( $value ) : sanitize_text_field( $value );
        }

        if ( array_filter( $clean, 'strlen' ) ) {
            $rows[] = $clean;
        }
    }

    return $rows;
}

function vmb_native_settings_text_row( $name, $label, $value, $type = 'text' ) {
    printf( '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="%3$s" id="%1$s" name="%1$s" value="%4$s" class="regular-text"></td></tr>', esc_attr( $name ), esc_html( $label ), esc_attr( $type ), esc_attr( $value ) );
}

function vmb_native_settings_textarea_row( $name, $label, $value ) {
    printf( '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><textarea id="%1$s" name="%1$s" rows="6" class="large-text">%3$s</textarea></td></tr>', esc_attr( $name ), esc_html( $label ), esc_textarea( $value ) );
}

function vmb_native_settings_image_row( $name, $label, $value ) {
    echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';
    vmb_native_image_field( $name, '', $value );
    echo '</td></tr>';
}
