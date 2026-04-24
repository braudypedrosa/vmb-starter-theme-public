<?php
namespace VMB_Sites\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Mapbox_Maps extends Widget_Base {

    public function get_name() {
        return 'vmb_mapbox_maps';
    }

    public function get_title() {
        return __( 'Mapbox Maps', 'vmb-sites' );
    }

    public function get_icon() {
        return 'eicon-map-pin';
    }

    public function get_categories() {
        return [ 'vmb-widgets' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'vmb-sites' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'map_id',
            [
                'label' => __( 'Select Map', 'vmb-sites' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_mapbox_maps(),
            ]
        );

        $this->end_controls_section();
    }

    private function get_mapbox_maps() {
        $maps = get_posts([
            'post_type' => 'mapbox-map',
            'numberposts' => -1,
        ]);

        $options = [];
        foreach ( $maps as $map ) {
            $options[ $map->ID ] = $map->post_title;
        }

        return $options;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        if ( ! empty( $settings['map_id'] ) ) {
            echo do_shortcode('[mapbox-map id="' . $settings['map_id'] . '"]');
        }
    }

    protected function _content_template() {
        ?>
        <div class="mapbox-maps-widget">
            {{{ '[mapbox-map id="' + settings.map_id + '"]' }}}
        </div>
        <?php
    }
}