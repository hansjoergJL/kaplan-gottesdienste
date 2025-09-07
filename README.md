# KaPlan Gottesdienste WordPress Plugin

A WordPress plugin that displays current church services and community events from the KaPlan church management system.

## Description

**KaPlan Gottesdienste** seamlessly integrates your KaPlan church management system with your WordPress website, automatically displaying up-to-date service schedules, events, and community activities. This plugin fetches data directly from your KaPlan server via API and presents it in a beautifully formatted, user-friendly display.

### Key Features

- **Real-time Data Sync**: Automatically fetches current church services and events from KaPlan API
- **Flexible Display Modes**: 
  - Chronological listing (default)
  - Church-grouped organization
  - Community events display
- **German Localization**: Native German date/time formatting and language support
- **Customizable Output**: Multiple formatting options and templates
- **Service Management**: Displays cancelled services with clear visual indicators
- **Event Registration**: Supports registration links for community events
- **Secure Connections**: Supports both HTTP and HTTPS API connections
- **Mobile Responsive**: Clean HTML output that works on all devices

## Installation

### Method 1: WordPress Admin Upload (Recommended)

1. Download the plugin as a ZIP file
2. In your WordPress admin, go to **Plugins → Add New → Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Activate the plugin after installation

### Method 2: Manual FTP/SFTP Upload

1. Upload the `kaplan-gottesdienste` folder to `/wp-content/plugins/`
2. Go to **Plugins** in your WordPress admin
3. Find "KaPlan Gottesdienste" and click **Activate**

### Requirements

- **WordPress**: 4.0 or higher
- **PHP**: 5.6 or higher (7.4+ recommended)
- **KaPlan Server**: Active KaPlan installation with API access
- **Internet Connection**: Required for API calls to KaPlan server

## Usage

### Basic Shortcode

Add church services to any post or page using the shortcode:

```php
[ausgabe_kaplan server="your-kaplan-server.com" arbeitsgruppe="your-group" code="your-code"]
```

### Complete Parameter Example

```php
[ausgabe_kaplan 
    server="kaplan.example.com" 
    arbeitsgruppe="gemeinde" 
    code="abc123" 
    mode="B" 
    options="LU" 
    secure="1" 
    leitung="VN" 
    template="1" 
    days="30"
]
```

### Shortcode Parameters

| Parameter | Default | Description | Options |
|-----------|---------|-------------|---------|
| `server` | *required* | Your KaPlan server domain | e.g., `kaplan.example.com` |
| `arbeitsgruppe` | *required* | KaPlan work group identifier | Your group name |
| `code` | *required* | KaPlan API access code | Your access code |
| `mode` | `B` | Display mode | `A` = Church grouped<br>`B` = Chronological<br>`VT` = Events<br>`GT` = Community events |
| `options` | `false` | Display options | `L` = Show leadership<br>`U` = Show "Uhr" with times<br>`E` = Show day descriptions<br>`V-` = Hide venue |
| `secure` | `1` | Connection type | `1` = HTTPS<br>`0` = HTTP |
| `leitung` | `false` | Leadership display format | `K` = Abbreviation<br>`N` = Last name<br>`VN` = First + Last<br>`V.N` = Initial + Last<br>`TN` = Title + Last<br>`TVN` = Title + First + Last<br>`TV.N` = Title + Initial + Last<br>`O` = Organization |
| `template` | `1` | Output template | `1` = Standard format |
| `days` | `false` | Number of days to display | Any positive integer |

### Examples

**Basic church services:**
```php
[ausgabe_kaplan server="kaplan.example.com" arbeitsgruppe="gemeinde" code="abc123"]
```

**Events with leadership and registration:**
```php
[ausgabe_kaplan server="kaplan.example.com" arbeitsgruppe="events" code="def456" mode="VT" leitung="TVN" options="LU"]
```

**Community events for next 14 days:**
```php
[ausgabe_kaplan server="kaplan.example.com" arbeitsgruppe="community" code="ghi789" mode="GT" days="14"]
```

## Automatic Updates (Easiest Method)

To enable automatic updates within the WordPress admin, add this plugin to a GitHub repository and use the **GitHub Updater** plugin:

### Step 1: Install GitHub Updater

1. Install the [GitHub Updater](https://github.com/afragen/github-updater) plugin
2. Activate GitHub Updater

### Step 2: Add Update Headers

Add these headers to the plugin file (`kaplan_gottesdienste.php`) after the existing plugin headers:

```php
/**
 * GitHub Plugin URI: your-username/kaplan-gottesdienste
 * GitHub Branch: main
 * Requires PHP: 5.6
 * Requires WP: 4.0
 */
```

### Step 3: Enable Updates

1. Push your plugin to a GitHub repository
2. The plugin will now appear in **Dashboard → Updates** when new versions are available
3. Updates can be installed with one click from the WordPress admin

### Alternative: WordPress.org Repository

For the most seamless updates, consider submitting the plugin to the WordPress.org repository:

1. Review [Plugin Developer Handbook](https://developer.wordpress.org/plugins/)
2. Submit for review at [WordPress.org Plugin Directory](https://wordpress.org/plugins/developers/)
3. Once approved, updates will be automatic for all users

## Technical Documentation

### Plugin Architecture

- **Main Class**: `kaplan_kalender` - Handles API communication and HTML generation
- **Date Class**: `DateTime_german` - Provides German localization for dates
- **Shortcode**: `ausgabe_kaplan` - WordPress integration point

### Key Methods

- `get_url($atts)` - Constructs KaPlan API URL with parameters
- `get_html($atts)` - Fetches data and generates HTML output
- `format_leitung()` - Formats leadership names according to specifications
- `handle_link()` - Processes HTTP links in event descriptions

### Hooks and Filters

Currently, the plugin doesn't expose WordPress hooks. Consider adding:

```php
// Future enhancement ideas
$html = apply_filters('kaplan_before_output', $html, $data);
do_action('kaplan_data_received', $data);
```

### Custom CSS Classes

The plugin outputs HTML with these CSS classes for styling:

- `.kaplan-export` - Main container
- `.kalender` - Definition list container
- `dt` - Date headers
- `dd` - Event content

### Development Setup

1. Clone the repository
2. Ensure PHP 5.6+ is available
3. Set up a local WordPress installation
4. Configure KaPlan API access for testing

## Changelog

### Version 1.6.1 (2024-04-26)
- **Added**: Community events support (mode=GT)
- **Author**: Hans-Joerg Joedike

### Version 1.6.0 (2024-04-18)
- **Added**: Leadership display functionality (Leitung parameter)
- **Enhanced**: Celebrant information display
- **Author**: Hans-Joerg Joedike

### Version 1.5.1 (2023-04-20)
- **Added**: TE_Zusatz2 field integration
- **Improved**: Additional event information display
- **Author**: Hans-Joerg Joedike

### Version 1.5.0 (2023-04-09)
- **Enhanced**: Cancelled service display with church name
- **Improved**: Service cancellation notifications
- **Author**: Hans-Joerg Joedike

### Version 1.4.0 (2022-03-29)
- **Added**: WordPress 5.9.2 compatibility
- **Fixed**: Compatibility issues with latest WordPress version
- **Author**: Hans-Joerg Joedike

### Version 1.3.x (2021-02-05)
- **Added**: HTTP link processing in additional fields
- **Enhanced**: Automatic conversion of URLs to "Internet" links
- **Author**: Hans-Joerg Joedike

### Version 1.0.0 (2021)
- **Initial**: First release
- **Added**: Basic KaPlan integration
- **Added**: Church service display functionality
- **Author**: Peter Hellerhoff

## Support

### Troubleshooting

**Plugin not displaying data:**
1. Verify KaPlan server is accessible
2. Check API credentials (arbeitsgruppe, code)
3. Ensure firewall allows outbound connections
4. Test API URL manually in browser

**Styling issues:**
1. Add custom CSS to your theme
2. Use browser developer tools to identify CSS conflicts
3. Ensure theme supports the plugin's HTML structure

**Update issues:**
1. Verify GitHub repository is public (if using GitHub Updater)
2. Check plugin headers are correctly formatted
3. Ensure GitHub Updater plugin is active

### Getting Help

- **Plugin Authors**: Peter Hellerhoff & Hans-Joerg Joedike
- **Website**: [https://www.kaplan-software.de](https://www.kaplan-software.de)
- **KaPlan Software**: Contact for API access and server configuration

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards

- Follow WordPress PHP Coding Standards
- Use meaningful variable names
- Comment complex logic
- Test with multiple WordPress versions
- Ensure German localization compatibility

---

**Made with ❤️ for the church community**
