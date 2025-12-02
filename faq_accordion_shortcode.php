<?php
/*
Plugin Name: FAQ Accordion Shortcode
Description: Add FAQ accordion items using shortcodes. Includes an editor for styles, scripts, and icon settings.
Version: 1.2
Author: VERSLO IDĖJŲ PARTNERIAI, UAB
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function faq_manage_assets() {
    $css_path = plugin_dir_path(__FILE__) . 'assets/style.css';
    $js_path  = plugin_dir_path(__FILE__) . 'assets/script.js';
    $css_ver  = file_exists($css_path) ? filemtime($css_path) : '1.0';
    $js_ver   = file_exists($js_path)  ? filemtime($js_path)  : '1.0';

    wp_register_style('faq-style', plugin_dir_url(__FILE__) . 'assets/style.css', array(), $css_ver);
    wp_register_script('faq-script', plugin_dir_url(__FILE__) . 'assets/script.js', array(), $js_ver, true);
    
    wp_enqueue_style('faq-style');
    wp_enqueue_script('faq-script');
}
add_action('wp_enqueue_scripts', 'faq_manage_assets');

function faq_accordion_wrapper_shortcode($atts, $content = null) {
    return '<div class="faq-accordion-container" itemscope itemtype="https://schema.org/FAQPage">' . trim(do_shortcode(shortcode_unautop($content))) . '</div>';
}
add_shortcode('faq', 'faq_accordion_wrapper_shortcode');

function faq_accordion_item_shortcode($atts, $content = null) {
    $a = shortcode_atts(array('title' => ''), $atts);
    $html = '';
    $html .= '<div class="faq-accordion-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">';
    $html .= '<h4 class="faq-accordion-item-title" itemprop="name">' . esc_html($a['title']) . '</h4>';
    $html .= '<div class="faq-accordion-item-content" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">';
    $html .= '<div itemprop="text">' . trim(do_shortcode(shortcode_unautop($content))) . '</div>';
    $html .= '</div></div>';
    return $html;
}
add_shortcode('faq_item', 'faq_accordion_item_shortcode');

function faq_accordion_add_admin_menu() {
    add_options_page('FAQ Accordion Settings', 'FAQ Accordion', 'manage_options', 'faq-accordion-editor', 'faq_accordion_settings_page_html');
}
add_action('admin_menu', 'faq_accordion_add_admin_menu');

function faq_accordion_settings_page_html() {
    if (!current_user_can('manage_options')) return;

    $css_file = plugin_dir_path(__FILE__) . 'assets/style.css';
    $js_file  = plugin_dir_path(__FILE__) . 'assets/script.js';

    // Handle Form Submission
    if (isset($_POST['faq_save_settings'])) {
        check_admin_referer('faq_save_settings_nonce');
        $success = true;

        // 2. Save CSS/JS Files
        if (isset($_POST['faq_custom_css'])) {
            if (file_put_contents($css_file, wp_unslash($_POST['faq_custom_css'])) === false) $success = false;
        }
        if (isset($_POST['faq_custom_js'])) {
            if (file_put_contents($js_file, wp_unslash($_POST['faq_custom_js'])) === false) $success = false;
        }

        if ($success) {
            echo '<div class="notice notice-success is-dismissible"><p>Settings and files saved successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Error saving files. Check permissions.</p></div>';
        }
    }

    $current_css  = file_exists($css_file) ? file_get_contents($css_file) : '// Style file not found';
    $current_js   = file_exists($js_file) ? file_get_contents($js_file) : '// Script file not found';
    $files_writable = is_writable($css_file) && is_writable($js_file);

    ?>
    <div class="wrap">
        <h1>FAQ Accordion Settings</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('faq_save_settings_nonce'); ?>

            <?php if (!$files_writable): ?>
                <div class="notice notice-warning inline"><p><strong>Warning:</strong> Asset files are not writable. You cannot edit code, but you can change icons.</p></div>
            <?php endif; ?>

            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 300px;">
                    <h3>Style (CSS)</h3>
                    <textarea name="faq_custom_css" rows="15" class="large-text code" style="background: #f0f0f1; font-family: monospace;"><?php echo esc_textarea($current_css); ?></textarea>
                </div>
                <div style="flex: 1; min-width: 300px;">
                    <h3>Script (JavaScript)</h3>
                    <textarea name="faq_custom_js" rows="15" class="large-text code" style="background: #f0f0f1; font-family: monospace;"><?php echo esc_textarea($current_js); ?></textarea>
                </div>
            </div>

            <p class="submit">
                <input type="submit" name="faq_save_settings" id="submit" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>
    <?php
}