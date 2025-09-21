# KaPlan Plugin - Leitung Positioning Change

## Overview
This document describes the modification made to the KaPlan Gottesdienste WordPress plugin to improve the layout of "Leitung" (leadership/officiant) information in Template="2" and Template="3" layouts.

## Changes Made

### Template="2" (2-Column Layout)
**Before**: Leitung information was embedded inline with room information in parentheses
**After**: Leitung information appears as the last element on a fresh line within the content column (second column)

### Template="3" (3-Column Layout)  
**Before**: Leitung information was embedded within the service info block
**After**: Leitung information appears as the last element on a fresh line within the service info column (second column)

## Technical Details

### Code Changes
1. **Template 2**: 
   - Removed `$Ltg` from inline room information string
   - Added Leitung as separate element: `<br><div class="kaplan-leitung">($Ltg)</div>`

2. **Template 3**:
   - Removed embedded Leitung from service info block 
   - Added Leitung as final service line: `<div class="kaplan-service-line kaplan-leitung">($Ltg)</div>`

### CSS Styling
Added `.kaplan-leitung` class to both template stylesheets:
```css
.kaplan-leitung {
    display: block;
    margin-top: 2px;
    font-size: 0.9em;
    color: #666;
}
```

## Usage
The Leitung information will now automatically appear as the last element in the second column for both Template="2" and Template="3" when the `leitung` shortcode parameter is specified.

Example shortcodes:
```
[ausgabe_kaplan template="2" leitung="TN" server="..." ...]
[ausgabe_kaplan template="3" leitung="VN" server="..." ...]
```

## Styling Customization
To customize the appearance of the Leitung information, you can override the `.kaplan-leitung` CSS class in your theme's stylesheet or use WordPress customizer.

## Version Information
- **Modified Files**: `kaplan_gottesdienste.php`
- **Date**: 2025-09-21
- **Affects**: Template="2" and Template="3" layouts only
- **Template="1"**: Unchanged - maintains original inline layout

## Testing
This change has been tested for:
- ✅ PHP syntax validation 
- ✅ Template 2 layout positioning
- ✅ Template 3 layout positioning  
- ✅ Template 1 remains unchanged
- ✅ CSS styling consistency