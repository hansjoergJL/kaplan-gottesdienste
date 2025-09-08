# KaPlan Plugin - Custom GitHub Updater Setup

This document explains how to set up and use the built-in GitHub updater for the KaPlan Plugin. **No additional plugins are required for users** - updates will appear automatically in the standard WordPress admin interface.

## ✅ What's Already Done

The updater system is already integrated into your plugin:

1. **Custom Updater Class**: `includes/class-kaplan-updater.php`
2. **Main Plugin Integration**: Modified `kaplan_gottesdienste.php`
3. **GitHub Repository Configuration**: Set to `hansjoergJL/kaplan-gottesdienste`

## 🚀 Quick Start

### For Plugin Users (WordPress Administrators)

**Nothing to install!** Updates will appear automatically in:
- **Dashboard → Updates** (when available)
- **Plugins → Installed Plugins** (update notification)

### For Plugin Developers (You)

1. **Create GitHub Repository** (if not done already)
2. **Create Releases** to trigger updates
3. **Test the Update Process**

## 📋 Detailed Setup Instructions

### Step 1: GitHub Repository Setup

1. **Create/Verify Repository**:
   ```bash
   # If repository doesn't exist yet:
   git init
   git add .
   git commit -m "Initial KaPlan Plugin version 1.7.0"
   git remote add origin https://github.com/hansjoergJL/kaplan-gottesdienste.git
   git push -u origin main
   ```

2. **Repository Structure** should look like:
   ```
   kaplan-gottesdienste/
   ├── kaplan_gottesdienste.php
   ├── includes/
   │   └── class-kaplan-updater.php
   ├── README.md
   └── UPDATER_SETUP.md
   ```

### Step 2: Creating Releases

#### Manual Release Creation

1. **Go to GitHub Repository**: https://github.com/hansjoergJL/kaplan-gottesdienste
2. **Click "Releases"** → **"Create a new release"**
3. **Tag Version**: Use format `v1.8.0` (always prefix with 'v')
4. **Release Title**: `Version 1.8.0 - Brief Description`
5. **Description**: Write changelog in markdown:
   ```markdown
   ## Changes in v1.8.0
   
   ### Added
   * New feature X
   * Improved Y functionality
   
   ### Fixed
   * Bug Z resolved
   * Performance improvements
   
   ### Changed
   * Updated API integration
   ```

6. **Upload Assets** (Optional): Attach a ZIP file of the plugin
7. **Publish Release**

#### Automated Release (Recommended)

Create `.github/workflows/release.yml` for automated releases:

```yaml
name: Create Release

on:
  push:
    tags:
      - 'v*'

jobs:
  release:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Create Plugin ZIP
      run: |
        zip -r kaplan-gottesdienste.zip . \
          -x "*.git*" "*.github*" "*node_modules*" "*.md" "*.yml" "*.yaml"
    
    - name: Create Release
      uses: softprops/action-gh-release@v1
      with:
        files: kaplan-gottesdienste.zip
        generate_release_notes: true
      env:
        GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
```

### Step 3: Version Management

#### Update Version Numbers

When preparing a new release, update version in **both** places:

1. **Main Plugin File** (`kaplan_gottesdienste.php`):
   ```php
   * Version: 1.8.0
   ```

2. **Plugin Constants** (same file):
   ```php
   define('KAPLAN_PLUGIN_VERSION', '1.8.0');
   ```

#### Version Numbering Best Practices

- **Major**: `1.0.0` → `2.0.0` (breaking changes)
- **Minor**: `1.7.0` → `1.8.0` (new features)
- **Patch**: `1.7.0` → `1.7.1` (bug fixes)

### Step 4: Testing Updates

#### Test Environment Setup

1. **Create Test WordPress Site**:
   ```bash
   # Using WP-CLI (recommended)
   wp core download
   wp core config --dbname=test_db --dbuser=root --dbpass=password
   wp core install --url=localhost --title="Test" --admin_user=admin --admin_password=password --admin_email=test@example.com
   ```

2. **Install Your Plugin**:
   - Upload current version (1.7.0)
   - Activate plugin
   - Verify it appears in Plugins list

3. **Create New Release**:
   - Update version to 1.8.0 in code
   - Commit and push changes
   - Create GitHub release with tag `v1.8.0`

4. **Test Update Process**:
   - In WordPress admin, go to **Dashboard → Updates**
   - Plugin should appear in "Plugins" section
   - Click "Update Now"
   - Verify update completes successfully

## 🔧 Advanced Configuration

### Private Repository Support

If your repository is private, add a GitHub token:

1. **Generate Token**: GitHub → Settings → Developer settings → Personal access tokens
2. **Add Token to Plugin**:
   ```php
   function kaplan_init_updater() {
       if (class_exists('KaPlan_GitHub_Updater')) {
           new KaPlan_GitHub_Updater(
               KAPLAN_PLUGIN_FILE,
               KAPLAN_PLUGIN_VERSION,
               KAPLAN_GITHUB_REPO,
               'ghp_your_token_here'  // Add this line
           );
       }
   }
   ```

### Custom Update Server (Alternative)

Instead of GitHub, you can host your own update server:

1. **Create Update Endpoint** (`update-server.php`):
   ```php
   <?php
   header('Content-Type: application/json');
   
   $latest_version = '1.8.0';
   $current_version = $_GET['version'] ?? '1.0.0';
   
   if (version_compare($current_version, $latest_version, '<')) {
       echo json_encode([
           'new_version' => $latest_version,
           'package' => 'https://your-server.com/kaplan-plugin.zip',
           'url' => 'https://your-website.com/kaplan-plugin',
           'tested' => '6.4',
           'requires' => '4.0',
           'requires_php' => '7.4'
       ]);
   } else {
       http_response_code(204); // No update available
   }
   ?>
   ```

2. **Modify Updater Class** to use custom URL instead of GitHub API.

## 🐛 Troubleshooting

### Common Issues

1. **Updates Not Appearing**:
   - Check GitHub repository is public
   - Verify release was created properly
   - Clear WordPress transients: `wp transient delete --all`

2. **Update Fails**:
   - Check file permissions in `/wp-content/plugins/`
   - Verify ZIP file in release is valid
   - Check WordPress error logs

3. **Version Not Detected**:
   - Ensure tag format is `v1.8.0` (with 'v' prefix)
   - Verify version in plugin header matches tag

### Debug Mode

Enable debug logging by adding to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check logs in `/wp-content/debug.log` for updater messages.

### Force Update Check

Add this to your theme's `functions.php` temporarily:
```php
add_action('admin_init', function() {
    if (isset($_GET['force_kaplan_update'])) {
        delete_site_transient('update_plugins');
        wp_update_plugins();
        wp_die('Update check forced!');
    }
});
```

Visit: `yoursite.com/wp-admin/?force_kaplan_update=1`

## 📊 Monitoring Updates

### User Analytics

Track update adoption by adding to the updater class:
```php
// Add to get_remote_version_info() method
wp_remote_post('https://your-analytics.com/track', [
    'body' => [
        'plugin' => 'kaplan',
        'version' => $this->version,
        'site' => home_url()
    ]
]);
```

### Update Notifications

Get notified when users update:
```php
// Add to after_update() method
wp_remote_post('https://your-webhook.com/updated', [
    'body' => [
        'plugin' => 'kaplan',
        'from_version' => $this->version,
        'to_version' => $new_version,
        'site' => home_url()
    ]
]);
```

## ✅ Checklist for Releases

- [ ] Update version in plugin header
- [ ] Update version constant
- [ ] Test functionality locally
- [ ] Commit and push changes
- [ ] Create GitHub release with proper tag
- [ ] Write meaningful changelog
- [ ] Test update process on staging site
- [ ] Monitor for user issues

## 📞 Support

If users experience update issues:

1. **Check Requirements**:
   - WordPress 4.0+
   - PHP 7.4+
   - Active internet connection

2. **Manual Update Process**:
   - Download latest release ZIP
   - Deactivate plugin
   - Delete old plugin folder
   - Upload new version
   - Reactivate plugin

3. **Contact Information**:
   - Website: https://www.kaplan-software.de
   - Plugin Issues: GitHub repository issues section

---

**🎉 Your plugin now has professional, automatic updates without requiring users to install any additional plugins!**
