<?php
/*
Plugin Name: FAQ Accordion Shortcode
Description: Add FAQ accordion items using shortcodes. Includes a syntax-highlighted editor for styles and scripts.
Version: 1.2
Author: VERSLO IDĖJŲ PARTNERIAI, UAB
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// -----------------------------------------------------------------------------
// 1. Asset Management
// -----------------------------------------------------------------------------
function faq_manage_assets() {

    // Define paths for filemtime (versioning)
    $css_path = plugin_dir_path(__FILE__) . 'assets/style.css';
    $js_path  = plugin_dir_path(__FILE__) . 'assets/script.js';

    // Use file modification time as version to bust cache immediately on save
    $css_ver = file_exists($css_path) ? filemtime($css_path) : '1.0';
    $js_ver  = file_exists($js_path)  ? filemtime($js_path)  : '1.0';

    wp_register_style(
        'faq-style', 
        plugin_dir_url(__FILE__) . 'assets/style.css', 
        array(), 
        $css_ver
    );

    wp_register_script(
        'faq-script', 
        plugin_dir_url(__FILE__) . 'assets/script.js', 
        array(), 
        $js_ver, 
        true // Load in footer
    );

    wp_enqueue_style('faq-style');
    wp_enqueue_script('faq-script');
}
add_action('wp_enqueue_scripts', 'faq_manage_assets');

// -----------------------------------------------------------------------------
// 2. Admin Assets (Code Editor)
// -----------------------------------------------------------------------------
function faq_accordion_admin_assets($hook) {
    // Only load on our specific settings page
    if ($hook !== 'settings_page_faq-accordion-editor') {
        return;
    }

    // Enable the WordPress Code Editor
    // We check for the function to ensure compatibility with older WP versions (pre 4.9)
    if (function_exists('wp_enqueue_code_editor')) {
        
        // Enqueue settings for CSS
        $settings_css = wp_enqueue_code_editor(array('type' => 'text/css'));
        
        // Enqueue settings for JS
        $settings_js = wp_enqueue_code_editor(array('type' => 'text/javascript'));

        // If the user hasn't disabled syntax highlighting in their profile
        if (false !== $settings_css && false !== $settings_js) {
            wp_add_inline_script(
                'code-editor',
                sprintf(
                    'jQuery( function() { 
                        if( wp.codeEditor ) {
                            wp.codeEditor.initialize( "faq_custom_css", %s ); 
                            wp.codeEditor.initialize( "faq_custom_js", %s ); 
                        }
                     } );',
                    wp_json_encode($settings_css),
                    wp_json_encode($settings_js)
                )
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'faq_accordion_admin_assets');

// -----------------------------------------------------------------------------
// 3. Shortcode Logic
// -----------------------------------------------------------------------------
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

// -----------------------------------------------------------------------------
// 4. Settings Page (CSS/JS Editor)
// -----------------------------------------------------------------------------

// Register the menu item
function faq_accordion_add_admin_menu() {
    add_options_page(
        'FAQ Accordion',     // Page Title
        'FAQ Accordion',            // Menu Title
        'manage_options',           // Capability required
        'faq-accordion-editor',     // Menu Slug
        'faq_accordion_settings_page_html' // Callback function
    );
}
add_action('admin_menu', 'faq_accordion_add_admin_menu');

// Render the settings page
function faq_accordion_settings_page_html() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Define file paths
    $css_file = plugin_dir_path(__FILE__) . 'assets/style.css';
    $js_file  = plugin_dir_path(__FILE__) . 'assets/script.js';

    // Handle Form Submission
    if (isset($_POST['faq_save_settings'])) {
        // Verify nonce for security
        check_admin_referer('faq_save_settings_nonce');

        $success = true;

        // Save CSS
        if (isset($_POST['faq_custom_css'])) {
            $css_content = wp_unslash($_POST['faq_custom_css']);
            if (file_put_contents($css_file, $css_content) === false) {
                $success = false;
            }
        }

        // Save JS
        if (isset($_POST['faq_custom_js'])) {
            $js_content = wp_unslash($_POST['faq_custom_js']);
            if (file_put_contents($js_file, $js_content) === false) {
                $success = false;
            }
        }

        if ($success) {
            echo '<div class="notice notice-success is-dismissible"><p>Assets saved successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>There was an error saving the files. Please check file permissions.</p></div>';
        }
    }

    // Read current content to display in textarea
    $current_css = file_exists($css_file) ? file_get_contents($css_file) : '/* Style file not found */';
    $current_js  = file_exists($js_file) ? file_get_contents($js_file)  : '// Script file not found';

    // Check if files are writable
    $files_writable = is_writable($css_file) && is_writable($js_file);

    ?>
    <div class="wrap">
        <h1>FAQ Accordion Settings</h1>
        
        <?php if (!$files_writable): ?>
            <div class="notice notice-warning">
                <p><strong>Warning:</strong> Your asset files are not writable by the server. You may not be able to save changes. Please check permissions for the <code>/assets/</code> folder (chmod 755 or 644).</p>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field('faq_save_settings_nonce'); ?>
            
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                
                <div style="flex: 1; min-width: 300px;">
                    <h2>Style (CSS)</h2>
                    <p><em>Located in: /assets/style.css</em></p>
                    <textarea name="faq_custom_css" id="faq_custom_css" rows="20" class="large-text code"><?php echo esc_textarea($current_css); ?></textarea>
                </div>

                <div style="flex: 1; min-width: 300px;">
                    <h2>Script (JavaScript)</h2>
                    <p><em>Located in: /assets/script.js</em></p>
                    <textarea name="faq_custom_js" id="faq_custom_js" rows="20" class="large-text code"><?php echo esc_textarea($current_js); ?></textarea>
                </div>

            </div>

            <br>
            <p class="submit">
                <input type="submit" name="faq_save_settings" id="submit" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>
    <?php
}