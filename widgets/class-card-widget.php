<?php
namespace Card_Plugin\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;
use Elementor\Repeater;

if (!defined('ABSPATH')) exit;

if (!class_exists('\Elementor\Widget_Base')) return;

class Card_Widget extends Widget_Base {
    public function get_name() {
        return 'custom_card';
    }

    public function get_title() {
        return 'Custom Card';
    }

    public function get_icon() {
        return 'eicon-price-table';
    }

    public function get_categories() {
        return ['basic'];
    }

    public function get_style_depends() {
        return ['elementor-icons', 'card-widget-style'];
    }

    public function get_script_depends() {
        return ['card-widget-script'];
    }

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        
        \wp_register_style(
            'card-widget-style',
            CARD_PLUGIN_URL . 'assets/css/card-widget.css',
            [],
            '1.0.0'
        );

        \wp_register_script(
            'card-widget-script',
            CARD_PLUGIN_URL . 'assets/js/card-widget.js',
            ['jquery', 'elementor-frontend'],
            '1.0.0',
            true
        );
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Card Content',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Select Card Control
        $this->add_control(
            'select_card',
            [
                'label' => __('Select Card', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_cards_list(),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        // Button Section
        $this->start_controls_section(
            'button_section',
            [
                'label' => __('Button', 'card-plugin'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_button',
            [
                'label' => __('Show Button', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'card-plugin'),
                'label_off' => __('No', 'card-plugin'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Click Here', 'card-plugin'),
                'placeholder' => __('Enter button text', 'card-plugin'),
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_url',
            [
                'label' => __('Button URL', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'card-plugin'),
                'show_external' => true,
                'default' => [
                    'url' => '',
                    'is_external' => false,
                    'nofollow' => false,
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_size',
            [
                'label' => __('Size', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'sm',
                'options' => [
                    'xs' => __('Extra Small', 'card-plugin'),
                    'sm' => __('Small', 'card-plugin'),
                    'md' => __('Medium', 'card-plugin'),
                    'lg' => __('Large', 'card-plugin'),
                    'xl' => __('Extra Large', 'card-plugin'),
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_style',
            [
                'label' => __('Style', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => __('Default', 'card-plugin'),
                    'primary' => __('Primary', 'card-plugin'),
                    'secondary' => __('Secondary', 'card-plugin'),
                    'outline' => __('Outline', 'card-plugin'),
                    'link' => __('Link', 'card-plugin'),
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        // Button Icon
        $this->add_control(
            'button_icon',
            [
                'label' => __('Icon', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => '',
                    'library' => 'solid',
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_icon_position',
            [
                'label' => __('Icon Position', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'after',
                'options' => [
                    'before' => __('Before', 'card-plugin'),
                    'after' => __('After', 'card-plugin'),
                ],
                'condition' => [
                    'show_button' => 'yes',
                    'button_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'button_icon_spacing',
            [
                'label' => __('Icon Spacing', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 4,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-button .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_button' => 'yes',
                    'button_icon[value]!' => '',
                ],
            ]
        );

        // Button Colors
        $this->start_controls_tabs('button_style_tabs');

        // Normal State
        $this->start_controls_tab(
            'button_normal_state',
            [
                'label' => __('Normal', 'card-plugin'),
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => __('Background Color', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->end_controls_tab();

        // Hover State
        $this->start_controls_tab(
            'button_hover_state',
            [
                'label' => __('Hover', 'card-plugin'),
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_hover_text_color',
            [
                'label' => __('Text Color', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button:hover' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_hover_background_color',
            [
                'label' => __('Background Color', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button:hover' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label' => __('Border Color', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button:hover' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'show_button' => 'yes',
                    'button_border_border!' => '',
                ],
            ]
        );

        $this->add_control(
            'button_hover_animation',
            [
                'label' => __('Hover Animation', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::HOVER_ANIMATION,
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        // Button Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .elementor-button',
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        // Button Border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .elementor-button',
                'separator' => 'before',
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        // Button Padding
        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        // Button Shadow
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .elementor-button',
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_animation',
            [
                'label' => __('Hover Animation', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('None', 'card-plugin'),
                    'fade' => __('Fade', 'card-plugin'),
                    'scale' => __('Scale', 'card-plugin'),
                    'slide-up' => __('Slide Up', 'card-plugin'),
                    'slide-right' => __('Slide Right', 'card-plugin'),
                    'rotate' => __('Rotate', 'card-plugin'),
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_direction',
            [
                'label' => __('Direction', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'horizontal' => [
                        'title' => __('Horizontal', 'card-plugin'),
                        'icon' => 'eicon-h-align-stretch',
                    ],
                    'vertical' => [
                        'title' => __('Vertical', 'card-plugin'),
                        'icon' => 'eicon-v-align-stretch',
                    ],
                ],
                'default' => 'horizontal',
                'toggle' => true,
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_alignment',
            [
                'label' => __('Alignment', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => __('Left', 'card-plugin'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'card-plugin'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'end' => [
                        'title' => __('Right', 'card-plugin'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'stretch' => [
                        'title' => __('Stretch', 'card-plugin'),
                        'icon' => 'eicon-h-align-stretch',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_gap',
            [
                'label' => __('Spacing', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button-wrapper' => '--card-button-gap: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Card Style Section
        $this->start_controls_section(
            'section_card_style',
            [
                'label' => __('Card Style', 'card-plugin'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_background_color',
            [
                'label' => __('Background Color', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-card' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'selector' => '{{WRAPPER}} .elementor-card',
            ]
        );

        $this->add_control(
            'card_border_radius',
            [
                'label' => __('Border Radius', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .elementor-card',
            ]
        );

        $this->add_responsive_control(
            'card_padding',
            [
                'label' => __('Padding', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Title Style Section
        $this->start_controls_section(
            'section_title_style',
            [
                'label' => __('Title Style', 'card-plugin'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Color', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-heading-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .elementor-heading-title',
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label' => __('Margin', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-heading-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Content Style Section
        $this->start_controls_section(
            'section_content_style',
            [
                'label' => __('Content Style', 'card-plugin'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_color',
            [
                'label' => __('Color', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-text-editor' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'content_typography',
                'selector' => '{{WRAPPER}} .elementor-text-editor',
            ]
        );

        $this->add_responsive_control(
            'content_margin',
            [
                'label' => __('Margin', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-text-editor' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Divider Section
        $this->start_controls_section(
            'section_divider',
            [
                'label' => __('Divider', 'card-plugin'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'show_divider',
            [
                'label' => __('Show Divider', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'card-plugin'),
                'label_off' => __('No', 'card-plugin'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'divider_style',
            [
                'label' => __('Style', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'solid',
                'options' => [
                    'solid' => __('Solid', 'card-plugin'),
                    'double' => __('Double', 'card-plugin'),
                    'dotted' => __('Dotted', 'card-plugin'),
                    'dashed' => __('Dashed', 'card-plugin'),
                ],
                'condition' => [
                    'show_divider' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-divider' => 'border-style: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'divider_weight',
            [
                'label' => __('Ketebalan', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0.1,
                ],
                'range' => [
                    'px' => [
                        'min' => 0.1,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'show_divider' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-divider' => 'height: {{SIZE}}px; background-color: var(--divider-color); border: none;',
                ],
            ]
        );

        $this->add_control(
            'divider_color',
            [
                'label' => __('Color', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ddd',
                'condition' => [
                    'show_divider' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--divider-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'divider_width',
            [
                'label' => __('Width', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%', 'px'],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                ],
                'condition' => [
                    'show_divider' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-divider' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'divider_gap',
            [
                'label' => __('Gap', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 15,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'condition' => [
                    'show_divider' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-divider-wrapper' => 'margin-top: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'divider_alignment',
            [
                'label' => __('Alignment', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __('Left', 'card-plugin'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'card-plugin'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => __('Right', 'card-plugin'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'condition' => [
                    'show_divider' => 'yes',
                    'divider_width!' => '100',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-divider-wrapper' => 'display: flex; justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'divider_position',
            [
                'label' => __('Divider Position', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'after_title',
                'options' => [
                    'before_title' => __('Before Title', 'card-plugin'),
                    'after_title' => __('After Title', 'card-plugin'),
                    'before_image' => __('Before Image', 'card-plugin'),
                    'after_image' => __('After Image', 'card-plugin'),
                    'before_content' => __('Before Content', 'card-plugin'),
                    'after_content' => __('After Content', 'card-plugin'),
                    'before_button' => __('Before Button', 'card-plugin'),
                    'after_button' => __('After Button', 'card-plugin')
                ],
                'condition' => [
                    'show_divider' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Image Style Section
        $this->start_controls_section(
            'section_image_style',
            [
                'label' => __('Image Style', 'card-plugin'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Image Size
        $this->add_responsive_control(
            'image_width',
            [
                'label' => __('Width', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%', 'px'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-card-image' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_max_width',
            [
                'label' => __('Max Width', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-card-image' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label' => __('Height', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-card-image' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Image Border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'selector' => '{{WRAPPER}} .elementor-card-image img',
                'separator' => 'before',
            ]
        );

        // Image Border Radius
        $this->add_control(
            'image_border_radius',
            [
                'label' => __('Border Radius', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-card-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Image Box Shadow
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'image_box_shadow',
                'selector' => '{{WRAPPER}} .elementor-card-image img',
            ]
        );

        // Image Margin
        $this->add_responsive_control(
            'image_margin',
            [
                'label' => __('Margin', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-card-image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Image Padding
        $this->add_responsive_control(
            'image_padding',
            [
                'label' => __('Padding', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-card-image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Image Alignment
        $this->add_responsive_control(
            'image_alignment',
            [
                'label' => __('Alignment', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'card-plugin'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'card-plugin'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'card-plugin'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .elementor-card-image' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Spacing Section
        $this->start_controls_section(
            'section_spacing',
            [
                'label' => __('Spacing', 'card-plugin'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Image Spacing
        $this->add_responsive_control(
            'image_gap',
            [
                'label' => __('Image Bottom Spacing', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-card-image' => '--card-image-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Title Spacing
        $this->add_responsive_control(
            'title_gap',
            [
                'label' => __('Title Bottom Spacing', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-heading-title' => '--card-title-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Description Spacing
        $this->add_responsive_control(
            'description_gap',
            [
                'label' => __('Description Bottom Spacing', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-text-editor' => '--card-description-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Card Padding
        $this->add_responsive_control(
            'card_padding',
            [
                'label' => __('Card Padding', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', '%'],
                'selectors' => [
                    '{{WRAPPER}} .custom-card' => '--card-padding-top: {{TOP}}{{UNIT}}; --card-padding-right: {{RIGHT}}{{UNIT}}; --card-padding-bottom: {{BOTTOM}}{{UNIT}}; --card-padding-left: {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Content Spacing
        $this->add_responsive_control(
            'content_gap',
            [
                'label' => __('Content Bottom Spacing', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-card-content' => '--card-content-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Meta Info Spacing
        $this->add_responsive_control(
            'meta_gap',
            [
                'label' => __('Meta Info Bottom Spacing', 'card-plugin'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .card-meta' => '--card-meta-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        if (!empty($settings['select_card'])) {
            $card_post = \get_post($settings['select_card']);
            
            if ($card_post) {
                // Get image from ACF
                $image = get_field('_card_image', $card_post->ID);
                
                ?>
                <div class="elementor-card">
                    <?php 
                    // Render image
                    if ($image && is_array($image)) : ?>
                        <div class="elementor-card-image">
                            <img src="<?php echo esc_url($image['url']); ?>" 
                                 alt="<?php echo esc_attr($image['alt']); ?>" 
                                 class="elementor-card-img" />
                        </div>
                    <?php endif;

                    // Render title
                    if (!empty($card_post->post_title)) : ?>
                        <h3 class="elementor-heading-title"><?php echo \esc_html($card_post->post_title); ?></h3>
                    <?php endif; ?>
                    
                    <?php // Render content
                    if (!empty($card_post->post_content)) : ?>
                        <div class="elementor-text-editor"><?php echo \wp_kses_post($card_post->post_content); ?></div>
                    <?php endif; ?>

                    <?php
                    // Render buttons dari settings widget (bukan dari ACF)
                    if ($settings['show_button'] === 'yes' && !empty($settings['button_text'])) : 
                        // Set button attributes
                        $this->add_render_attribute(
                            'button',
                            [
                                'class' => [
                                    'elementor-button',
                                    'elementor-size-' . $settings['button_size'],
                                    'elementor-button-' . $settings['button_style'],
                                    $settings['button_animation'] ? 'with-' . $settings['button_animation'] : '',
                                ],
                                'href' => $settings['button_url']['url'] ?? '#',
                                'target' => !empty($settings['button_url']['is_external']) ? '_blank' : '_self',
                                'rel' => !empty($settings['button_url']['nofollow']) ? 'nofollow' : '',
                            ]
                        );
                    ?>
                        <div class="elementor-button-wrapper <?php echo esc_attr($settings['button_direction'] ?? 'horizontal'); ?> <?php echo esc_attr($settings['button_alignment'] ?? 'center'); ?>">
                            <a <?php echo $this->get_render_attribute_string('button'); ?>>
                                <span class="elementor-button-content-wrapper">
                                    <?php if (!empty($settings['button_icon']['value'])) : ?>
                                        <?php if ($settings['button_icon_position'] === 'before') : ?>
                                            <span class="elementor-button-icon elementor-align-icon-left">
                                                <?php \Elementor\Icons_Manager::render_icon($settings['button_icon'], ['aria-hidden' => 'true']); ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <span class="elementor-button-text"><?php echo esc_html($settings['button_text']); ?></span>
                                    
                                    <?php if (!empty($settings['button_icon']['value'])) : ?>
                                        <?php if ($settings['button_icon_position'] === 'after') : ?>
                                            <span class="elementor-button-icon elementor-align-icon-right">
                                                <?php \Elementor\Icons_Manager::render_icon($settings['button_icon'], ['aria-hidden' => 'true']); ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            }
        } else {
            echo '<div class="elementor-notice">Please select a card to display</div>';
        }
    }

    private function get_cards_list() {
        $cards = [];
        
        $args = [
            'post_type' => 'card',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ];
        
        $query = new \WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $id = \get_the_ID();
                $title = \get_the_title();
                $cards[$id] = $title;
            }
        }
        
        \wp_reset_postdata();
        
        return $cards;
    }

    private function get_pages_list() {
        $pages = [];
        
        $args = [
            'post_type' => 'page',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ];
        
        $query = new \WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $id = \get_the_ID();
                $title = \get_the_title();
                $pages[$id] = $title;
            }
        }
        
        \wp_reset_postdata();
        
        return $pages;
    }

    private function get_posts_list() {
        $posts = [];
        
        $args = [
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ];
        
        $query = new \WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $id = \get_the_ID();
                $title = \get_the_title();
                $posts[$id] = $title;
            }
        }
        
        \wp_reset_postdata();
        
        return $posts;
    }
} 