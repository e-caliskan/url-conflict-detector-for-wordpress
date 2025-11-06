<?php
/**
 * Plugin Name: URL Conflict Detector
 * Plugin URI: https://github.com/e-caliskan/url-conflict-detector-for-wordpress
 * Description: Detects and resolves URL conflicts between products, media, categories, tags, pages, and posts in WordPress.
 * Version: 1.0.0
 * Author: Emrah Çalışkan
 * Author URI: https://emrahcaliskan.tr
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: url-conflict-detector
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

class URL_Conflict_Detector {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'url_conflicts';
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_scan_conflicts', array($this, 'ajax_scan_conflicts'));
        add_action('wp_ajax_fix_conflict', array($this, 'ajax_fix_conflict'));
        add_action('wp_ajax_get_conflict_details', array($this, 'ajax_get_conflict_details'));
        add_action('wp_ajax_get_available_types', array($this, 'ajax_get_available_types'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('url-conflict-detector', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            url varchar(255) NOT NULL,
            type_1 varchar(50) NOT NULL,
            id_1 bigint(20) NOT NULL,
            title_1 text NOT NULL,
            type_2 varchar(50) NOT NULL,
            id_2 bigint(20) NOT NULL,
            title_2 text NOT NULL,
            status varchar(20) DEFAULT 'pending',
            scan_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY url_index (url)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function deactivate() {
        // Optional: Drop table on deactivation (not recommended)
        // Table will be dropped on uninstall via uninstall.php
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('URL Conflicts', 'url-conflict-detector'),
            __('URL Conflicts', 'url-conflict-detector'),
            'manage_options',
            'url-conflict-detector',
            array($this, 'admin_page'),
            'dashicons-warning',
            30
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_url-conflict-detector') {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'url-conflict-detector-admin',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue jQuery
        wp_enqueue_script('jquery');
        
        // Enqueue JS
        wp_enqueue_script(
            'url-conflict-detector-admin',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script for AJAX and translations
        wp_localize_script('url-conflict-detector-admin', 'urlConflictDetector', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('url_conflict_detector_nonce'),
            'i18n' => array(
                'allContentTypes' => __('All Content Types', 'url-conflict-detector'),
                'allTaxonomies' => __('All Taxonomies', 'url-conflict-detector'),
                'selectAtLeastOne' => __('Please select at least one scan option.', 'url-conflict-detector'),
                'error' => __('Error', 'url-conflict-detector'),
                'scanError' => __('An error occurred during scanning.', 'url-conflict-detector'),
                'noConflicts' => __('Great! No conflicts found.', 'url-conflict-detector'),
                'resolved' => __('Resolved', 'url-conflict-detector'),
                'pending' => __('Pending', 'url-conflict-detector'),
                'fix' => __('Fix', 'url-conflict-detector'),
                'fixed' => __('Fixed', 'url-conflict-detector'),
                'conflictingUrl' => __('Conflicting URL', 'url-conflict-detector'),
                'newSlug' => __('New Slug', 'url-conflict-detector'),
                'updateItem' => __('Update This Item', 'url-conflict-detector'),
                'cancel' => __('Cancel', 'url-conflict-detector'),
                'enterValidSlug' => __('Please enter a valid slug.', 'url-conflict-detector'),
                'updating' => __('Updating...', 'url-conflict-detector'),
                'slugUpdated' => __('Slug successfully updated!', 'url-conflict-detector'),
                'errorOccurred' => __('An error occurred.', 'url-conflict-detector')
            )
        ));
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('URL Conflict Detection and Resolution', 'url-conflict-detector'); ?></h1>
            
            <div class="ucd-scan-options">
                <h3><?php esc_html_e('Select Items to Scan', 'url-conflict-detector'); ?></h3>
                <div id="scan-options-container">
                    <p><?php esc_html_e('Loading options...', 'url-conflict-detector'); ?></p>
                </div>
            </div>
            
            <div class="ucd-header">
                <button id="scan-conflicts" class="button button-primary button-large">
                    <span class="dashicons dashicons-search"></span> <?php esc_html_e('Scan for Conflicts', 'url-conflict-detector'); ?>
                </button>
                <div id="scan-progress" style="display:none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <p class="progress-text"><?php esc_html_e('Scanning...', 'url-conflict-detector'); ?></p>
                </div>
            </div>
            
            <div id="conflicts-summary" style="display:none;">
                <div class="ucd-stats">
                    <div class="stat-box">
                        <h3 id="total-conflicts">0</h3>
                        <p><?php esc_html_e('Total Conflicts', 'url-conflict-detector'); ?></p>
                    </div>
                    <div class="stat-box">
                        <h3 id="pending-conflicts">0</h3>
                        <p><?php esc_html_e('Pending', 'url-conflict-detector'); ?></p>
                    </div>
                    <div class="stat-box">
                        <h3 id="resolved-conflicts">0</h3>
                        <p><?php esc_html_e('Resolved', 'url-conflict-detector'); ?></p>
                    </div>
                </div>
            </div>
            
            <div id="conflicts-list">
                <h2><?php esc_html_e('Detected Conflicts', 'url-conflict-detector'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('URL', 'url-conflict-detector'); ?></th>
                            <th><?php esc_html_e('First Item', 'url-conflict-detector'); ?></th>
                            <th><?php esc_html_e('Second Item', 'url-conflict-detector'); ?></th>
                            <th><?php esc_html_e('Status', 'url-conflict-detector'); ?></th>
                            <th><?php esc_html_e('Actions', 'url-conflict-detector'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="conflicts-tbody">
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px;">
                                <?php esc_html_e('Select items from above and click "Scan for Conflicts" button to start scanning.', 'url-conflict-detector'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Fix Modal -->
        <div id="fix-modal" class="ucd-modal" style="display:none;">
            <div class="ucd-modal-content">
                <span class="ucd-close">&times;</span>
                <h2><?php esc_html_e('Fix Conflict', 'url-conflict-detector'); ?></h2>
                <div id="fix-modal-body"></div>
            </div>
        </div>
        <?php
    }
    
    public function ajax_get_available_types() {
        check_ajax_referer('url_conflict_detector_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission.', 'url-conflict-detector'));
        }
        
        global $wpdb;
        
        $available_types = array(
            'post_types' => array(),
            'taxonomies' => array()
        );
        
        // Post Types
        $post_types = get_post_types(array('public' => true), 'objects');
        foreach ($post_types as $post_type) {
            if (in_array($post_type->name, array('attachment'))) {
                continue; // Media files will be added separately
            }
            
            $count = wp_count_posts($post_type->name);
            $total = 0;
            foreach ($count as $status => $num) {
                if (in_array($status, array('publish', 'draft', 'pending', 'private'))) {
                    $total += $num;
                }
            }
            
            if ($total > 0) {
                $available_types['post_types'][] = array(
                    'value' => $post_type->name,
                    'label' => $post_type->labels->name,
                    'count' => $total
                );
            }
        }
        
        // Media (Attachment)
        $media_count = wp_count_posts('attachment');
        $media_total = isset($media_count->inherit) ? $media_count->inherit : 0;
        if ($media_total > 0) {
            $available_types['post_types'][] = array(
                'value' => 'media',
                'label' => __('Media', 'url-conflict-detector'),
                'count' => $media_total
            );
        }
        
        // Taxonomies
        $taxonomies = get_taxonomies(array('public' => true), 'objects');
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomy->name,
                'hide_empty' => false,
                'count' => true
            ));
            
            if (!is_wp_error($terms) && count($terms) > 0) {
                $available_types['taxonomies'][] = array(
                    'value' => $taxonomy->name,
                    'label' => $taxonomy->labels->name,
                    'count' => count($terms)
                );
            }
        }
        
        wp_send_json_success($available_types);
    }
    
    public function ajax_scan_conflicts() {
        check_ajax_referer('url_conflict_detector_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission.', 'url-conflict-detector'));
        }
        
        $scan_types = isset($_POST['scan_types']) ? $_POST['scan_types'] : array();
        
        if (empty($scan_types)) {
            wp_send_json_error(__('Please select items to scan.', 'url-conflict-detector'));
        }
        
        global $wpdb;
        
        // First clear existing records
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");
        
        $conflicts = array();
        $urls = array();
        
        // Collect all URLs
        $items = $this->get_all_urls($scan_types);
        
        // Group URLs and detect conflicts
        foreach ($items as $item) {
            $url = $item['url'];
            if (!isset($urls[$url])) {
                $urls[$url] = array();
            }
            $urls[$url][] = $item;
        }
        
        // Save conflicts
        foreach ($urls as $url => $items_list) {
            if (count($items_list) > 1) {
                for ($i = 0; $i < count($items_list); $i++) {
                    for ($j = $i + 1; $j < count($items_list); $j++) {
                        $wpdb->insert(
                            $this->table_name,
                            array(
                                'url' => $url,
                                'type_1' => $items_list[$i]['type'],
                                'id_1' => $items_list[$i]['id'],
                                'title_1' => $items_list[$i]['title'],
                                'type_2' => $items_list[$j]['type'],
                                'id_2' => $items_list[$j]['id'],
                                'title_2' => $items_list[$j]['title'],
                                'status' => 'pending'
                            ),
                            array('%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s')
                        );
                        $conflicts[] = array(
                            'url' => $url,
                            'items' => array($items_list[$i], $items_list[$j])
                        );
                    }
                }
            }
        }
        
        $stats = $this->get_conflict_stats();
        
        wp_send_json_success(array(
            'conflicts' => $this->get_conflicts_for_display(),
            'stats' => $stats
        ));
    }
    
    private function get_all_urls($scan_types) {
        global $wpdb;
        $items = array();
        
        foreach ($scan_types as $type) {
            // Taxonomy check
            if (strpos($type, 'taxonomy_') === 0) {
                $taxonomy_name = str_replace('taxonomy_', '', $type);
                $terms = get_terms(array(
                    'taxonomy' => $taxonomy_name,
                    'hide_empty' => false
                ));
                
                if (!is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        $full_url = $this->get_full_permalink($term->term_id, 'taxonomy_' . $taxonomy_name);
                        $items[] = array(
                            'type' => 'taxonomy_' . $taxonomy_name,
                            'id' => $term->term_id,
                            'title' => $term->name,
                            'url' => $full_url
                        );
                    }
                }
            }
            // Special media check
            elseif ($type === 'media') {
                $attachments = $wpdb->get_results("
                    SELECT ID, post_title, post_name 
                    FROM {$wpdb->posts} 
                    WHERE post_type = 'attachment'
                ");
                
                foreach ($attachments as $attachment) {
                    $full_url = $this->get_full_permalink($attachment->ID, 'media');
                    $items[] = array(
                        'type' => 'media',
                        'id' => $attachment->ID,
                        'title' => $attachment->post_title,
                        'url' => $full_url
                    );
                }
            }
            // Post Type
            else {
                $posts = $wpdb->get_results($wpdb->prepare("
                    SELECT ID, post_title, post_name 
                    FROM {$wpdb->posts} 
                    WHERE post_type = %s 
                    AND post_status IN ('publish', 'draft', 'pending', 'private')
                ", $type));
                
                foreach ($posts as $post) {
                    $full_url = $this->get_full_permalink($post->ID, $type);
                    $items[] = array(
                        'type' => $type,
                        'id' => $post->ID,
                        'title' => $post->post_title,
                        'url' => $full_url
                    );
                }
            }
        }
        
        return $items;
    }
    
    private function get_full_permalink($id, $type) {
        // For taxonomies
        if (strpos($type, 'taxonomy_') === 0) {
            $taxonomy_name = str_replace('taxonomy_', '', $type);
            $term_link = get_term_link($id, $taxonomy_name);
            if (!is_wp_error($term_link)) {
                return $this->normalize_url($term_link);
            }
            return '';
        }
        
        // For Post/Page/Custom Post Type
        $permalink = get_permalink($id);
        if ($permalink) {
            return $this->normalize_url($permalink);
        }
        
        return '';
    }
    
    private function normalize_url($url) {
        // Remove site URL, get only path
        $site_url = trailingslashit(home_url());
        $path = str_replace($site_url, '', $url);
        
        // Remove leading and trailing slashes
        $path = trim($path, '/');
        
        // If empty, it's homepage
        if (empty($path)) {
            return 'homepage';
        }
        
        return $path;
    }
    
    private function get_conflicts_for_display() {
        global $wpdb;
        
        $conflicts = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY scan_date DESC", ARRAY_A);
        
        return $conflicts;
    }
    
    private function get_conflict_stats() {
        global $wpdb;
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        $pending = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'pending'");
        $resolved = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'resolved'");
        
        return array(
            'total' => intval($total),
            'pending' => intval($pending),
            'resolved' => intval($resolved)
        );
    }
    
    public function ajax_get_conflict_details() {
        check_ajax_referer('url_conflict_detector_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Yetkiniz yok.');
        }
        
        $conflict_id = intval($_POST['conflict_id']);
        
        global $wpdb;
        $conflict = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $conflict_id), ARRAY_A);
        
        if (!$conflict) {
            wp_send_json_error(__('Conflict not found.', 'url-conflict-detector'));
        }
        
        wp_send_json_success($conflict);
    }
    
    public function ajax_fix_conflict() {
        check_ajax_referer('url_conflict_detector_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission.', 'url-conflict-detector'));
        }
        
        $conflict_id = intval($_POST['conflict_id']);
        $item_type = sanitize_text_field($_POST['item_type']);
        $item_id = intval($_POST['item_id']);
        $new_slug = sanitize_title($_POST['new_slug']);
        
        global $wpdb;
        
        // Update slug
        $result = false;
        
        // Taxonomy check
        if (strpos($item_type, 'taxonomy_') === 0) {
            $taxonomy_name = str_replace('taxonomy_', '', $item_type);
            $result = wp_update_term($item_id, $taxonomy_name, array('slug' => $new_slug));
        }
        // Media check
        elseif ($item_type === 'media') {
            $result = wp_update_post(array(
                'ID' => $item_id,
                'post_name' => $new_slug
            ));
        }
        // Post Type
        else {
            $result = wp_update_post(array(
                'ID' => $item_id,
                'post_name' => $new_slug
            ));
        }
        
        if ($result && !is_wp_error($result)) {
            // Mark conflict as resolved
            $wpdb->update(
                $this->table_name,
                array('status' => 'resolved'),
                array('id' => $conflict_id),
                array('%s'),
                array('%d')
            );
            
            wp_send_json_success(__('Slug successfully updated.', 'url-conflict-detector'));
        } else {
            $error_message = is_wp_error($result) ? $result->get_error_message() : __('An error occurred while updating slug.', 'url-conflict-detector');
            wp_send_json_error($error_message);
        }
    }
}

new URL_Conflict_Detector();