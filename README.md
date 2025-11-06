# URL Conflict Detector

A WordPress plugin that detects and resolves URL conflicts between different content types including posts, pages, products, media, categories, tags, and custom taxonomies.

## Description

URL Conflict Detector helps WordPress site administrators identify and fix URL conflicts that can cause SEO issues, broken links, and confusion. The plugin scans your entire site for duplicate URLs across different content types and provides an easy-to-use interface for resolving conflicts.

## Features

- 🔍 **Comprehensive Scanning**: Scan all content types including:
  - Posts & Pages
  - Products (WooCommerce)
  - Media attachments
  - Categories & Tags
  - Product Categories & Product Tags
  - Custom Post Types
  - Custom Taxonomies

- 🎯 **Flexible Selection**: Choose which content types to scan
- 📊 **Visual Dashboard**: Clear statistics showing total, pending, and resolved conflicts
- ✏️ **Easy Resolution**: Fix conflicts directly from the admin interface
- 🔄 **Conflict Tracking**: Monitor which conflicts have been resolved
- 🌐 **Multilingual**: Supports English and German (translatable to any language)

## Installation

### From WordPress Admin

1. Download the plugin ZIP file
2. Go to **Plugins > Add New** in your WordPress admin
3. Click **Upload Plugin** and select the ZIP file
4. Click **Install Now**
5. Activate the plugin

### Manual Installation

1. Upload the `url-conflict-detector` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress

### From GitHub

```bash
cd wp-content/plugins
git clone https://github.com/e-caliskan/url-conflict-detector-for-wordpress.git
```

Then activate the plugin from the WordPress admin panel.

## Usage

### Accessing the Plugin

After activation, find **URL Conflicts** in the WordPress admin menu (look for the warning icon ⚠️).

### Scanning for Conflicts

1. Navigate to **URL Conflicts** in the WordPress admin menu
2. Select the content types you want to scan:
   - Use the checkboxes to select individual types
   - Use "All Content Types" or "All Taxonomies" to select groups
3. Click **Scan for Conflicts** button
4. Wait for the scan to complete

### Viewing Results

The plugin displays:
- **Total Conflicts**: Total number of URL conflicts found
- **Pending**: Conflicts that haven't been resolved yet
- **Resolved**: Conflicts that have been fixed

Each conflict shows:
- The conflicting URL
- Both items sharing the same URL
- Their content types
- Current status

### Fixing Conflicts

1. Click the **Fix** button next to a conflict
2. A modal window will open showing both items
3. Enter a new slug for one of the items
4. Click **Update This Item**
5. The conflict will be marked as resolved

## Screenshots

1. **Main Dashboard** - Overview of scan options and statistics
2. **Conflict List** - Detailed view of detected conflicts
3. **Fix Modal** - Interface for resolving individual conflicts

## System Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher

## Technical Details

### Database Table

The plugin creates a custom table `wp_url_conflicts` to store conflict data:
- Conflict URL
- Both conflicting items (type, ID, title)
- Status (pending/resolved)
- Scan date

### Uninstall

When you delete the plugin:
- The custom database table is automatically removed
- All plugin data is cleaned up
- No traces are left in your database

## Frequently Asked Questions

**Q: Will this plugin slow down my site?**
A: No. The plugin only runs when you explicitly start a scan from the admin panel. It has no impact on your frontend performance.

**Q: Can I scan only specific content types?**
A: Yes! You can select exactly which content types and taxonomies to scan.

**Q: What happens to resolved conflicts?**
A: They remain in the database for your records but are clearly marked as "Resolved" and no longer flagged as issues.

**Q: Does it work with WooCommerce?**
A: Yes! The plugin fully supports WooCommerce products, product categories, and product tags.

**Q: Does it work with custom post types?**
A: Yes! All public custom post types will be automatically detected and available for scanning.

**Q: Will it delete my content?**
A: No. The plugin only updates slugs (permalinks). It never deletes content.

**Q: Can I undo changes?**
A: The plugin doesn't have a built-in undo feature, but you can manually change the slug back through WordPress's standard editing interface.

## Support

For bug reports, feature requests, or support questions:
- Open an issue on [GitHub](https://github.com/e-caliskan/url-conflict-detector-for-wordpress/issues)




## Changelog

### 1.0.0
- Initial release
- Core conflict detection functionality
- Support for all WordPress content types
- Admin interface with statistics
- Conflict resolution system
- Multilingual support (English, German)

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024 Emrah ÇALIŞKAN

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## Credits

Developed by [Emrah ÇALIŞKAN](https://emrahcaliskan.tr)

---

**Note**: This plugin is provided as-is. Always backup your database before making changes to your site's content structure.
