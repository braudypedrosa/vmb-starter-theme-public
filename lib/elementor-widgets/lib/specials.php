<?php
namespace VMB_Sites\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Specials extends Widget_Base {

    public function get_name() {
        return 'vmb_specials';
    }

    public function get_title() {
        return __( 'VMB Specials', 'vmb-sites' );
    }

    public function get_icon() {
        return 'eicon-post-list';
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

        $this->end_controls_section();
    }

    protected function render() {
        echo do_shortcode('[vmb_specials]');
    }

    protected function _content_template() {
        ?>
        <div class="specials-widget">
            {{{ '[vmb_specials]' }}}
        </div>
        <?php
    }
}