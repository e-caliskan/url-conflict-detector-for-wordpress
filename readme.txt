=== URL Conflict Detector ===
Contributors: e-caliskan
Tags: url, conflict, seo, permalink, duplicate
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Detects and resolves URL conflicts between products, media, categories, tags, pages, and posts in WordPress.

== Description ==

URL Conflict Detector helps WordPress site administrators identify and fix URL conflicts that can cause SEO issues, broken links, and confusion. The plugin scans your entire site for duplicate URLs across different content types and provides an easy-to-use interface for resolving conflicts.

= Features =

* **Comprehensive Scanning**: Scan all content types including posts, pages, products, media, categories, tags, and custom taxonomies
* **Flexible Selection**: Choose which content types to scan
* **Visual Dashboard**: Clear statistics showing total, pending, and resolved conflicts
* **Easy Resolution**: Fix conflicts directly from the admin interface
* **Conflict Tracking**: Monitor which conflicts have been resolved
* **Multilingual**: Supports English and German (translatable to any language)
* **WooCommerce Compatible**: Full support for WooCommerce products and taxonomies
* **Custom Post Types**: Automatically detects and scans custom post types

= Use Cases =

* Identify duplicate URLs causing SEO problems
* Fix permalink conflicts before they affect rankings
* Clean up URL structure after importing content
* Resolve conflicts between different content types
* Maintain clean URL architecture

= How It Works =

1. Navigate to **URL Conflicts** in your WordPress admin menu
2. Select the content types you want to scan
3. Click **Scan for Conflicts**
4. Review detected conflicts in a clear, organized table
5. Fix conflicts by updating slugs with one click

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins > Add New
3. Search for "URL Conflict Detector"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to Plugins > Add New > Upload Plugin
4. Choose the ZIP file and click "Install Now"
5. Activate the plugin

= After Activation =

1. Find **URL Conflicts** in the WordPress admin menu (with a warning icon)
2. Select content types to scan
3. Start scanning for conflicts

== Frequently Asked Questions ==

= Will this plugin slow down my site? =

No. The plugin only runs when you explicitly start a scan from the admin panel. It has no impact on your frontend performance.

= Can I scan only specific content types? =

Yes! You can select exactly which content types and taxonomies to scan.

= What happens to resolved conflicts? =

They remain in the database for your records but are clearly marked as "Resolved" and no longer flagged as issues.

= Does it work with WooCommerce? =

Yes! The plugin fully supports WooCommerce products, product categories, and product tags.

= Does it work with custom post types? =

Yes! All public custom post types will be automatically detected and available for scanning.

= Will it delete my content? =

No. The plugin only updates slugs (permalinks). It never deletes content.

= Can I undo changes? =

The plugin doesn't have a built-in undo feature, but you can manually change the slug back through WordPress's standard editing interface.

= What happens when I uninstall the plugin? =

All plugin data, including the custom database table, is automatically removed. No traces are left in your database.

== Screenshots ==

1. Main dashboard showing scan options and content type selection
2. Conflict detection results with statistics
3. Detailed conflict list with type indicators
4. Fix modal for resolving individual conflicts

== Changelog ==

= 1.0.0 =
* Initial release
* Core conflict detection functionality
* Support for all WordPress content types
* Admin interface with statistics
* Conflict resolution system
* Multilingual support (English, German)
* WooCommerce compatibility
* Custom post type support

== Upgrade Notice ==

= 1.0.0 =
Initial release of URL Conflict Detector.

== Additional Information ==

= Support =

For support, feature requests, or bug reports, please visit our [GitHub repository](https://github.com/e-caliskan/url-conflict-detector-for-wordpress) or the [WordPress.org support forum](https://wordpress.org/support/plugin/url-conflict-detector/).

= Contributing =

We welcome contributions! Visit our [GitHub repository](https://github.com/e-caliskan/url-conflict-detector-for-wordpress) to contribute.

= Privacy =

This plugin does not collect, store, or transmit any user data outside your WordPress installation. All data is stored locally in your WordPress database.
