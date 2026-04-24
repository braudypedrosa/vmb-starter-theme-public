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
            'vmb-fields',
            array(
                'title' => esc_html__( 'VMB Fields', 'vmb-starter-theme' ),
            )
        );
    }

    if ( ! class_exists( 'VMB_Native_Text_Field_Dynamic_Tag' ) ) {
        class VMB_Native_Text_Field_Dynamic_Tag extends \Elementor\Core\DynamicTags\Tag {
            public function get_name() {
                return 'vmb-field-text';
            }

            public function get_title() {
                return esc_html__( 'VMB Field', 'vmb-starter-theme' );
            }

            public function get_group() {
                return 'vmb-fields';
            }

            public function get_categories() {
                return array( 'text', 'post_meta' );
            }

            public function render() {
                $key = $this->get_settings( 'key' );
                if ( ! $key ) {
                    return;
                }

                $value = vmb_get_elementor_text_value( $key );
                if ( '' === $value || null === $value || false === $value ) {
                    return;
                }

                echo wp_kses_post( $value );
            }

            protected function register_controls() {
                vmb_register_elementor_key_control( $this );
            }
        }
    }

    if ( class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) && ! class_exists( 'VMB_Native_URL_Field_Dynamic_Tag' ) ) {
        class VMB_Native_URL_Field_Dynamic_Tag extends \Elementor\Core\DynamicTags\Data_Tag {
            public function get_name() {
                return 'vmb-field-url';
            }

            public function get_title() {
                return esc_html__( 'VMB URL Field', 'vmb-starter-theme' );
            }

            public function get_group() {
                return 'vmb-fields';
            }

            public function get_categories() {
                return array( 'url', 'svg' );
            }

            public function get_panel_template_setting_key() {
                return 'key';
            }

            public function get_value( array $options = array() ) {
                return vmb_get_elementor_url_value( $this->get_settings( 'key' ), $this->get_settings( 'fallback' ) );
            }

            protected function register_controls() {
                vmb_register_elementor_key_control( $this );

                if ( ! class_exists( '\Elementor\Controls_Manager' ) ) {
                    return;
                }

                $this->add_control(
                    'fallback',
                    array(
                        'label' => esc_html__( 'Fallback', 'vmb-starter-theme' ),
                        'type'  => \Elementor\Controls_Manager::TEXT,
                    )
                );
            }
        }
    }

    if ( class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) && ! class_exists( 'VMB_Native_Image_Field_Dynamic_Tag' ) ) {
        class VMB_Native_Image_Field_Dynamic_Tag extends \Elementor\Core\DynamicTags\Data_Tag {
            public function get_name() {
                return 'vmb-field-image';
            }

            public function get_title() {
                return esc_html__( 'VMB Image Field', 'vmb-starter-theme' );
            }

            public function get_group() {
                return 'vmb-fields';
            }

            public function get_categories() {
                return array( 'image' );
            }

            public function get_panel_template_setting_key() {
                return 'key';
            }

            public function get_value( array $options = array() ) {
                return vmb_get_elementor_image_value( $this->get_settings( 'key' ), $this->get_settings( 'fallback' ) );
            }

            protected function register_controls() {
                vmb_register_elementor_key_control( $this );

                if ( ! class_exists( '\Elementor\Controls_Manager' ) ) {
                    return;
                }

                $this->add_control(
                    'fallback',
                    array(
                        'label' => esc_html__( 'Fallback', 'vmb-starter-theme' ),
                        'type'  => \Elementor\Controls_Manager::MEDIA,
                    )
                );
            }
        }
    }

    if ( class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) && ! class_exists( 'VMB_Native_Gallery_Field_Dynamic_Tag' ) ) {
        class VMB_Native_Gallery_Field_Dynamic_Tag extends \Elementor\Core\DynamicTags\Data_Tag {
            public function get_name() {
                return 'vmb-field-gallery';
            }

            public function get_title() {
                return esc_html__( 'VMB Gallery Field', 'vmb-starter-theme' );
            }

            public function get_group() {
                return 'vmb-fields';
            }

            public function get_categories() {
                return array( 'gallery' );
            }

            public function get_panel_template_setting_key() {
                return 'key';
            }

            public function get_value( array $options = array() ) {
                return vmb_get_elementor_gallery_value( $this->get_settings( 'key' ) );
            }

            protected function register_controls() {
                vmb_register_elementor_key_control( $this );
            }
        }
    }

    if ( ! class_exists( 'VMB_Native_Term_Field_Dynamic_Tag' ) ) {
        class VMB_Native_Term_Field_Dynamic_Tag extends \Elementor\Core\DynamicTags\Tag {
            public function get_name() {
                return 'vmb-term-field';
            }

            public function get_title() {
                return esc_html__( 'VMB Term Field', 'vmb-starter-theme' );
            }

            public function get_group() {
                return 'vmb-fields';
            }

            public function get_categories() {
                return array( 'text', 'number', 'url', 'post_meta', 'color' );
            }

            public function render() {
                echo wp_kses_post(
                    vmb_get_elementor_term_field_value(
                        $this->get_settings( 'taxonomy' ),
                        $this->get_settings( 'term_field' ),
                        $this->get_settings( 'meta_field' )
                    )
                );
            }

            protected function register_controls() {
                if ( ! class_exists( '\Elementor\Controls_Manager' ) ) {
                    return;
                }

                $this->add_control(
                    'taxonomy',
                    array(
                        'label' => esc_html__( 'Taxonomy', 'vmb-starter-theme' ),
                        'type'  => \Elementor\Controls_Manager::TEXT,
                    )
                );

                $this->add_control(
                    'term_field',
                    array(
                        'label' => esc_html__( 'Field', 'vmb-starter-theme' ),
                        'type'  => \Elementor\Controls_Manager::TEXT,
                    )
                );

                $this->add_control(
                    'meta_field',
                    array(
                        'label' => esc_html__( 'Meta Field', 'vmb-starter-theme' ),
                        'type'  => \Elementor\Controls_Manager::TEXT,
                    )
                );
            }
        }
    }

    $dynamic_tags->register( new VMB_Native_Text_Field_Dynamic_Tag() );

    if ( class_exists( 'VMB_Native_URL_Field_Dynamic_Tag' ) ) {
        $dynamic_tags->register( new VMB_Native_URL_Field_Dynamic_Tag() );
    }

    if ( class_exists( 'VMB_Native_Image_Field_Dynamic_Tag' ) ) {
        $dynamic_tags->register( new VMB_Native_Image_Field_Dynamic_Tag() );
    }

    if ( class_exists( 'VMB_Native_Gallery_Field_Dynamic_Tag' ) ) {
        $dynamic_tags->register( new VMB_Native_Gallery_Field_Dynamic_Tag() );
    }

    $dynamic_tags->register( new VMB_Native_Term_Field_Dynamic_Tag() );
}

function vmb_register_elementor_key_control( $tag ) {
    if ( ! class_exists( '\Elementor\Controls_Manager' ) ) {
        return;
    }

    $tag->add_control(
        'key',
        array(
            'label' => esc_html__( 'Key', 'vmb-starter-theme' ),
            'type'  => \Elementor\Controls_Manager::TEXT,
        )
    );
}

function vmb_get_elementor_text_value( $key ) {
    return vmb_normalize_elementor_text_value( vmb_get_elementor_field_value( $key ) );
}

function vmb_get_elementor_url_value( $key, $fallback = '' ) {
    $field = vmb_parse_elementor_field_key( $key );
    $value = vmb_get_elementor_field_value( $key );

    if ( is_array( $value ) && isset( $value['url'] ) ) {
        $value = $value['url'];
    }

    $value = vmb_normalize_elementor_text_value( $value );
    if ( '' === $value && $fallback ) {
        $value = $fallback;
    }

    if ( '' === $value ) {
        return '';
    }

    if ( in_array( $field['field'], array( 'phone_number', 'groups_phone_number' ), true ) ) {
        return vmb_format_elementor_tel_url( $value );
    }

    if ( false !== strpos( $field['field'], 'email' ) && is_email( $value ) && 0 !== strpos( $value, 'mailto:' ) ) {
        return 'mailto:' . $value;
    }

    return $value;
}

function vmb_format_elementor_tel_url( $value ) {
    $value = preg_replace( '/^tel:/', '', (string) $value );
    $value = preg_replace( '/(?!^\+)[^\d]/', '', $value );

    return $value ? 'tel:' . $value : '';
}

function vmb_get_elementor_image_value( $key, $fallback = array() ) {
    $value = vmb_get_elementor_field_value( $key );
    $image = vmb_normalize_elementor_image_value( $value );

    if ( empty( $image['url'] ) && is_array( $fallback ) ) {
        $image = vmb_normalize_elementor_image_value( $fallback );
    }

    return $image;
}

function vmb_get_elementor_gallery_value( $key ) {
    $value = vmb_get_elementor_field_value( $key );

    if ( function_exists( 'vmb_format_gallery_value' ) ) {
        return vmb_format_gallery_value( $value );
    }

    return is_array( $value ) ? $value : array();
}

function vmb_get_elementor_field_value( $key ) {
    $field = vmb_parse_elementor_field_key( $key );

    if ( 'option' === $field['scope'] ) {
        return vmb_get_field( $field['field'], 'option' );
    }

    $term = vmb_get_elementor_current_term();
    if ( $term ) {
        $term_value = get_term_meta( $term->term_id, $field['field'], true );
        if ( ! vmb_elementor_value_is_empty( $term_value ) ) {
            if ( in_array( $field['field'], array( 'featured_image', 'main_image', 'room_layout', 'preview_image' ), true ) ) {
                return vmb_format_image_value( $term_value );
            }

            return $term_value;
        }
    }

    $post_id = get_the_ID();
    if ( $post_id ) {
        $post_value = vmb_get_field( $field['field'], $post_id );
        if ( ! vmb_elementor_value_is_empty( $post_value ) ) {
            return $post_value;
        }
    }

    return vmb_get_field( $field['field'], 'option' );
}

function vmb_parse_elementor_field_key( $key ) {
    $key = (string) $key;

    if ( 0 === strpos( $key, 'options:' ) ) {
        return array(
            'scope' => 'option',
            'field' => substr( $key, 8 ),
        );
    }

    if ( false !== strpos( $key, ':' ) ) {
        $parts = explode( ':', $key, 2 );
        $key   = $parts[1];
    }

    return array(
        'scope' => 'auto',
        'field' => $key,
    );
}

function vmb_normalize_elementor_text_value( $value ) {
    if ( is_array( $value ) ) {
        if ( isset( $value['address'] ) ) {
            return $value['address'];
        }

        $value = implode( ', ', array_filter( array_map( 'strval', $value ) ) );
    }

    return is_scalar( $value ) ? (string) $value : '';
}

function vmb_normalize_elementor_image_value( $value ) {
    $image = array(
        'id'  => null,
        'url' => '',
    );

    if ( is_array( $value ) && ! empty( $value['url'] ) ) {
        $image['id']  = isset( $value['id'] ) ? $value['id'] : ( isset( $value['ID'] ) ? $value['ID'] : null );
        $image['url'] = $value['url'];
        return $image;
    }

    if ( is_numeric( $value ) ) {
        $attachment = vmb_format_image_value( $value );
        if ( $attachment ) {
            $image['id']  = $attachment['id'];
            $image['url'] = $attachment['url'];
        }

        return $image;
    }

    if ( is_string( $value ) && preg_match( '#^https?://#', $value ) ) {
        $image['url'] = $value;
    }

    return $image;
}

function vmb_get_elementor_term_field_value( $taxonomy, $field, $meta_field = '' ) {
    $term = vmb_get_elementor_current_term( $taxonomy );

    if ( ! $term && get_the_ID() && $taxonomy ) {
        $terms = wp_get_post_terms( get_the_ID(), $taxonomy );
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            $term = $terms[0];
        }
    }

    if ( ! $term ) {
        return '';
    }

    switch ( $field ) {
        case 'meta_field':
            $value = $meta_field ? get_term_meta( $term->term_id, $meta_field, true ) : '';
            return is_array( $value ) ? implode( ', ', $value ) : $value;

        case 'term_url':
            $url = get_term_link( $term->term_id, $taxonomy );
            return is_wp_error( $url ) ? '' : $url;

        default:
            return isset( $term->$field ) ? $term->$field : '';
    }
}

function vmb_get_elementor_current_term( $taxonomy = '' ) {
    $object = get_queried_object();
    if ( $object instanceof WP_Term && ( ! $taxonomy || $taxonomy === $object->taxonomy ) ) {
        return $object;
    }

    return null;
}

function vmb_elementor_value_is_empty( $value ) {
    return '' === $value || null === $value || false === $value || ( is_array( $value ) && empty( $value ) );
}
