<?php
if (!defined('ABSPATH')) exit;

class Card_ACF {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('acf/init', [$this, 'register_fields']);
        add_filter('acf/settings/show_admin', '__return_true');
    }

    public function register_fields() {
        acf_add_local_field_group([
            'key' => 'group_card_fields',
            'title' => 'Card Fields',
            'fields' => [
                [
                    'key' => 'field_card_subtitle',
                    'label' => 'Subtitle',
                    'name' => '_card_subtitle',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_card_image',
                    'label' => 'Image',
                    'name' => '_card_image',
                    'type' => 'image',
                    'return_format' => 'array',
                ]
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'card',
                    ]
                ]
            ]
        ]);
    }
} 