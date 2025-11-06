<?php
/**
 * Uninstall script for URL Conflict Detector
 * 
 * This file is executed when the plugin is deleted from the WordPress admin.
 * It removes all plugin data including database tables.
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Define table name
$table_name = $wpdb->prefix . 'url_conflicts';

// Drop the conflicts table
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

// Clean up any options if they exist (currently plugin doesn't use options, but good practice)
delete_option('url_conflict_detector_version');
delete_option('url_conflict_detector_settings');

// For multisite installations
if (is_multisite()) {
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        
        $table_name = $wpdb->prefix . 'url_conflicts';
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        
        delete_option('url_conflict_detector_version');
        delete_option('url_conflict_detector_settings');
        
        restore_current_blog();
    }
}
