<?php
/*
Plugin Name: FAQ Accortion Shortcode
Description: Add FAQ accordion items using shortcodes.
Version: 1.0
Author: VERSLO IDĖJŲ PARTNERIAI, UAB
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function faq_manage_assets() {
    global $post;

    wp_register_style(
        'faq-style', 
        plugin_dir_url(__FILE__) . 'assets/style.css', 
        array(), 
        '1.2'
    );

    wp_register_script(
        'faq-script', 
        plugin_dir_url(__FILE__) . 'assets/script.js', 
        array(), 
        '1.2', 
        true // Load in footer
    );

    if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'faq') || has_shortcode($post->post_content, 'faq_item'))) {
        wp_enqueue_style('faq-style');
        wp_enqueue_script('faq-script');
    }
}
add_action('wp_enqueue_scripts', 'faq_manage_assets');


function faq_accordion_wrapper_shortcode($atts, $content = null) {
    return '<div class="faq-accordion-container" itemscope itemtype="https://schema.org/FAQPage">' . trim(do_shortcode(shortcode_unautop($content))) . '</div>';
}
add_shortcode('faq', 'faq_accordion_wrapper_shortcode');


function faq_accordion_item_shortcode($atts, $content = null) {
    $a = shortcode_atts(array(
        'title' => '',
    ), $atts);

    $html = '';
    $html .= '<div class="faq-accordion-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">';
    $html .= '<h4 class="faq-accordion-item-title" itemprop="name">' . esc_html($a['title']) . '</h4>';
    $html .= '<div class="faq-accordion-item-content" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">';
    $html .= '<div itemprop="text">' . trim(do_shortcode(shortcode_unautop($content))) . '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}
add_shortcode('faq_item', 'faq_accordion_item_shortcode');