<?php
/**
 * Plugin Name: Card Plugin
 * Description: Custom card widget for Elementor
 * Version: 1.0.0
 * Author: Your Name
 */

if (!defined('WPINC')) {
    die;
}

if (!defined('ABSPATH')) exit;

// Define constants
define('CARD_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CARD_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load dependencies
require_once CARD_PLUGIN_PATH . 'includes/class-card-cpt.php';
require_once CARD_PLUGIN_PATH . 'includes/class-card-acf.php';
require_once CARD_PLUGIN_PATH . 'includes/class-card-ajax.php';

// Register Custom Post Type
function init_card_plugin() {
    error_log('=== REGISTERING CARD POST TYPE ===');
    
    $labels = [
        'name'               => 'Cards',
        'singular_name'      => 'Card',
        'menu_name'          => 'Cards',
        'add_new'           => 'Add New Card',
        'add_new_item'      => 'Add New Card',
        'edit_item'         => 'Edit Card',
        'new_item'          => 'New Card',
        'view_item'         => 'View Card',
        'search_items'      => 'Search Cards',
        'not_found'         => 'No cards found',
        'not_found_in_trash'=> 'No cards found in Trash'
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'card'],
        'capability_type'   => 'post',
        'has_archive'       => false,
        'hierarchical'      => false,
        'menu_position'     => 5,
        'menu_icon'         => 'dashicons-grid-view',
        'supports'          => ['title', 'editor', 'thumbnail']
    ];

    register_post_type('card', $args);
    error_log('Post type registered: ' . (post_type_exists('card') ? 'YES' : 'NO'));

    // Initialize CPT class
    Card_CPT::get_instance();
    error_log('=== END REGISTERING CARD POST TYPE ===');
}

// Register Elementor widget
function register_card_widget($widgets_manager) {
    require_once(CARD_PLUGIN_PATH . 'widgets/class-card-widget.php');
    $widgets_manager->register(new \Card_Plugin\Widgets\Card_Widget());
}

// Initialize Elementor functionality
function init_elementor_card_widget() {
    // Pastikan Elementor sudah terinstall dan aktif
    if (!did_action('elementor/loaded')) {
        return;
    }

    // Register widget
    add_action('elementor/widgets/register', 'register_card_widget');
}

// Activation hook
register_activation_hook(__FILE__, function() {
    init_card_plugin();
    flush_rewrite_rules();
});

// Deactivation hook
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

// Check required plugins
function check_required_plugins() {
    if (!class_exists('\\Elementor\\Plugin')) {
        add_action('admin_notices', function() {
            $message = sprintf(
                esc_html__('Card Plugin requires Elementor plugin to be installed and activated. %s', 'card-plugin'),
                '<a href="' . esc_url(admin_url('plugin-install.php?s=Elementor&tab=search&type=term')) . '">Install Elementor</a>'
            );
            printf('<div class="notice notice-error"><p>%s</p></div>', $message);
        });
        return false;
    }

    if (!class_exists('ACF')) {
        add_action('admin_notices', function() {
            $message = sprintf(
                esc_html__('Card Plugin requires Advanced Custom Fields plugin to be installed and activated. %s', 'card-plugin'),
                '<a href="' . esc_url(admin_url('plugin-install.php?s=Advanced+Custom+Fields&tab=search&type=term')) . '">Install ACF</a>'
            );
            printf('<div class="notice notice-error"><p>%s</p></div>', $message);
        });
        return false;
    }
    
    return true;
}

// Initialize everything
add_action('plugins_loaded', function() {
    error_log('=== CARD PLUGIN INITIALIZATION ===');
    
    if (check_required_plugins()) {
        error_log('Required plugins OK');
        
        // Initialize post type
        add_action('init', function() {
            init_card_plugin();
            error_log('Card post type initialized');
        });
        
        // Initialize Elementor widget
        if (did_action('elementor/loaded')) {
            error_log('Elementor loaded');
            add_action('elementor/widgets/register', 'register_card_widget');
        } else {
            error_log('Elementor not loaded!');
        }
        
        // Initialize ACF fields
        if (class_exists('ACF')) {
            error_log('ACF loaded');
            Card_ACF::get_instance();
        } else {
            error_log('ACF not loaded!');
        }
    } else {
        error_log('Required plugins missing!');
    }
    
    error_log('=== END CARD PLUGIN INITIALIZATION ===');
});

// Tambahkan AJAX handler
add_action('wp_ajax_delete_card', 'handle_delete_card');
add_action('wp_ajax_save_card_data', 'handle_save_card_data');

// Tambahkan AJAX handler untuk get card data
add_action('wp_ajax_get_card_data', 'handle_get_card_data');
add_action('wp_ajax_nopriv_get_card_data', 'handle_get_card_data');

// Tambahkan script untuk editor Elementor
function enqueue_card_admin_scripts() {
    // Enqueue jQuery
    wp_enqueue_script('jquery');
    
    // Enqueue card admin script
    wp_enqueue_script(
        'card-admin',
        CARD_PLUGIN_URL . 'assets/js/card-admin.js',
        ['jquery', 'elementor-editor'],
        time(), // Untuk development
        true
    );

    // Localize script
    wp_localize_script('card-admin', 'cardAdmin', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('card_nonce')
    ]);
}
add_action('elementor/editor/after_enqueue_scripts', 'enqueue_card_admin_scripts');

function handle_delete_card() {
    // Verifikasi nonce untuk keamanan
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'card_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    // Verifikasi card ID
    $card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;
    if ($card_id <= 0) {
        wp_send_json_error('Invalid card ID');
    }

    // Hapus post
    $result = wp_delete_post($card_id, true);
    
    if ($result) {
        wp_send_json_success('Card deleted successfully');
    } else {
        wp_send_json_error('Failed to delete card');
    }
}

function handle_save_card_data() {
    // Debug
    error_log('Received save card request: ' . print_r($_POST, true));

    // Verifikasi nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'card_nonce')) {
        error_log('Invalid nonce');
        wp_send_json_error('Security check failed');
        return;
    }

    // Validasi data
    if (empty($_POST['title'])) {
        error_log('Missing title');
        wp_send_json_error('Title is required');
        return;
    }

    // Cek apakah ini update atau create baru
    $card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;

    // Buat post data
    $post_data = array(
        'post_title'    => sanitize_text_field($_POST['title']),
        'post_type'     => 'card',
        'post_status'   => 'publish',
        'post_content'  => wp_kses_post($_POST['description'] ?? ''),
        'ID'            => $card_id // Jika ID ada, akan update post yang ada
    );

    error_log('Creating/Updating post with data: ' . print_r($post_data, true));

    // Insert/Update post
    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
        error_log('Error creating/updating post: ' . $post_id->get_error_message());
        wp_send_json_error($post_id->get_error_message());
        return;
    }

    error_log('Post created/updated with ID: ' . $post_id);

    // Update meta data
    update_post_meta($post_id, '_card_subtitle', sanitize_text_field($_POST['subtitle'] ?? ''));
    update_post_meta($post_id, '_card_button_enabled', $_POST['show_button'] ?? '');
    update_post_meta($post_id, '_card_button_text', sanitize_text_field($_POST['button_text'] ?? ''));
    update_post_meta($post_id, '_card_button_url', esc_url_raw($_POST['button_url'] ?? ''));

    // Handle gambar
    if (!empty($_POST['image_id'])) {
        set_post_thumbnail($post_id, absint($_POST['image_id']));
    }

    error_log('Card saved successfully');

    wp_send_json_success([
        'card_id' => $post_id,
        'message' => 'Card saved successfully'
    ]);
}

// Tambahkan hook untuk update post
add_action('post_updated', 'handle_card_update', 10, 3);

function handle_card_update($post_id, $post_after, $post_before) {
    // Pastikan ini adalah post type 'card'
    if ($post_after->post_type !== 'card') {
        return;
    }

    // Debug
    error_log('Card updated: ' . $post_id);
    error_log('Old title: ' . $post_before->post_title);
    error_log('New title: ' . $post_after->post_title);

    // Force refresh halaman yang menggunakan card ini
    add_action('wp_footer', function() use ($post_id) {
        ?>
        <script>
        // Refresh semua widget card yang menggunakan card ini
        if (typeof elementorFrontend !== 'undefined') {
            elementorFrontend.hooks.addAction('frontend/element_ready/custom_card.default', function($element) {
                var cardId = $element.find('.custom-card').data('card-id');
                if (cardId === '<?php echo $post_id; ?>') {
                    elementorFrontend.elements.$window.trigger('elementor/frontend/init');
                }
            });
        }
        </script>
        <?php
    });
}

// Tambahkan hook untuk refresh frontend
add_action('elementor/frontend/after_register_scripts', function() {
    wp_enqueue_script(
        'card-widget-script',
        CARD_PLUGIN_URL . 'assets/js/card-widget.js',
        ['jquery', 'elementor-frontend'],
        '1.0.0',
        true
    );

    wp_localize_script('card-widget-script', 'cardWidgetData', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('card_widget_nonce')
    ]);
});

function handle_get_card_data() {
    $card_id = isset($_GET['card_id']) ? intval($_GET['card_id']) : 0;
    
    if ($card_id <= 0) {
        wp_send_json_error('Invalid card ID');
        return;
    }

    $card = get_post($card_id);
    if (!$card) {
        wp_send_json_error('Card not found');
        return;
    }

    $data = [
        'title' => $card->post_title,
        'description' => $card->post_content,
        'subtitle' => get_post_meta($card_id, '_card_subtitle', true)
    ];

    if (has_post_thumbnail($card_id)) {
        $data['image_url'] = get_the_post_thumbnail_url($card_id);
    }

    wp_send_json_success($data);
}

// Tambahkan script untuk auto-update
add_action('wp_footer', function() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        function updateCards() {
            $('.elementor-widget-custom_card').each(function() {
                var $widget = $(this);
                var cardId = $widget.data('card-id');
                
                if (cardId) {
                    $.get(ajaxurl, {
                        action: 'get_card_data',
                        card_id: cardId
                    }, function(response) {
                        if (response.success) {
                            var data = response.data;
                            
                            // Update title
                            $widget.find('.card-title').text(data.title);
                            
                            // Update subtitle
                            $widget.find('.card-subtitle').text(data.subtitle);
                            
                            // Update description
                            $widget.find('.card-description').html(data.description);
                            
                            // Update image
                            if (data.image_url) {
                                $widget.find('.card-image img').attr('src', data.image_url);
                            }
                        }
                    });
                }
            });
        }

        // Update setiap 5 detik
        setInterval(updateCards, 5000);
    });
    </script>
    <?php
}, 100);

// Initialize
add_action('init', function() {
    Card_ACF::get_instance();
    Card_Ajax::get_instance();
});

// Enqueue admin styles
function enqueue_card_admin_styles() {
    $screen = get_current_screen();
    
    // Load styles hanya di edit screen untuk CPT card
    if ($screen && $screen->post_type === 'card') {
        wp_enqueue_style(
            'card-admin-style',
            CARD_PLUGIN_URL . 'assets/css/card-admin.css',
            [],
            '1.0.0'
        );
    }
}
add_action('admin_enqueue_scripts', 'enqueue_card_admin_styles');

// Register Card Category Taxonomy
function register_card_taxonomy() {
    $labels = array(
        'name'              => _x('Card Categories', 'taxonomy general name', 'card-plugin'),
        'singular_name'     => _x('Card Category', 'taxonomy singular name', 'card-plugin'),
        'search_items'      => __('Search Categories', 'card-plugin'),
        'all_items'         => __('All Categories', 'card-plugin'),
        'parent_item'       => __('Parent Category', 'card-plugin'),
        'parent_item_colon' => __('Parent Category:', 'card-plugin'),
        'edit_item'         => __('Edit Category', 'card-plugin'),
        'update_item'       => __('Update Category', 'card-plugin'),
        'add_new_item'      => __('Add New Category', 'card-plugin'),
        'new_item_name'     => __('New Category Name', 'card-plugin'),
        'menu_name'         => __('Categories', 'card-plugin'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'           => $labels,
        'show_ui'          => true,
        'show_admin_column' => true,
        'query_var'        => true,
        'rewrite'          => array('slug' => 'card-category'),
        'show_in_rest'     => true, // Enable Gutenberg editor support
    );

    register_taxonomy('card_category', array('card'), $args);
}
add_action('init', 'register_card_taxonomy'); 