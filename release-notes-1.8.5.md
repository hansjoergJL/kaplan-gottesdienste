# KaPlan Plugin v1.8.5 Release Notes

**Release Date**: September 21, 2025  
**Type**: Critical Bug Fix

## 🔧 Critical Fix: Smart Quotes Normalization

### What Was Fixed
- **Smart Quotes Bug**: Plugin was not displaying events when shortcode attributes contained "smart quotes" (e.g., `days="14"`) instead of regular quotes (`days="14"`)
- **PHP Syntax Error**: Fixed invalid attribute syntax `#["ReturnTypeWillChange"]` → `#[ReturnTypeWillChange]`

### Technical Details
The WordPress visual editor often converts regular quotes to Unicode smart quotes automatically. This caused shortcode attributes like `days="14"` to become `days="14"` which corrupted API URLs, resulting in empty responses from the KaPlan server.

### Solution Implemented
Added robust normalization in the `kaplan_kalender()` function:
- Converts all smart quote variants to regular quotes
- Sanitizes numeric fields (`days`, `template`) to contain only digits
- Trims surrounding whitespace and quotes
- Prevents malformed API URLs

### Before vs After

**Before v1.8.5:**
```
[ausgabe_kaplan days="14" ...] 
→ API URL: days="\u201c14\u201d" 
→ Result: Empty table, no events
```

**After v1.8.5:**
```
[ausgabe_kaplan days="14" ...] 
→ API URL: days=14 
→ Result: ✅ Events displayed correctly
```

## 🧪 Testing
- Verified on local test environment with debug enabled
- API now returns proper JSON with event data (1394 bytes vs 6 bytes before)
- Template 3 layout displays correctly with German date formatting
- Registration links functional

## 📦 Compatibility
- **WordPress**: 2.7+
- **PHP**: 5.5+
- **Backward Compatible**: Existing shortcodes with regular quotes still work
- **Template Support**: All templates (1, 2, 3) fully functional

## 🔒 Security
- Improved input sanitization for numeric fields
- Better URL parameter validation
- Prevents injection of malformed API requests

## 🚀 Installation
1. Deactivate current KaPlan plugin
2. Upload `kaplan-gottesdienste-1.8.5.zip`
3. Activate the updated plugin
4. Smart quotes in existing shortcodes will be automatically normalized

---
**Important**: This is a critical fix that resolves data display issues. Update recommended for all installations.