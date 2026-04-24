<?php
namespace VMB_Sites\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Gallery extends Widget_Base {

    public function get_name() {
        return 'vmb_gallery';
    }

    public function get_title() {
        return __( 'VMB Gallery', 'vmb-sites' );
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
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
            'gallery_id',
            [
                'label' => __( 'Gallery ID', 'vmb-sites' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_gallery_posts(),
                'label_block' => true,
                'placeholder' => __( 'Select Gallery', 'vmb-sites' ),
            ]
        );

        $this->end_controls_section();
    }

    private function get_gallery_posts() {
        $args = [
            'post_type' => 'vmb_gallery',
            'posts_per_page' => -1,
        ];

        $posts = get_posts( $args );
        $options = [];

        if ( ! empty( $posts ) ) {
            foreach ( $posts as $post ) {
                $options[ $post->ID ] = $post->post_title;
            }
        }

        return $options;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $gallery_id = $settings['gallery_id'];

        if ( ! empty( $gallery_id ) ) {
            echo do_shortcode('[vmb_gallery id="' . $gallery_id . '"]');
        } else {
            echo __( 'Please enter a valid Gallery ID.', 'vmb-sites' );
        }
    }

    protected function _content_template() {
        ?>
        <#
        var gallery_id = settings.gallery_id;
        if ( gallery_id ) {
            var shortcode = '[vmb_gallery id="' + gallery_id + '"]';
            view.addRenderAttribute( 'wrapper', 'class', 'gallery-widget' );
        #>
        <div {{{ view.getRenderAttributeString( 'wrapper' ) }}}>
            {{{ shortcode }}}
        </div>
        <#
        } else {
            #>
            <div class="gallery-widget">
                <?php echo __( 'Please enter a valid Gallery ID.', 'vmb-sites' ); ?>
            </div>
            <#
        }
        #>
        <?php
    }
}
