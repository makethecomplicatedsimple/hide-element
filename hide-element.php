<?php
/*
 * Plugin Name:       Hide Element Shortcode
 * Plugin URI:        http://mtcs.co
 * Description:       Hide content of your pages.
 * Version:           1.0.0
 * Author:            Daniel Käfer
 * Author URI:        http://mtcs.co
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       hide-element
 * Domain Path:       /lang
 */

Namespace MTCS;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class Hide_Element {

    private $pattern;
 
    public function __construct() {
 
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action('wp_loaded', array( $this, 'hide_VC_integration' ) );

        add_filter('the_content', array( $this, 'get_shortcodes'), 1 );
        add_shortcode( 'hide', array( $this, 'hide_shortcode' ) );
        
    }

    function load_textdomain (){
        load_plugin_textdomain( 'hide-element', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
    }

    function get_shortcodes ( $content ){

        $this->pattern = get_shortcode_regex();
        
        $hidden_elements = $this->get_hidden_shortcodes( $content );
        
        $content = str_replace( $hidden_elements, '', $content );

        return $content;
    }

    private function get_hidden_shortcodes ( $content ){
        $replace = array();

        if ( preg_match_all( '/'. $this->pattern .'/s', $content, $matches ) ){
            
            if ( array_key_exists( 3, $matches ) ){
                foreach ( $matches[3] as $idx => $atts ) {
                    if ( strpos( $atts, 'hide_element="true"' ) !== false ){
                        $replace[] = $matches[0][$idx];
                    }elseif ( strpos( $atts, 'hide_element=\'true\'' ) !== false ){
                        $replace[] = $matches[0][$idx];
                    }
                }
            }

            if ( array_key_exists( 5, $matches ) ){
                foreach ( $matches[5] as $shortcode_content ) {
                    $replace = array_merge( $replace, $this->get_hidden_shortcodes( $shortcode_content ) );
                }
            }

        }
        
        return $replace;
    }

    function hide_VC_integration (){
        $attributes = array(
            'type' => 'checkbox',
            'heading' => __("Hide this?", "hide-element"),
            'param_name' => 'hide_element',
            'value' => array( __("Yes", "hide-element") => 'true'),
            'description' => __( "Hide this Element from the frontend", "hide-element" ),
            'weight' => 0,
        );

        if ( class_exists( '\WPBMap' ) ){
            $shortcodes = \WPBMap::getSortedUserShortCodes();
            foreach ( $shortcodes as $shortcode ) {
                vc_add_param( $shortcode['base'], $attributes );
            }
        }
    }

    function hide_shortcode( $atts, $content ) {
        $atts = shortcode_atts( array(
            'show' => 'false'
        ), $atts, 'hide' );

        if ( 'true' != $atts['show'] ){
            $content = '';
        }

        return do_shortcode( $content );
    }
 
}

$hide_element = new Hide_Element();