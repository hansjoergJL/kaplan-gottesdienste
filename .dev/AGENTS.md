# KaPlan Plugin - WordPress Development

> **For AI Agents**: This file contains critical project information for consistent development workflow

## WordPress Installation Location
**Local WordPress Installation:** `~/Local Sites/kaplan-plugin-test/app/public/`

### Key Paths:
- WordPress Root: `~/Local Sites/kaplan-plugin-test/app/public/`
- wp-config.php: `~/Local Sites/kaplan-plugin-test/app/public/wp-config.php`
- Debug Log: `~/Local Sites/kaplan-plugin-test/app/public/wp-content/debug.log`
- Plugins Directory: `~/Local Sites/kaplan-plugin-test/app/public/wp-content/plugins/`

## Development Notes
- This is a Local by Flywheel development environment
- Plugin being developed: KaPlan Plugin
- Plugin source location: `/Users/hans-jorgjodike/Development/php/WordPress/KaPlan Plugin`

## Remember
Always use the Local Sites path when working with WordPress configuration files and debugging.

## Plugin Fix History
### 2025-09-21: Fixed PHP Syntax Error
- **File**: `kaplan_gottesdienste.php`
- **Line**: 657
- **Error**: Invalid PHP attribute syntax `#["ReturnTypeWillChange"]`
- **Fix**: Changed to proper syntax `#[ReturnTypeWillChange]` (removed quotes)
- **Status**: ✅ Fixed in both local project and WordPress installation

### 2025-09-21: Fixed Smart Quotes Bug
- **File**: `kaplan_gottesdienste.php`
- **Lines**: 687-705
- **Error**: Smart quotes in shortcode attributes (e.g., days="14") broke API URLs
- **Fix**: Added normalization to convert smart quotes to regular quotes and sanitize numeric fields
- **Result**: Plugin now displays event data correctly on test site
- **Status**: ✅ Fixed in both local project and WordPress installation

## Team Collaboration Notes
- **Canonical Documentation**: `/Users/hans-jorgjodike/Library/Mobile Documents/iCloud~md~obsidian/Documents/Joedike/Development/README (Mac)/KaPlan-Plugin_README.md`
- **Local Development**: This directory (`/Users/hans-jorgjodike/Development/php/WordPress/KaPlan Plugin`)
- **WordPress Installation**: `~/Local Sites/kaplan-plugin-test/app/public/wp-content/plugins/kaplan-gottesdienste/`
