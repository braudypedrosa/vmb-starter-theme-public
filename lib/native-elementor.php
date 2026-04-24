<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'elementor/dynamic_tags/register', 'vmb_register_native_elementor_dynamic_tags', 20 );

function vmb_register_native_elementor_dynamic_tags( $dynamic_tags ) {
    if ( ! class_exists( '\Elementor\Core\DynamicTags\Tag' ) ) {
        return;
    }

    if ( method_exists( $dynamic_tags, 'register_group' ) ) {
        $dynamic_tags->register_group(
            'acf',
            array(
                'title' => esc_html__( 'ACF', 'vmb-starter-theme' ),
            )
        );
    }

    if ( ! class_exists( 'VMB_Native_ACF_Text_Dynamic_Tag' ) ) {
        class VMB_Native_ACF_Text_Dynamic_Tag extends \Elementor\Core\DynamicTags\Tag {
            public function get_name() {
                return 'acf-text';
            }

            public function get_title() {
                return esc_html__( 'ACF Field', 'vmb-starter-theme' );
            }

            public function get_group() {
                return 'acf';
            }

            public function get_categories() {
                return array( 'text', 'post_meta' );
            }

            public function render() {
                $key = $this->get_settings( 'key' );
                if ( ! $key ) {
                    return;
                }

                $value = vmb_get_elementor_acf_text_value( $key );
                if ( '' === $value || null === $value || false === $value ) {
                    return;
                }

                echo wp_kses_post( $value );
            }

            protected function register_controls() {
                if ( ! class_exists( '\Elementor\Controls_Manager' ) ) {
                    return;
                }

                $this->add_control(
                    'key',
                    array(
                        'label' => esc_html__( 'Key', 'vmb-starter-theme' ),
                        'type'  => \Elementor\Controls_Manager::TEXT,
                    )
                );
            }
        }
    }

    $dynamic_tags->register( new VMB_Native_ACF_Text_Dynamic_Tag() );
}

function vmb_get_elementor_acf_text_value( $key ) {
    $key = (string) $key;

    if ( 0 === strpos( $key, 'options:' ) ) {
        return vmb_normalize_elementor_acf_text_value( vmb_get_field( substr( $key, 8 ), 'option' ) );
    }

    if ( false !== strpos( $key, ':' ) ) {
        $parts = explode( ':', $key, 2 );
        $key   = $parts[1];
    }

    return vmb_normalize_elementor_acf_text_value( vmb_get_field( $key, get_the_ID() ) );
}

function vmb_normalize_elementor_acf_text_value( $value ) {
    if ( is_array( $value ) ) {
        if ( isset( $value['address'] ) ) {
            return $value['address'];
        }

        $value = implode( ', ', array_filter( array_map( 'strval', $value ) ) );
    }

    return is_scalar( $value ) ? (string) $value : '';
}
