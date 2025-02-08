<?php
if (!defined('ABSPATH')) exit;

class Card_Scripts {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_scripts']);
    }

    public function enqueue_editor_scripts() {
        wp_enqueue_script(
            'card-admin',
            CARD_PLUGIN_URL . 'assets/js/card-admin.js',
            ['jquery', 'elementor-editor'],
            '1.0.0',
            true
        );

        wp_localize_script('card-admin', 'cardAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('card_nonce')
        ]);
    }
} 