<?php
namespace VMB_Sites\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function register_vmb_widgets( $widgets_manager ) {
    require_once( __DIR__ . '/lib/booking-engine.php' );
    require_once( __DIR__ . '/lib/gallery.php' );
    require_once( __DIR__ . '/lib/rooms.php' );
    require_once( __DIR__ . '/lib/reviews.php' );
    require_once( __DIR__ . '/lib/specials.php' );
    require_once( __DIR__ . '/lib/discount-cards.php' );
    require_once( __DIR__ . '/lib/mapbox-maps.php' );

    $widgets_manager->register( new \VMB_Sites\Widgets\Booking_Engine() );
    $widgets_manager->register( new \VMB_Sites\Widgets\Gallery() );
    $widgets_manager->register( new \VMB_Sites\Widgets\Rooms() );
    $widgets_manager->register( new \VMB_Sites\Widgets\Reviews() );
    $widgets_manager->register( new \VMB_Sites\Widgets\Specials() );
    $widgets_manager->register( new \VMB_Sites\Widgets\Discount_Cards() );
    $widgets_manager->register( new \VMB_Sites\Widgets\Mapbox_Maps() );
}
add_action( 'elementor/widgets/register', '\VMB_Sites\Widgets\register_vmb_widgets' );

function add_vmb_widget_categories( $elements_manager ) {
    $elements_manager->add_category(
        'vmb-widgets',
        [
            'title' => __( 'VMB Widgets', 'vmb-sites' ),
            'icon' => 'fa fa-plug',
        ]
    );
}
add_action( 'elementor/elements/categories_registered', '\VMB_Sites\Widgets\add_vmb_widget_categories' );