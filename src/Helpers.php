<?php

namespace WpMVC\Helpers;

defined( 'ABSPATH' ) || exit;

class Helpers
{
    public static function get_plugin_version( string $plugin_slug ) {
        // Define the path to the plugins directory
        $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
    
        // Construct the path to the main plugin file
        $main_file = $plugin_dir . '/' . $plugin_slug . '.php';
    
        // Check if the file exists
        if ( ! file_exists( $main_file ) ) {
            return null; // Plugin main file not found
        }
    
        // Read the file contents
        $file_contents = file_get_contents( $main_file );
    
        // Use a regex to find the version
        if ( preg_match( '/^\s*\* Version:\s*(.+)$/mi', $file_contents, $matches ) ) {
            return trim( $matches[1] );
        }
    
        return null; // Version not found
    }
}