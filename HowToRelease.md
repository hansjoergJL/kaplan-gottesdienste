# 🚀 KaPlan Plugin - Release Guide

This guide explains how to create new releases of the KaPlan Plugin with the built-in GitHub updater system.

## 📋 Quick Release Checklist

- [ ] Update version in plugin file
- [ ] Test plugin functionality
- [ ] Commit changes
- [ ] Run release script OR manual tag/push
- [ ] Verify GitHub release was created
- [ ] Test update on WordPress site

## 🎯 Two Ways to Release

### Option 1: Automated Script (Recommended)
```bash
./release.sh
```
The script will:
- Read version from plugin file
- Create appropriate git tag
- Push to GitHub
- Trigger automated release

### Option 2: Manual Process
1. Update version numbers
2. Commit changes
3. Create git tag manually
4. Push to GitHub

---

## 🔧 Option 1: Automated Release (Recommended)

### Step 1: Update Version
Edit `kaplan_gottesdienste.php` and update **both** version locations:

```php
/**
 * Version: 1.8.0    ← Update this
 */

// And this:
define('KAPLAN_PLUGIN_VERSION', '1.8.0');    ← Update this
```

### Step 2: Test Locally
- Test plugin functionality
- Ensure no PHP errors
- Verify shortcode works correctly

### Step 3: Run Release Script
```bash
# Make script executable (first time only)
chmod +x release.sh

# Run the release script
./release.sh
```

The script will:
1. ✅ Read version from your plugin file
2. ✅ Validate git status
3. ✅ Create commit with version message
4. ✅ Create git tag (e.g., `v1.8.0`)
5. ✅ Push to GitHub repository
6. ✅ Trigger GitHub Actions release workflow

### Step 4: Verify Release
1. **Check GitHub**: https://github.com/hansjoergJL/kaplan-gottesdienste/releases
2. **Verify ZIP file** was created by GitHub Actions
3. **Test update** on a WordPress site

---

## 🛠️ Option 2: Manual Release Process

### Step 1: Update Version Numbers

Edit `kaplan_gottesdienste.php`:

```php
/**
 * Plugin Name:  KaPlan Gottesdienste
 * Plugin URI: https://www.kaplan-software.de
 * Description: Anzeige aktueller Gottesdienste aus KaPlan
 * Version: 1.8.0    ← Change this
 * Author: Peter Hellerhoff & Hans-Joerg Joedike
 * Author URI: https://www.kaplan-software.de
 * License: GPL2 or newer
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  kaplan-import
 * GitHub Plugin URI: hansjoergJL/kaplan-gottesdienste
 * GitHub Branch: main
 * Requires PHP: 7.4
 * Requires WP: 4.0
 */

// Define plugin constants
define('KAPLAN_PLUGIN_VERSION', '1.8.0');    ← Change this
```

**Important**: Both version numbers must match exactly!

### Step 2: Test Changes
```bash
# Test plugin syntax
php -l kaplan_gottesdienste.php

# If available, test on local WordPress
wp plugin activate kaplan-gottesdienste
```

### Step 3: Commit Changes
```bash
git add .
git commit -m "Version 1.8.0 - Description of changes"
```

### Step 4: Create Release Tag
```bash
# Create tag (always prefix with 'v')
git tag v1.8.0

# Push commits and tags
git push origin main
git push origin --tags
```

### Step 5: GitHub Actions Automatic Process
GitHub will automatically:
1. ✅ Detect the new tag
2. ✅ Run the release workflow
3. ✅ Create plugin ZIP file
4. ✅ Publish GitHub release
5. ✅ Notify WordPress sites with updates

---

## 🔍 Version Numbering Guidelines

### Semantic Versioning (Recommended)
- **Major** (`1.0.0` → `2.0.0`): Breaking changes, major new features
- **Minor** (`1.7.0` → `1.8.0`): New features, backward compatible
- **Patch** (`1.7.0` → `1.7.1`): Bug fixes, small improvements

### Examples
```bash
# Bug fix release
v1.7.1 - Fix API connection timeout

# Feature release  
v1.8.0 - Add support for community events

# Major release
v2.0.0 - Complete UI redesign, new API
```

## 📊 Release Testing Process

### Before Release
1. **Local Testing**:
   ```bash
   # Check syntax
   php -l kaplan_gottesdienste.php
   php -l includes/class-kaplan-updater.php
   
   # Test on local WordPress if available
   wp plugin activate kaplan-gottesdienste
   wp plugin deactivate kaplan-gottesdienste
   ```

2. **Staging Environment** (if available):
   - Install current version
   - Test all shortcode parameters
   - Verify KaPlan API connectivity

### After Release
1. **GitHub Verification**:
   - Check release was created: https://github.com/hansjoergJL/kaplan-gottesdienste/releases
   - Download and test ZIP file
   - Verify GitHub Actions workflow completed successfully

2. **WordPress Update Testing**:
   - Install previous version on test site
   - Wait for update notification (or force check)
   - Update plugin and verify functionality

## 🚨 Troubleshooting Releases

### Common Issues

#### Release Not Created
**Problem**: Tagged version but no GitHub release appears

**Solutions**:
- Check GitHub Actions tab for workflow errors
- Verify tag format is `v1.8.0` (with 'v' prefix)
- Ensure `.github/workflows/release.yml` exists

#### WordPress Not Detecting Update
**Problem**: Update doesn't appear in WordPress admin

**Solutions**:
```bash
# Clear WordPress transients
wp transient delete --all

# Force update check
wp plugin update --all --dry-run

# Check plugin version detection
wp plugin get kaplan-gottesdienste --field=version
```

#### Update Installation Fails
**Problem**: Update downloads but installation fails

**Solutions**:
- Check WordPress file permissions
- Verify ZIP file is valid
- Check WordPress error logs
- Ensure sufficient disk space

### Debug Commands
```bash
# Check current git status
git status
git log --oneline -5

# View recent tags
git tag -l -n1 | tail -5

# Check GitHub Actions status (requires gh CLI)
gh workflow list
gh run list --limit 5
```

## 📈 Release History Tracking

### Changelog Best Practices

Always document changes in your git commits and GitHub releases:

```markdown
## Version 1.8.0 - 2024-XX-XX

### Added
- New feature X for better user experience
- Support for community events display

### Fixed
- Bug with timezone handling
- API connection timeout issues

### Changed
- Improved error messages
- Updated German translations

### Security
- Enhanced input validation
- Updated API authentication
```

### Git Commit Messages
```bash
# Good commit messages
git commit -m "Version 1.8.0 - Add community events support"
git commit -m "Fix: Resolve timezone display bug"
git commit -m "Security: Improve input validation"

# Less helpful
git commit -m "Update"
git commit -m "Fix bug"
```

## 🎯 Release Automation Tips

### Using the Release Script
The `release.sh` script provides:
- ✅ Automatic version detection
- ✅ Git status validation
- ✅ Consistent tagging format
- ✅ Error checking
- ✅ Confirmation prompts

### GitHub Actions Benefits
Our workflow automatically:
- ✅ Creates clean plugin ZIP
- ✅ Excludes development files
- ✅ Generates release notes
- ✅ Handles GitHub API authentication

## 📞 Getting Help

### Pre-Release Questions
- Is the version number in both locations?
- Did you test the plugin functionality?
- Are there any uncommitted changes?

### Post-Release Questions  
- Was the GitHub release created?
- Does the ZIP file work when manually installed?
- Are users receiving update notifications?

### Support Resources
- Plugin repository: https://github.com/hansjoergJL/kaplan-gottesdienste
- GitHub Actions docs: https://docs.github.com/en/actions
- WordPress Plugin API: https://developer.wordpress.org/plugins/

---

## 🎉 Success Indicators

After a successful release, you should see:

1. ✅ **GitHub Release**: New version listed on releases page
2. ✅ **ZIP File**: Download asset available in release
3. ✅ **WordPress Update**: Notification appears in admin after ~12 hours
4. ✅ **GitHub Actions**: Green checkmark on workflow run
5. ✅ **Version Detection**: WordPress shows correct new version

**You now have professional, automated plugin releases! 🚀**
