# KaPlan Plugin - Smart Quotes Bug Fix

## Issue Summary
The KaPlan plugin was not displaying data when shortcode attributes contained "smart quotes" (typographic quotes like `"14"`) instead of regular ASCII quotes (`"14"`).

## Root Cause
WordPress visual editors often convert regular quotes to smart quotes automatically. When users copy-paste shortcodes or type them, attributes like `days="14"` become `days="14"` with Unicode smart quotes (U+201C, U+201D).

These smart quotes were passed directly to the API URL construction, resulting in malformed URLs like:
```
https://web.kaplanhosting.de/get.asp?...&days="14"
```

The API server couldn't parse the Unicode quotes, returning empty responses.

## Symptoms
- Plugin shortcode rendered empty tables
- Debug output showed `Response length: 6`, `Records found: 0`
- API URLs contained escaped Unicode characters: `days=\\u201c14\\u201d`

## Solution
Added smart quote normalization in the `kaplan_kalender()` function:

```php
// Normalize smart quotes and sanitize numeric attributes to avoid malformed URLs
foreach ($atts as $k => $v) {
    if (is_string($v)) {
        $v = str_replace(
            ['"','"','„',''',''','‹','›'],
            ['\"','\"','\"','\\'','\\'','\\'','\\''],
            $v
        );
        // Trim surrounding quotes/spaces
        $v = trim($v, " \\t\\n\\r\\0\\x0B\\\"'" );
        $atts[$k] = $v;
    }
}
// Ensure numeric-only fields are numbers
foreach (['days','template'] as $nk) {
    if (isset($atts[$nk]) && is_string($atts[$nk])) {
        $atts[$nk] = preg_replace('/\\D+/', '', $atts[$nk]);
    }
}
```

## Test Case
**Before:**
```
[ausgabe_kaplan days="14" ...] → days="\u201c14\u201d" → Empty response
```

**After:**
```
[ausgabe_kaplan days="14" ...] → days=14 → Valid JSON with events
```

## Prevention
The fix handles all common smart quote variants and ensures numeric fields are clean integers, preventing similar issues in the future.

## Files Modified
- `/wp-content/plugins/kaplan-gottesdienste/kaplan_gottesdienste.php` (Lines 687-705)
- Local development version synchronized

## Testing
Verified on http://kaplan-plugin-test.local/sample-page/ with debug enabled:
- Smart quotes properly converted
- API returns 1394 bytes with 2 events
- Template 3 layout displays correctly
- Registration links functional

---
**Fix Date**: 2025-09-21  
**Plugin Version**: 1.8.4 (pending bump to 1.8.5)  
**Issue**: Smart quotes in shortcode attributes break API requests  
**Status**: ✅ Resolved