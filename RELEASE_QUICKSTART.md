# 🚀 KaPlan Plugin - Quick Release Reference

## 📋 One-Minute Release Process

### 1. Update Version (30 seconds)
Edit `kaplan_gottesdienste.php`:
```php
* Version: 1.8.0                           ← Change this
define('KAPLAN_PLUGIN_VERSION', '1.8.0');  ← Change this
```

### 2. Run Release Script (30 seconds)
```bash
./release.sh "Brief description of changes"
```

**Done!** 🎉 WordPress users will get updates automatically.

---

## 🔧 Script Features

The `release.sh` script automatically:

✅ **Reads version** from plugin file  
✅ **Validates** both version locations match  
✅ **Checks** PHP syntax for errors  
✅ **Creates** git commit and tag  
✅ **Pushes** to GitHub repository  
✅ **Triggers** automated release workflow  

## 🎯 Usage Examples

```bash
# Interactive mode (prompts for commit message)
./release.sh

# With custom commit message
./release.sh "Add support for community events"

# Show help
./release.sh --help
```

## 📋 What Happens After Release

1. **GitHub Actions** (2-3 minutes):
   - Creates ZIP file
   - Publishes GitHub release
   - Generates release notes

2. **WordPress Updates** (within 12 hours):
   - Users see update notification
   - One-click update available
   - Automatic plugin update

## 🔍 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| Script can't find version | Check both version locations in plugin file |
| Tag already exists | Update version number or delete existing tag |
| PHP syntax error | Fix syntax errors in plugin files |
| Git not initialized | Run `git init` and set up repository |

## 📚 Full Documentation

- **Complete Guide**: `HowToRelease.md`
- **Setup Instructions**: `UPDATER_SETUP.md`  
- **Implementation Details**: `IMPLEMENTATION_COMPLETE.md`

---

**✨ Professional WordPress plugin updates made simple!**
