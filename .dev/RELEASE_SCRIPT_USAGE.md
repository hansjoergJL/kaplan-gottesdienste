# KaPlan Plugin Release Script Documentation

## Enhanced create-release.sh v2.0.0

The `create-release.sh` script has been enhanced to provide a **complete one-step release process** that automatically handles GitHub releases and ZIP file uploads.

## What's New in v2.0.0

✅ **Automatic GitHub Release Creation** - Creates releases via GitHub CLI  
✅ **ZIP File Upload** - Automatically attaches plugin ZIP to release  
✅ **Release Notes Integration** - Uses existing `release-notes-X.Y.Z.md` files  
✅ **Authentication Check** - Verifies GitHub CLI authentication  
✅ **Help Documentation** - Built-in `--help` flag  
✅ **Error Handling** - Graceful fallback to manual steps  

## Usage

### Interactive Mode
```bash
./create-release.sh
```
The script will prompt for version and title.

### Direct Mode
```bash
./create-release.sh 1.8.6 "Bug fixes and improvements"
```

### Help
```bash
./create-release.sh --help
```

## What the Script Does

1. **Updates Plugin Version** - Modifies `kaplan_gottesdienste.php` with new version
2. **Git Operations** - Creates commit and pushes tag to GitHub  
3. **Creates ZIP File** - Packages plugin for distribution
4. **GitHub Release** - Automatically creates release page (if GitHub CLI available)
5. **Uploads ZIP** - Attaches distribution file to GitHub release

## Requirements

### Required
- Git repository with remote origin
- Write access to the repository
- Proper file structure (kaplan_gottesdienste.php in current directory)

### Optional (for full automation)
- **GitHub CLI (`gh`)** - For automatic release creation
- **GitHub CLI Authentication** - Run `gh auth login` first
- **Release Notes File** - `release-notes-X.Y.Z.md` for detailed notes

## Output Examples

### With GitHub CLI (Full Automation)
```
Creating release for version 1.8.6: Bug fixes and improvements
Updating version in plugin file...
Committing version update...
Creating and pushing tag v1.8.6...
Creating ZIP file...
Checking for GitHub CLI...
📱 GitHub CLI found - checking authentication...
✅ GitHub CLI authenticated - creating release automatically...
📋 Using release notes from: release-notes-1.8.6.md
🚀 Creating GitHub release...
✅ GitHub release created successfully!
📦 Uploading ZIP file to release...
✅ ZIP file uploaded successfully!

🎉 RELEASE COMPLETED SUCCESSFULLY!

📦 ZIP file created: kaplan-gottesdienste-1.8.6.zip
🏷️  Tag created and pushed: v1.8.6
🌐 GitHub release: https://github.com/hansjoergJL/kaplan-gottesdienste/releases/tag/v1.8.6

🔄 WordPress sites will receive update notifications within 12 hours!
```

### Without GitHub CLI (Manual Steps)
```
Creating release for version 1.8.6: Bug fixes and improvements
Updating version in plugin file...
Committing version update...
Creating and pushing tag v1.8.6...
Creating ZIP file...
Checking for GitHub CLI...
⚠️  GitHub CLI not found. Showing manual steps...

✅ Release preparation complete!

📦 ZIP file created: kaplan-gottesdienste-1.8.6.zip
🏷️  Tag created: v1.8.6

🌐 Manual steps (GitHub CLI not available):
1. Go to: https://github.com/hansjoergJL/kaplan-gottesdienste/releases
2. Click 'Create a new release'
3. Select tag: v1.8.6
4. Title: KaPlan Gottesdienste v1.8.6
5. Upload the ZIP file: kaplan-gottesdienste-1.8.6.zip
6. Add release notes describing the changes
7. Click 'Publish release'

🔄 After publishing, WordPress sites will receive update notifications!
```

## Error Handling

The script handles various scenarios gracefully:

- **Missing GitHub CLI** → Falls back to manual steps
- **GitHub CLI not authenticated** → Prompts for `gh auth login`  
- **Release creation fails** → Shows manual instructions
- **ZIP upload fails** → Provides direct link for manual upload

## File Integration

### Release Notes
If a file named `release-notes-X.Y.Z.md` exists (matching the version), the script will automatically use it for the GitHub release description.

### Version Management  
The script updates both places where version is stored:
- Plugin header: `* Version: X.Y.Z`
- PHP constant: `define('KAPLAN_PLUGIN_VERSION', 'X.Y.Z');`

## Best Practices

1. **Test Locally First** - Ensure plugin works before releasing
2. **Prepare Release Notes** - Create `release-notes-X.Y.Z.md` for detailed documentation  
3. **Semantic Versioning** - Use proper version numbers (X.Y.Z)
4. **Clear Commit Messages** - The script creates meaningful commit messages
5. **Backup Before Release** - Git handles versioning, but good practice

---

**Enhanced by**: Integration of GitHub CLI automation  
**Version**: 2.0.0  
**Date**: September 21, 2025