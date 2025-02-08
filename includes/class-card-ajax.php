<?php
if (!defined('ABSPATH')) exit;

class Card_Ajax {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('elementor/ajax/register_actions', [$this, 'register_ajax_actions']);
    }

    public function register_ajax_actions($ajax_manager) {
        $ajax_manager->register_ajax_action('update_card_widget', [$this, 'update_card_widget']);
    }

    public function update_card_widget($request) {
        $selected_card = $request['selected_card'];
        
        // Get card post
        $card_post = get_post($selected_card);
        if (!$card_post) {
            return ['html' => '<div class="card-error">Card not found</div>'];
        }

        // Get card data from ACF
        $settings = [
            'card_title' => $card_post->post_title,
            'card_description' => $card_post->post_content,
            'card_subtitle' => get_field('_card_subtitle', $card_post->ID)
        ];

        // Get image from ACF
        $image = get_field('_card_image', $card_post->ID);
        if ($image && is_array($image)) {
            $settings['card_image'] = [
                'url' => $image['url'],
                'id' => $image['ID'],
                'alt' => $image['alt']
            ];
        }

        // Get meta data
        $settings['author_name'] = get_the_author_meta('display_name', $card_post->post_author);
        $settings['post_date'] = get_the_date(get_option('date_format'), $card_post);

        ob_start();
        include CARD_PLUGIN_PATH . 'templates/layouts/default.php';
        $html = ob_get_clean();

        return ['html' => $html];
    }
} 