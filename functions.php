<?php
if ( ! defined( 'ABSPATH' ) ) {
exit; // Exit if accessed directly.
}
// Required files
require_once get_stylesheet_directory() . '/inc/theme-updater.php';
require_once get_stylesheet_directory() . '/inc/plugins.php';
require get_stylesheet_directory() . '/lib/native-fields.php';
require get_stylesheet_directory() . '/lib/native-content.php';
require get_stylesheet_directory() . '/lib/native-admin.php';
require get_stylesheet_directory() . '/lib/helper.php';
require get_stylesheet_directory() . '/lib/util-shortcodes.php';
require get_stylesheet_directory() . '/lib/shortcodes.php';
// Load Ajax Functions
require get_stylesheet_directory() . '/lib/ajax-functions/vmb-widgets-ajax.php';
// Elementor Widgets
if ( class_exists( 'Elementor\Plugin' ) ) {
require get_stylesheet_directory() . '/lib/elementor-widgets/elementor-widgets.php';
}
// Load all modules
foreach (glob(get_stylesheet_directory() . '/lib/modules/*.php') as $file) {
require $file;
}
global $version;
$version = 'version_' . uniqid();
// Actions
add_action( 'init', 'vmb_enqueue' );
add_action( 'wp_enqueue_scripts', 'vmb_enqueue_public', 20 );
add_action( 'admin_enqueue_scripts', 'vmb_enqueue_admin', 20 );
// Add this line to trigger the wpdi_plugins_loaded action
do_action( 'wpdi_plugins_loaded' );
/**
* Load child theme scripts & styles.
*
* @return void
*/
function vmb_enqueue_public() {
global $version;
// Enqueue third party libraries
wp_enqueue_script('slick_js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
wp_enqueue_style('slick_css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css', array(), '1.8.1');
wp_enqueue_style('slick_theme_css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css', array(), '1.8.1');
wp_enqueue_style('font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css', array(), '5.15.3');
wp_enqueue_script('mixitup_js', 'https://cdn.jsdelivr.net/npm/mixitup@3/dist/mixitup.min.js', array('jquery'), '3.3.1', true);
wp_enqueue_style('vmb_public_styles', get_stylesheet_directory_uri() . '/dist/css/bundled-public.css', array(), $version);
wp_enqueue_script('vmb_public_scripts', get_stylesheet_directory_uri() . '/dist/js/bundled-public.js', array('jquery'), $version, true);
$mapbox_settings = json_decode(get_option('bub_mapbox_plugin_settings'), true);
// Localize script for Mapbox
wp_localize_script('vmb_public_scripts', 'vmb_public_scripts_localize', array(
'mapbox_token' => $mapbox_settings['mapbox_token'],
'mapbox_style' => $mapbox_settings['mapbox_style'],
'mapbox_marker_color' => $mapbox_settings['mapbox_marker_color'],
));
}
function vmb_enqueue_admin() {
global $version;
// Enqueue third party libraries
wp_enqueue_script( 'datatables_js', 'https://cdn.datatables.net/2.1.3/js/dataTables.min.js', array( 'jquery' ), $version, false );
wp_enqueue_script( 'sweetalert_js', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', null, $version, false );
wp_enqueue_style('vmb_admin_styles', get_stylesheet_directory_uri() . '/dist/css/bundled-admin.css', array(), $version);
wp_enqueue_script('vmb_admin_scripts', get_stylesheet_directory_uri() . '/dist/js/bundled-admin.js', array('jquery'), $version, true);
$cached_specials = get_option('vmb_api_cached_specials', true);
$cached_specials_category = get_option('vmb_specials_category', true);
wp_localize_script( 'vmb_admin_scripts', 'vmb_ajax',  
array(
'cached_specials' => $cached_specials,
'cached_special_categories' => $cached_specials_category,
'ajax_url' => admin_url('admin-ajax.php'),
'nonce' => wp_create_nonce('specials_nonce')
)
);
}
function vmb_enqueue_general() {
global $version;
wp_enqueue_style('vmb_general_styles', get_stylesheet_directory_uri() . '/dist/css/bundled-general.css', array(), $version);
wp_enqueue_script('vmb_general_scripts', get_stylesheet_directory_uri() . '/dist/js/bundled-general.js', array('jquery'), $version, true);
}
function vmb_enqueue() {
if ( is_admin() ) {
add_action( 'admin_enqueue_scripts', 'vmb_enqueue_general', 20 );
} else {
add_action( 'wp_enqueue_scripts', 'vmb_enqueue_general', 20 );
}
}
// add_action('admin_init', 'trigger_plugin_update_check');
// function trigger_plugin_update_check() {
//     if (is_admin() && current_user_can('update_plugins')) {
//         wp_update_themes(); // Force WordPress to check for theme updates
//     }
// }
add_action('wp_footer', function() {
global $post;
if (is_singular('vmb_room') && vmb_get_field('custom_external_url', $post->ID)) {
echo '<script>jQuery("body").addClass("external-room");</script>';
}
});
add_action('admin_head', function () {
echo '<style>
.notice:has(p > .bsf-core-license-form-btn) { display: none; }
</style>';
});
add_action('wp_head', function () {
$show_banner = vmb_get_field('show_announcement_banner', 'option');
if (!$show_banner) {
echo '<style>
.vmb-announcement-banner { display: none!important; }
</style>';
}
$current_post_id = get_the_ID();
$show_booking_widget = vmb_get_field('show_booking_widget', $current_post_id);
if (!$show_booking_widget) {
echo '<style>
.booking-engine-form { display: none!important; }
</style>';
}
$show_availability_calendar = vmb_get_field('show_availability_calendar', $current_post_id);
if (!$show_availability_calendar) {
echo '<style>.availability-calendar { display: none!important; } </style>';
}
$show_countdown_timer = vmb_get_field('show_countdown_timer', $current_post_id);
if(!$show_countdown_timer) {
echo '<style>body:not(.elementor-editor-active) .countdown-timer { display: none!important; } </style>';
}
$dynamic_phone_numbers = vmb_get_field('dynamic_phone_numbers', 'option');
if(!empty($dynamic_phone_numbers)) {
// Get textarea from ACF options and convert to array
$phone_list_raw = $dynamic_phone_numbers;
$phone_numbers = array_filter(array_map('trim', explode("\n", $phone_list_raw)));
// Pass to JS
echo "<script>
window.dynamicPhoneNumbers = " . json_encode(array_values($phone_numbers)) . ";
</script>";
}
});
add_action( 'gform_after_submission_3', 'set_webcam_allowed_cookie', 10, 2 );
function set_webcam_allowed_cookie( $entry, $form ) {
// Set cookie to expire in 1 day
$cookie_name  = 'webcam_allowed';
$cookie_value = 'true';
$expiry_time  = time() + DAY_IN_SECONDS;
// Set cookie
setcookie( $cookie_name, $cookie_value, $expiry_time, COOKIEPATH, COOKIE_DOMAIN );
// For multisite support
if ( is_ssl() ) {
setcookie( $cookie_name, $cookie_value, $expiry_time, COOKIEPATH, COOKIE_DOMAIN, true, true );
}
}
add_action( 'template_redirect', 'vmb_check_webcam_cookie' );
function vmb_check_webcam_cookie() {
// Only apply logic on the /webcam page
if ( is_page( 'webcam' ) ) {
if ( ! isset( $_COOKIE['webcam_allowed'] ) || $_COOKIE['webcam_allowed'] !== 'true' ) {
wp_redirect( home_url( '/webcam-entry' ) );
exit;
}
}
}
// Start output buffering immediately — right after ABSPATH check
ob_start(function ($buffer) {
if (strpos($buffer, '<?xml') !== false) {
// Remove BOM and whitespace before XML declaration
return preg_replace('/^\xEF\xBB\xBF|\s+(?=<\?xml)/', '', $buffer);
}
return $buffer;
});
add_filter('gform_confirmation_3', 'redirect_to_webcam_after_submission', 10, 4);
function redirect_to_webcam_after_submission($confirmation, $form, $entry, $ajax) {
return [
'redirect' => home_url('/webcam/')
];
}
function enqueue_flatpickr_for_gf() {
wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
wp_enqueue_style('flatpickr-style', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_flatpickr_for_gf');
function init_flatpickr_on_gf_fields() {
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
const dateInputs = document.querySelectorAll('input.datepicker');
dateInputs.forEach(function(input) {
flatpickr(input, {
dateFormat: "m/d/Y"
});
});
});
</script>
<?php
}
add_action('wp_footer', 'init_flatpickr_on_gf_fields', 100);
add_filter( 'rank_math/sitemap/enable_caching', '__return_false');
