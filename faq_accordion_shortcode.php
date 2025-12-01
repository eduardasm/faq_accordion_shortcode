<?php
/*
Plugin Name: Simple Shortcode Accordion
Description: Adds [accordion] and [accordion_item] shortcodes to create collapsible content.
Version: 1.0
Author: Gemini
*/

// 1. Register the Wrapper Shortcode [accordion]
function ssa_accordion_wrapper_shortcode($atts, $content = null) {
    // Enqueue styles and scripts only when shortcode is used
    wp_enqueue_style('ssa-style');
    wp_enqueue_script('ssa-script');

    // Return the wrapper div
    return '<div class="ssa-accordion-container">' . do_shortcode($content) . '</div>';
}
add_shortcode('accordion', 'ssa_accordion_wrapper_shortcode');

// 2. Register the Item Shortcode [accordion_item title="..."]
function ssa_accordion_item_shortcode($atts, $content = null) {
    // Extract attributes
    $a = shortcode_atts(array(
        'title' => 'Click to Expand',
    ), $atts);

    $html = '';
    // The clickable title/button
    $html .= '<button class="ssa-accordion-trigger">' . esc_html($a['title']) . '</button>';
    // The content panel
    $html .= '<div class="ssa-accordion-panel">';
    $html .= '<div class="ssa-inner-content">' . do_shortcode($content) . '</div>';
    $html .= '</div>';

    return $html;
}
add_shortcode('accordion_item', 'ssa_accordion_item_shortcode');

// 3. Register Assets (CSS & JS)
function ssa_register_assets() {
    // Register CSS (Inline for simplicity in this example)
    wp_register_style('ssa-style', false);
    wp_add_inline_style('ssa-style', "
        .ssa-accordion-container {
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .ssa-accordion-trigger {
            background-color: #f1f1f1;
            color: #444;
            cursor: pointer;
            padding: 18px;
            width: 100%;
            border: none;
            text-align: left;
            outline: none;
            font-size: 16px;
            transition: 0.4s;
            border-bottom: 1px solid #ddd;
            font-weight: 600;
        }
        .ssa-accordion-trigger:hover, 
        .ssa-accordion-trigger.active {
            background-color: #e7e7e7;
        }
        /* Icon for open/close state */
        .ssa-accordion-trigger:after {
            content: '+'; 
            color: #777;
            font-weight: bold;
            float: right;
            margin-left: 5px;
        }
        .ssa-accordion-trigger.active:after {
            content: '-';
        }
        .ssa-accordion-trigger:last-child {
            border-bottom: none;
        }
        /* Hidden Panel */
        .ssa-accordion-panel {
            padding: 0 18px;
            background-color: white;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.2s ease-out;
            border-bottom: 1px solid #ddd;
        }
        /* Remove border from last panel */
        .ssa-accordion-panel:last-of-type {
            border-bottom: none;
        }
        .ssa-inner-content {
            padding: 15px 0;
        }
    ");

    // Register JS (Inline for simplicity)
    wp_register_script('ssa-script', false);
    wp_add_inline_script('ssa-script', "
        document.addEventListener('DOMContentLoaded', function() {
            var acc = document.getElementsByClassName('ssa-accordion-trigger');
            
            for (var i = 0; i < acc.length; i++) {
                acc[i].addEventListener('click', function() {
                    // Toggle the active class
                    this.classList.toggle('active');
                    
                    // Toggle the panel visibility
                    var panel = this.nextElementSibling;
                    if (panel.style.maxHeight) {
                        panel.style.maxHeight = null;
                    } else {
                        // Calculate exact height for smooth animation
                        panel.style.maxHeight = panel.scrollHeight + 'px';
                    } 
                });
            }
        });
    ");
}
add_action('wp_enqueue_scripts', 'ssa_register_assets');