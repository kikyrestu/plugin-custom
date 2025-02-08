<?php
if (!defined('ABSPATH')) exit;

class Card_Elementor {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Penting: Tunggu sampai Elementor benar-benar siap
        add_action('elementor/elements/categories_registered', [$this, 'add_elementor_widget_category']);
        add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets']); // Untuk Elementor versi lama
        add_action('elementor/widgets/register', [$this, 'register_widgets']); // Untuk Elementor versi baru
    }

    public function add_elementor_widget_category($elements_manager) {
        $elements_manager->add_category(
            'card-elements',
            [
                'title' => __('Card Elements', 'card-plugin'),
                'icon' => 'fa fa-plug'
            ]
        );
    }

    public function register_widgets($widgets_manager) {
        // Include widget file
        require_once CARD_PLUGIN_PATH . 'widgets/class-card-widget.php';
        
        // Register widget dengan cara yang berbeda berdasarkan versi Elementor
        if (method_exists($widgets_manager, 'register')) {
            // Elementor >= 3.5.0
            $widgets_manager->register(new \Card_Widget());
        } else {
            // Elementor < 3.5.0
            $widgets_manager->register_widget_type(new \Card_Widget());
        }
    }
} 