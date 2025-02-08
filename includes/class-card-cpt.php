<?php
if (!defined('ABSPATH')) exit;

class Card_CPT {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Add admin scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function enqueue_admin_scripts($hook) {
        global $post;
        
        // Hanya load di halaman edit card
        if ($hook == 'post-new.php' || $hook == 'post.php') {
            if ('card' === $post->post_type) {
                wp_enqueue_media();
                wp_enqueue_script('card-admin', CARD_PLUGIN_URL . 'assets/js/card-admin.js', ['jquery'], '1.0.0', true);
            }
        }
    }
} 