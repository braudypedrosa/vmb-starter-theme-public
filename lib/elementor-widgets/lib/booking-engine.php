<?php
namespace VMB_Sites\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Booking_Engine extends Widget_Base {

    public function get_name() {
        return 'booking_engine';
    }

    public function get_title() {
        return __( 'Booking Engine', 'vmb-sites' );
    }

    public function get_icon() {
        return 'eicon-calendar';
    }

    public function get_categories() {
        return [ 'vmb-widgets' ];
    }

    protected function render() {
        echo do_shortcode('[booking_engine]');
    }

    protected function _content_template() {
        ?>
        <#
        view.addRenderAttribute( 'wrapper', 'class', 'booking-engine' );
        #>
        <div {{{ view.getRenderAttributeString( 'wrapper' ) }}}>
            <?php echo do_shortcode('[booking_engine]'); ?>
        </div>
        <?php
    }
}