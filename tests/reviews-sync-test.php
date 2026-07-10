<?php
define( 'ABSPATH', __DIR__ . '/' );

function sanitize_title( $value ) {
    $value = strtolower( trim( (string) $value ) );
    $value = preg_replace( '/[^a-z0-9]+/', '-', $value );
    return trim( $value, '-' );
}

function sanitize_text_field( $value ) {
    return trim( (string) $value );
}

function wp_strip_all_tags( $value ) {
    return strip_tags( (string) $value );
}

function absint( $value ) {
    return abs( (int) $value );
}

$GLOBALS['vmb_test_meta'] = array();

function get_post_meta( $post_id, $key, $single = true ) {
    return isset( $GLOBALS['vmb_test_meta'][ $post_id ][ $key ] ) ? $GLOBALS['vmb_test_meta'][ $post_id ][ $key ] : '';
}

require __DIR__ . '/../lib/reviews-sync.php';

function vmb_test_assert_same( $expected, $actual, $message ) {
    if ( $expected !== $actual ) {
        fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
        exit( 1 );
    }
}

$unique_id = vmb_reviews_build_unique_id( 'Ocean Reef Resort', '12345', '987654' );
vmb_test_assert_same( 'ocean-reef-resort-987654', $unique_id, 'Unique IDs should preserve the legacy VMB property plus response ID format.' );

$response = array(
    'id'            => '987654',
    'date_submitted'=> '2026-07-06 12:00:00',
    'url_variables' => array(
        'firstname' => array( 'value' => 'kimberly' ),
        'lastname'  => array( 'value' => 'loudermilk' ),
    ),
    'survey_data'   => array(
        '45' => array(
            'answer'   => '5',
            'comments' => '<p>Great stay.</p>',
        ),
    ),
);

$prepared = vmb_reviews_prepare_alchemer_review( $response, '45', '12345', 'Ocean Reef Resort', 5 );
vmb_test_assert_same( 'ocean-reef-resort-987654', $prepared['unique_id'], 'Prepared reviews should expose the canonical unique ID.' );
vmb_test_assert_same( 'Kimberly Loudermilk', $prepared['title'], 'Prepared reviews should normalize the reviewer title.' );
vmb_test_assert_same( 'Great stay.', $prepared['comment'], 'Prepared reviews should strip markup from comments.' );
vmb_test_assert_same( 5, $prepared['rating'], 'Prepared reviews should normalize rating to an integer.' );

$too_low = vmb_reviews_prepare_alchemer_review( $response, '45', '12345', 'Ocean Reef Resort', 6 );
vmb_test_assert_same( null, $too_low, 'Reviews below the configured minimum rating should be ignored.' );

$GLOBALS['vmb_test_meta'][10] = array(
    'review_modified' => '1',
    'include_in_sync' => '',
);
vmb_test_assert_same( false, vmb_reviews_can_overwrite_post( 10 ), 'Manually modified reviews should be protected by default.' );

$GLOBALS['vmb_test_meta'][10]['include_in_sync'] = '1';
vmb_test_assert_same( true, vmb_reviews_can_overwrite_post( 10 ), 'Modified reviews can be overwritten only when include_in_sync is enabled.' );

$GLOBALS['vmb_test_meta'][11] = array();
vmb_test_assert_same( true, vmb_reviews_can_overwrite_post( 11 ), 'Untouched reviews can be refreshed by sync.' );

echo "reviews-sync tests passed\n";
