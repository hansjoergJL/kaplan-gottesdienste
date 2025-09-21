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
- **Multiple Templates**: 
  - Template="1": Traditional single-line format
  - Template="2": **NEW!** Two-column table layout with merged date headers
- **German Localization**: Native German date/time formatting and language support
- **Customizable Output**: Multiple formatting options and display templates
- **Service Management**: Displays cancelled services with clear visual indicators
- **Event Registration**: Supports registration links for community events
- **Secure Connections**: Supports both HTTP and HTTPS API connections
- **Mobile Responsive**: Clean HTML output that works on all devices
- **Automatic Updates**: Built-in GitHub release integration with one-click updates

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
    template="2"
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
| `template` | `1` | Output template | `1` = Standard single-line format<br>`2` = **NEW!** Two-column table layout |
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

**NEW! Two-column layout with Template="2":**
```php
[ausgabe_kaplan server="kaplan.example.com" arbeitsgruppe="gemeinde" code="abc123" template="2"]
```

**Template="2" with leadership and time formatting:**
```php
[ausgabe_kaplan server="kaplan.example.com" arbeitsgruppe="events" code="def456" template="2" leitung="TVN" options="LU"]
```

## Output Samples

### Template="1" (default)

```
Dienstag, 09. September 2025
  09.30  Themenschulung Level 2: Fortgeschrittene/ Aufbau/ Erweiterung (JKW Schulungsraum)
  It mail an Herr Vollmer vom 23.04.2025

Montag, 15. September 2025
  09.30  Morgengebet 
  11.30  Hl. Messe
```

### Template="2" (two-column layout)

```
| Dienstag, 09. September 2025                                    |
|----------|------------------------------------------------------|
| 09.30    | Hl. Messe                                            |
|          | unterstützt vom Kirchenchor                          |

| Montag, 15. September 2025                                      |
|----------|------------------------------------------------------|
| 09.30    | Morgengebet                                          |
| 11.30    | Hl. Messe                                            |
```

## Automatic Updates (Built-in)

### How It Works

1. **Automatic Detection**: The plugin checks for new releases on GitHub
2. **WordPress Integration**: Updates appear in your WordPress admin under **Plugins → Updates**
3. **One-Click Updates**: Install updates directly from WordPress admin
4. **Release Management**: Uses GitHub tags and releases for version control

### For Repository Maintainers

**Creating a New Release:**

1. **Update Version**: Increment version in plugin header (e.g., 1.8.0 → 1.8.1)
2. **Commit Changes**: `git commit -m "Version 1.8.1: Description of changes"`
3. **Create Tag**: `git tag -a v1.8.1 -m "Release v1.8.1"`
4. **Push Everything**: `git push origin main && git push origin v1.8.1`
5. **GitHub Release**: Create a release on GitHub using the tag

**Automatic Update Flow:**
- Users get notified of updates in their WordPress admin
- Updates include changelog from GitHub release notes
- Clean installation preserves settings and configuration

## Technical Documentation

### Plugin Architecture

- **Main Class**: `kaplan_kalender` - Handles API communication and HTML generation
- **Date Class**: `DateTime_german` - Provides German localization for dates
- **Shortcode**: `ausgabe_kaplan` - WordPress integration point

### Key Methods

- `get_url($atts)` - Constructs KaPlan API URL with parameters
- `get_html($atts)` - Fetches data and generates HTML output (supports both templates)
- `get_template2_css()` - **NEW!** Generates CSS styling for Template="2" table layout
- `format_leitung()` - Formats leadership names according to specifications
- `handle_link()` - Processes HTTP links in event descriptions

### Custom CSS Classes

The plugin outputs HTML with these CSS classes for styling:

**Template="1" (Definition List):**
- `.kaplan-export` - Main container
- `.kalender` - Definition list container
- `dt` - Date headers
- `dd` - Event content

**Template="2" (Table Layout):**
- `.kaplan-export` - Main container
- `.kaplan-table-layout` - Table container
- `.kaplan-date-header` - Date header rows (merged)
- `.kaplan-time-column` - Time column (left)
- `.kaplan-content-column` - Content column (right)
- `.kaplan-event-row` - Individual event rows
- `.kaplan-additional-info` - Additional event information

### Development Setup

1. Clone the repository
2. Ensure PHP 5.6+ is available
3. Set up a local WordPress installation
4. Configure KaPlan API access for testing

### Local Testing

**Local WordPress Installation Path:**
```
~/Local Sites/kaplan-plugin-test/app/public/wp-content/plugins/kaplan-gottesdienste/
```

To test locally, copy the plugin files to the above directory:
```bash
cp -r /path/to/plugin/* "~/Local Sites/kaplan-plugin-test/app/public/wp-content/plugins/kaplan-gottesdienste/"
```

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
1. Check if GitHub repository is accessible
2. Verify plugin can connect to GitHub API
3. Look for update notifications in WordPress admin under **Plugins**
4. Try "Check for Updates" link in plugin actions
5. Ensure server allows outbound HTTPS connections to api.github.com

### Getting Help

- **Plugin Authors**: Peter Hellerhoff & Hans-Joerg Joedike
- **Website**: [http://www.jlsoftware.de](http://www.jlsoftware.de/software/kaplan-gottesdienste)
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
