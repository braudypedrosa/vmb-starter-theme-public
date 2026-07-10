<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'VMB_ALCHEMER_REVIEWS_DAILY_HOOK', 'vmb_alchemer_reviews_daily_sync' );

function vmb_reviews_register_sync_hooks() {
    if ( ! function_exists( 'add_action' ) ) {
        return;
    }

    add_action( 'init', 'vmb_reviews_schedule_daily_sync' );
    add_action( 'switch_theme', 'vmb_reviews_clear_daily_sync' );
    add_action( VMB_ALCHEMER_REVIEWS_DAILY_HOOK, 'vmb_reviews_run_daily_sync' );
    add_action( 'admin_post_vmb_sync_alchemer_reviews', 'vmb_reviews_handle_manual_sync' );
    add_action( 'admin_post_vmb_cleanup_review_duplicates', 'vmb_reviews_handle_duplicate_cleanup' );
    add_action( 'save_post_vmb_reviews', 'vmb_reviews_mark_manual_update', 10, 3 );
    add_action( 'admin_notices', 'vmb_reviews_pending_notice' );
    add_action( 'vmb_native_after_integrations_settings', 'vmb_reviews_render_settings_controls' );
    add_filter( 'manage_vmb_reviews_posts_columns', 'vmb_reviews_add_admin_columns', 30 );
    add_action( 'manage_vmb_reviews_posts_custom_column', 'vmb_reviews_render_admin_column', 30, 2 );
}

vmb_reviews_register_sync_hooks();

function vmb_reviews_build_unique_id( $connected_property, $site_id, $response_id ) {
    $parts = array();

    $property = sanitize_title( $connected_property );
    if ( '' !== $property ) {
        $parts[] = $property;
    }

    $response_id = sanitize_title( $response_id );
    if ( '' !== $response_id ) {
        $parts[] = $response_id;
    }

    return implode( '-', array_filter( $parts ) );
}

function vmb_reviews_prepare_alchemer_review( $response, $field_id, $site_id, $connected_property, $minimum_rating = 5 ) {
    if ( empty( $response['id'] ) || empty( $response['survey_data'] ) || ! is_array( $response['survey_data'] ) ) {
        return null;
    }

    $field = vmb_reviews_get_response_field( $response['survey_data'], $field_id );
    if ( ! $field || empty( $field['comments'] ) ) {
        return null;
    }

    $rating = isset( $field['answer'] ) ? absint( $field['answer'] ) : 0;
    if ( $rating < absint( $minimum_rating ) ) {
        return null;
    }

    $comment = trim( wp_strip_all_tags( $field['comments'] ) );
    if ( '' === $comment ) {
        return null;
    }

    $first_name = vmb_reviews_get_response_variable( $response, 'firstname' );
    $last_name  = vmb_reviews_get_response_variable( $response, 'lastname' );
    $title      = trim( ucwords( strtolower( trim( $first_name . ' ' . $last_name ) ) ) );

    if ( '' === $title ) {
        $title = 'Alchemer Review ' . sanitize_text_field( $response['id'] );
    }

    return array(
        'response_id'        => sanitize_text_field( $response['id'] ),
        'unique_id'          => vmb_reviews_build_unique_id( $connected_property, $site_id, $response['id'] ),
        'site_id'            => sanitize_text_field( $site_id ),
        'connected_property' => sanitize_text_field( $connected_property ),
        'title'              => sanitize_text_field( $title ),
        'first_name'         => sanitize_text_field( ucwords( strtolower( $first_name ) ) ),
        'last_name'          => sanitize_text_field( ucwords( strtolower( $last_name ) ) ),
        'comment'            => $comment,
        'rating'             => $rating,
        'date_submitted'     => isset( $response['date_submitted'] ) ? sanitize_text_field( $response['date_submitted'] ) : '',
    );
}

function vmb_reviews_get_response_field( $survey_data, $field_id ) {
    if ( isset( $survey_data[ $field_id ] ) ) {
        return $survey_data[ $field_id ];
    }

    foreach ( $survey_data as $key => $field ) {
        if ( (string) $key === (string) $field_id ) {
            return $field;
        }
    }

    return null;
}

function vmb_reviews_get_response_variable( $response, $key ) {
    if ( empty( $response['url_variables'] ) || ! is_array( $response['url_variables'] ) ) {
        return '';
    }

    foreach ( $response['url_variables'] as $variable_key => $variable ) {
        if ( strtolower( $variable_key ) === strtolower( $key ) ) {
            return isset( $variable['value'] ) ? $variable['value'] : '';
        }
    }

    return '';
}

function vmb_reviews_can_overwrite_post( $post_id ) {
    $is_modified = (bool) get_post_meta( $post_id, 'review_modified', true ) || (bool) get_post_meta( $post_id, '_vmb_review_manually_edited', true );

    if ( ! $is_modified ) {
        return true;
    }

    return (bool) get_post_meta( $post_id, 'include_in_sync', true );
}

function vmb_reviews_get_settings() {
    $alchemer = function_exists( 'vmb_get_field' ) ? vmb_get_field( 'alchemer_settings', 'option' ) : array();

    return array(
        'api_token'       => isset( $alchemer['api_token'] ) ? $alchemer['api_token'] : '',
        'api_secret'      => isset( $alchemer['api_secret'] ) ? $alchemer['api_secret'] : '',
        'review_field_id' => isset( $alchemer['review_field_id'] ) ? $alchemer['review_field_id'] : '',
        'minimum_rating'  => ! empty( $alchemer['sync_preference']['minimum_rating'] ) ? absint( $alchemer['sync_preference']['minimum_rating'] ) : 5,
        'reviews_to_pull' => ! empty( $alchemer['sync_preference']['reviews_to_pull'] ) ? absint( $alchemer['sync_preference']['reviews_to_pull'] ) : 20,
        'site_id'         => function_exists( 'vmb_get_field' ) ? vmb_get_field( 'site_id', 'option' ) : '',
        'site_name'       => function_exists( 'vmb_get_field' ) ? vmb_get_field( 'site_name', 'option' ) : '',
    );
}

function vmb_reviews_get_sync_targets( $resort_id = 0 ) {
    $settings = vmb_reviews_get_settings();
    $targets  = array();

    if ( function_exists( 'post_type_exists' ) && post_type_exists( 'resort' ) ) {
        $args = array(
            'post_type'      => 'resort',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );

        if ( $resort_id ) {
            $args['post__in'] = array( absint( $resort_id ) );
        }

        foreach ( get_posts( $args ) as $resort ) {
            $site_id         = vmb_reviews_get_field_value( 'site_id', $resort->ID );
            $review_field_id = vmb_reviews_get_field_value( 'review_field_id', $resort->ID );

            if ( '' === $review_field_id ) {
                $review_field_id = $settings['review_field_id'];
            }

            if ( '' === $site_id || '' === $review_field_id ) {
                continue;
            }

            $targets[] = array(
                'post_id'            => $resort->ID,
                'site_id'            => $site_id,
                'review_field_id'    => $review_field_id,
                'connected_property' => get_the_title( $resort ),
            );
        }
    }

    if ( empty( $targets ) && ! $resort_id && '' !== $settings['site_id'] && '' !== $settings['review_field_id'] ) {
        $targets[] = array(
            'post_id'            => 0,
            'site_id'            => $settings['site_id'],
            'review_field_id'    => $settings['review_field_id'],
            'connected_property' => $settings['site_name'] ? $settings['site_name'] : get_bloginfo( 'name' ),
        );
    }

    return $targets;
}

function vmb_reviews_get_field_value( $key, $post_id ) {
    if ( function_exists( 'vmb_get_field' ) ) {
        $value = vmb_get_field( $key, $post_id );
        if ( '' !== $value && null !== $value ) {
            return $value;
        }
    }

    return get_post_meta( $post_id, $key, true );
}

function vmb_reviews_run_sync( $args = array() ) {
    $defaults = array(
        'source'           => 'manual',
        'new_status'       => 'publish',
        'resort_id'        => 0,
        'minimum_rating'   => 0,
        'limit'            => 0,
        'stop_at_existing' => false,
    );
    $args     = wp_parse_args( $args, $defaults );

    if ( function_exists( 'post_type_exists' ) && ! post_type_exists( 'vmb_reviews' ) ) {
        return array(
            'code'    => 'fail',
            'message' => 'The vmb_reviews post type is not available.',
            'stats'   => array(),
        );
    }

    $settings = vmb_reviews_get_settings();
    if ( empty( $settings['api_token'] ) || empty( $settings['api_secret'] ) ) {
        return array(
            'code'    => 'fail',
            'message' => 'Alchemer API token and secret are required.',
            'stats'   => array(),
        );
    }

    $minimum_rating = $args['minimum_rating'] ? absint( $args['minimum_rating'] ) : $settings['minimum_rating'];
    $limit          = $args['limit'] ? absint( $args['limit'] ) : $settings['reviews_to_pull'];
    $targets        = vmb_reviews_get_sync_targets( $args['resort_id'] );
    $totals         = array(
        'created'   => 0,
        'updated'   => 0,
        'protected' => 0,
        'skipped'   => 0,
        'errors'    => 0,
    );

    foreach ( $targets as $target ) {
        $result = vmb_reviews_sync_target( $target, $settings, array(
            'source'           => $args['source'],
            'new_status'       => $args['new_status'],
            'minimum_rating'   => $minimum_rating,
            'limit'            => $limit,
            'stop_at_existing' => (bool) $args['stop_at_existing'],
        ) );

        foreach ( $totals as $key => $value ) {
            $totals[ $key ] += isset( $result['stats'][ $key ] ) ? absint( $result['stats'][ $key ] ) : 0;
        }
    }

    update_option( 'vmb_reviews_last_sync_stats', $totals, false );
    update_option( 'vmb_reviews_last_sync_at', current_time( 'mysql' ), false );

    return array(
        'code'    => $totals['errors'] ? 'fail' : 'success',
        'message' => sprintf(
            'Review sync complete. Created: %d. Updated: %d. Protected: %d. Skipped: %d.',
            $totals['created'],
            $totals['updated'],
            $totals['protected'],
            $totals['skipped']
        ),
        'stats'   => $totals,
    );
}

function vmb_reviews_sync_target( $target, $settings, $args ) {
    $responses = vmb_reviews_fetch_alchemer_responses( $target, $settings, $args );
    $stats     = array(
        'created'   => 0,
        'updated'   => 0,
        'protected' => 0,
        'skipped'   => 0,
        'errors'    => 0,
    );

    if ( is_wp_error( $responses ) ) {
        $stats['errors']++;
        return array( 'stats' => $stats );
    }

    foreach ( $responses as $response ) {
        $review = vmb_reviews_prepare_alchemer_review( $response, $target['review_field_id'], $target['site_id'], $target['connected_property'], $args['minimum_rating'] );

        if ( ! $review ) {
            $stats['skipped']++;
            continue;
        }

        $post_id = vmb_reviews_find_review_post( $review['unique_id'], $review['response_id'], $review['site_id'] );

        if ( $post_id ) {
            if ( ! vmb_reviews_can_overwrite_post( $post_id ) ) {
                update_post_meta( $post_id, '_vmb_review_sync_status', 'protected' );
                update_post_meta( $post_id, '_vmb_review_sync_last_result', 'Manual edit protected from sync.' );
                $stats['protected']++;

                if ( $args['stop_at_existing'] ) {
                    break;
                }

                continue;
            }

            vmb_reviews_save_post_from_review( $post_id, $review, $target, $args['source'], 'synced' );
            $stats['updated']++;

            if ( $args['stop_at_existing'] ) {
                break;
            }

            continue;
        }

        $post_id = vmb_reviews_insert_review_post( $review, $args['new_status'] );
        if ( is_wp_error( $post_id ) || ! $post_id ) {
            $stats['errors']++;
            continue;
        }

        $sync_status = 'draft' === $args['new_status'] ? 'pending' : 'synced';
        vmb_reviews_save_post_from_review( $post_id, $review, $target, $args['source'], $sync_status );
        $stats['created']++;
    }

    return array( 'stats' => $stats );
}

function vmb_reviews_fetch_alchemer_responses( $target, $settings, $args ) {
    if ( ! function_exists( 'wp_remote_get' ) ) {
        return new WP_Error( 'vmb_reviews_http_unavailable', 'WordPress HTTP API is not available.' );
    }

    $limit    = max( 1, absint( $args['limit'] ) );
    $per_page = min( 100, $limit );
    $page     = 1;
    $data     = array();

    do {
        $params = array(
            'api_token'          => $settings['api_token'],
            'api_token_secret'   => $settings['api_secret'],
            'resultsperpage'     => $per_page,
            'page'               => $page,
            'filter[field][0]'   => '[question(' . $target['review_field_id'] . ')]',
            'filter[operator][0]'=> 'IS NOT NULL',
            'filter[field][1]'   => '[question(' . $target['review_field_id'] . ')]',
            'filter[operator][1]'=> '>=',
            'filter[value][1]'   => max( 1, absint( $args['minimum_rating'] ) ),
            'order_by'           => '-date_submitted',
        );

        $url      = add_query_arg( $params, 'https://api.alchemer.com/v5/survey/' . rawurlencode( $target['site_id'] ) . '/surveyresponse' );
        $response = wp_remote_get( $url, array( 'timeout' => 30 ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['result_ok'] ) ) {
            return new WP_Error( 'vmb_reviews_alchemer_error', ! empty( $body['message'] ) ? $body['message'] : 'Alchemer request failed.' );
        }

        $page_data = ! empty( $body['data'] ) && is_array( $body['data'] ) ? $body['data'] : array();
        foreach ( $page_data as $item ) {
            $data[] = $item;
            if ( count( $data ) >= $limit ) {
                break;
            }
        }

        $total_count = isset( $body['total_count'] ) ? absint( $body['total_count'] ) : count( $data );
        $page++;
    } while ( count( $data ) < $limit && count( $page_data ) === $per_page && count( $data ) < $total_count );

    return $data;
}

function vmb_reviews_find_review_post( $unique_id, $response_id = '', $site_id = '' ) {
    $meta_query = array(
        'relation' => 'OR',
        array(
            'key'   => 'vmb_review_id',
            'value' => $unique_id,
        ),
        array(
            'key'   => '_vmb_alchemer_unique_id',
            'value' => $unique_id,
        ),
    );

    if ( '' !== $response_id && '' !== $site_id ) {
        $meta_query[] = array(
            'relation' => 'AND',
            array(
                'key'   => '_vmb_alchemer_response_id',
                'value' => $response_id,
            ),
            array(
                'key'   => '_vmb_alchemer_site_id',
                'value' => $site_id,
            ),
        );
    }

    $posts = get_posts( array(
        'post_type'      => 'vmb_reviews',
        'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => $meta_query,
    ) );

    return $posts ? absint( $posts[0] ) : 0;
}

function vmb_reviews_insert_review_post( $review, $status ) {
    $GLOBALS['vmb_reviews_syncing'] = true;
    $post_id = wp_insert_post( array(
        'post_title'   => $review['title'],
        'post_type'    => 'vmb_reviews',
        'post_content' => $review['comment'],
        'post_status'  => $status,
    ), true );
    unset( $GLOBALS['vmb_reviews_syncing'] );

    return $post_id;
}

function vmb_reviews_save_post_from_review( $post_id, $review, $target, $source, $sync_status ) {
    $GLOBALS['vmb_reviews_syncing'] = true;
    wp_update_post( array(
        'ID'           => $post_id,
        'post_title'   => $review['title'],
        'post_content' => $review['comment'],
    ) );
    unset( $GLOBALS['vmb_reviews_syncing'] );

    update_post_meta( $post_id, 'vmb_review_id', $review['unique_id'] );
    update_post_meta( $post_id, 'vmb_review_firstname', $review['first_name'] );
    update_post_meta( $post_id, 'vmb_review_lastname', $review['last_name'] );
    update_post_meta( $post_id, 'vmb_review_comment', $review['comment'] );
    update_post_meta( $post_id, 'vmb_review_rating', $review['rating'] );
    update_post_meta( $post_id, 'connected_property', $target['connected_property'] );
    update_post_meta( $post_id, '_vmb_alchemer_unique_id', $review['unique_id'] );
    update_post_meta( $post_id, '_vmb_alchemer_response_id', $review['response_id'] );
    update_post_meta( $post_id, '_vmb_alchemer_site_id', $target['site_id'] );
    update_post_meta( $post_id, '_vmb_alchemer_review_field_id', $target['review_field_id'] );
    update_post_meta( $post_id, '_vmb_review_sync_source', $source );
    update_post_meta( $post_id, '_vmb_review_sync_status', $sync_status );
    update_post_meta( $post_id, '_vmb_review_sync_last_synced', current_time( 'mysql' ) );

    if ( get_post_meta( $post_id, 'include_in_sync', true ) ) {
        delete_post_meta( $post_id, 'review_modified' );
        delete_post_meta( $post_id, '_vmb_review_manually_edited' );
    }
}

function vmb_reviews_schedule_daily_sync() {
    if ( ! wp_next_scheduled( VMB_ALCHEMER_REVIEWS_DAILY_HOOK ) ) {
        wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', VMB_ALCHEMER_REVIEWS_DAILY_HOOK );
    }
}

function vmb_reviews_clear_daily_sync() {
    wp_clear_scheduled_hook( VMB_ALCHEMER_REVIEWS_DAILY_HOOK );
}

function vmb_reviews_run_daily_sync() {
    return vmb_reviews_run_sync( array(
        'source'           => 'daily',
        'new_status'       => 'draft',
        'stop_at_existing' => true,
    ) );
}

function vmb_reviews_handle_manual_sync() {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( 'You are not allowed to sync reviews.' );
    }

    check_admin_referer( 'vmb_sync_alchemer_reviews', 'vmb_reviews_sync_nonce' );

    $result = vmb_reviews_run_sync( array(
        'source'         => 'manual',
        'new_status'     => 'publish',
        'resort_id'      => isset( $_POST['vmb_reviews_resort_id'] ) ? absint( $_POST['vmb_reviews_resort_id'] ) : 0,
        'minimum_rating' => isset( $_POST['vmb_reviews_minimum_rating'] ) ? absint( $_POST['vmb_reviews_minimum_rating'] ) : 0,
        'limit'          => isset( $_POST['vmb_reviews_limit'] ) ? absint( $_POST['vmb_reviews_limit'] ) : 0,
    ) );

    wp_safe_redirect( add_query_arg( array(
        'page'             => 'site-settings',
        'vmb-sync-status'  => $result['code'],
        'vmb-sync-message' => rawurlencode( $result['message'] ),
    ), admin_url( 'admin.php' ) ) );
    exit;
}

function vmb_reviews_handle_duplicate_cleanup() {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( 'You are not allowed to clean up reviews.' );
    }

    check_admin_referer( 'vmb_cleanup_review_duplicates', 'vmb_reviews_cleanup_nonce' );

    $result = vmb_reviews_cleanup_duplicate_ids( false );

    wp_safe_redirect( add_query_arg( array(
        'page'             => 'site-settings',
        'vmb-sync-status'  => 'success',
        'vmb-sync-message' => rawurlencode( sprintf( 'Review cleanup complete. Added IDs: %d. Duplicates trashed: %d.', $result['assigned_ids'], $result['trashed_duplicates'] ) ),
    ), admin_url( 'admin.php' ) ) );
    exit;
}

function vmb_reviews_cleanup_duplicate_ids( $dry_run = true ) {
    $posts = get_posts( array(
        'post_type'      => 'vmb_reviews',
        'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
        'posts_per_page' => -1,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ) );

    $seen      = array();
    $assigned  = 0;
    $trashed   = 0;

    foreach ( $posts as $post ) {
        $unique_id = get_post_meta( $post->ID, 'vmb_review_id', true );

        if ( '' === $unique_id ) {
            $connected_property = get_post_meta( $post->ID, 'connected_property', true );
            $site_id            = get_post_meta( $post->ID, '_vmb_alchemer_site_id', true );
            $response_id        = get_post_meta( $post->ID, '_vmb_alchemer_response_id', true );

            if ( '' === $response_id ) {
                $response_id = 'manual-' . $post->ID;
            }

            $unique_id = vmb_reviews_build_unique_id( $connected_property ? $connected_property : get_bloginfo( 'name' ), $site_id, $response_id );

            if ( ! $dry_run ) {
                update_post_meta( $post->ID, 'vmb_review_id', $unique_id );
                update_post_meta( $post->ID, '_vmb_alchemer_unique_id', $unique_id );
            }
            $assigned++;
        }

        if ( isset( $seen[ $unique_id ] ) ) {
            if ( ! $dry_run ) {
                update_post_meta( $post->ID, '_vmb_review_duplicate_of', $seen[ $unique_id ] );
                wp_trash_post( $post->ID );
            }
            $trashed++;
            continue;
        }

        $seen[ $unique_id ] = $post->ID;
    }

    return array(
        'assigned_ids'        => $assigned,
        'trashed_duplicates'  => $trashed,
        'unique_review_count' => count( $seen ),
    );
}

function vmb_reviews_mark_manual_update( $post_id, $post, $update ) {
    if ( ! empty( $GLOBALS['vmb_reviews_syncing'] ) || ! $update || wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
        return;
    }

    if ( ! $post || 'vmb_reviews' !== $post->post_type ) {
        return;
    }

    update_post_meta( $post_id, 'review_modified', '1' );
    update_post_meta( $post_id, '_vmb_review_manually_edited', '1' );
    update_post_meta( $post_id, '_vmb_review_sync_status', 'manual' );
}

function vmb_reviews_pending_notice() {
    if ( ! function_exists( 'get_current_screen' ) || ! current_user_can( 'edit_theme_options' ) ) {
        return;
    }

    $screen = get_current_screen();
    if ( ! $screen || false === strpos( $screen->id, 'vmb_reviews' ) ) {
        return;
    }

    $pending = get_posts( array(
        'post_type'      => 'vmb_reviews',
        'post_status'    => 'draft',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_vmb_review_sync_status',
        'meta_value'     => 'pending',
    ) );

    if ( $pending ) {
        echo '<div class="notice notice-info"><p>There are Alchemer reviews imported as drafts and waiting for review.</p></div>';
    }
}

function vmb_reviews_add_admin_columns( $columns ) {
    $columns['vmb_review_sync_status'] = 'Sync Status';
    $columns['vmb_review_unique_id']   = 'Unique ID';
    return $columns;
}

function vmb_reviews_render_admin_column( $column, $post_id ) {
    if ( 'vmb_review_sync_status' === $column ) {
        $status = get_post_meta( $post_id, '_vmb_review_sync_status', true );
        echo esc_html( $status ? ucwords( str_replace( '_', ' ', $status ) ) : 'Not tracked' );
    }

    if ( 'vmb_review_unique_id' === $column ) {
        echo esc_html( get_post_meta( $post_id, 'vmb_review_id', true ) );
    }
}

function vmb_reviews_render_settings_controls() {
    if ( function_exists( 'post_type_exists' ) && ! post_type_exists( 'vmb_reviews' ) ) {
        echo '<p><em>Review sync is waiting for the vmb_reviews post type.</em></p>';
        return;
    }

    if ( isset( $_GET['vmb-sync-message'] ) ) {
        $class = isset( $_GET['vmb-sync-status'] ) && 'fail' === $_GET['vmb-sync-status'] ? 'notice notice-error' : 'notice notice-success';
        printf( '<div class="%s"><p>%s</p></div>', esc_attr( $class ), esc_html( rawurldecode( wp_unslash( $_GET['vmb-sync-message'] ) ) ) );
    }

    $settings = vmb_reviews_get_settings();
    ?>
    <h2>Alchemer Review Sync</h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="vmb_sync_alchemer_reviews">
        <?php wp_nonce_field( 'vmb_sync_alchemer_reviews', 'vmb_reviews_sync_nonce' ); ?>
        <table class="form-table" role="presentation"><tbody>
            <tr>
                <th scope="row"><label for="vmb_reviews_minimum_rating">Minimum Rating</label></th>
                <td><input type="number" min="1" max="5" id="vmb_reviews_minimum_rating" name="vmb_reviews_minimum_rating" value="<?php echo esc_attr( $settings['minimum_rating'] ); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="vmb_reviews_limit">Reviews to Pull</label></th>
                <td><input type="number" min="1" max="200" id="vmb_reviews_limit" name="vmb_reviews_limit" value="<?php echo esc_attr( $settings['reviews_to_pull'] ); ?>"></td>
            </tr>
            <?php if ( post_type_exists( 'resort' ) ) : ?>
                <tr>
                    <th scope="row"><label for="vmb_reviews_resort_id">Resort</label></th>
                    <td>
                        <select id="vmb_reviews_resort_id" name="vmb_reviews_resort_id">
                            <option value="0">All resorts</option>
                            <?php foreach ( get_posts( array( 'post_type' => 'resort', 'post_status' => 'publish', 'posts_per_page' => -1 ) ) as $resort ) : ?>
                                <option value="<?php echo esc_attr( $resort->ID ); ?>"><?php echo esc_html( get_the_title( $resort ) ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody></table>
        <?php submit_button( 'Sync Reviews Now', 'primary', 'submit', false ); ?>
    </form>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:16px;">
        <input type="hidden" name="action" value="vmb_cleanup_review_duplicates">
        <?php wp_nonce_field( 'vmb_cleanup_review_duplicates', 'vmb_reviews_cleanup_nonce' ); ?>
        <?php submit_button( 'Clean Up Duplicate Review IDs', 'secondary', 'submit', false ); ?>
    </form>
    <?php
}
