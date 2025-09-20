#!/bin/bash

# KaPlan WordPress Plugin Release Creator
# Usage: ./create-release.sh [version] [title]
# Example: ./create-release.sh 1.8.3 "Bug fixes and improvements"

set -e

# Get version from command line or ask user
if [ -z "$1" ]; then
    echo "Enter version number (e.g., 1.8.3):"
    read VERSION
else
    VERSION="$1"
fi

# Get release title from command line or ask user
if [ -z "$2" ]; then
    echo "Enter release title:"
    read TITLE
else
    TITLE="$2"
fi

echo "Creating release for version $VERSION: $TITLE"

# Check if we're in the right directory
if [ ! -f "kaplan_gottesdienste.php" ]; then
    echo "Error: kaplan_gottesdienste.php not found. Are you in the plugin directory?"
    exit 1
fi

# Update version in main plugin file
echo "Updating version in plugin file..."
sed -i.bak "s/Version: .*/Version: $VERSION/" kaplan_gottesdienste.php
sed -i.bak "s/define('KAPLAN_PLUGIN_VERSION', '.*'/define('KAPLAN_PLUGIN_VERSION', '$VERSION'/" kaplan_gottesdienste.php

# Commit the version update
echo "Committing version update..."
git add kaplan_gottesdienste.php
git commit -m "Version $VERSION - $TITLE"

# Create and push tag
echo "Creating and pushing tag v$VERSION..."
git tag "v$VERSION" -m "Version $VERSION - $TITLE"
git push origin main
git push origin "v$VERSION"

# Create ZIP file for release
echo "Creating ZIP file..."
ZIP_NAME="kaplan-gottesdienste-$VERSION.zip"
zip -r "$ZIP_NAME" . -x "*.git*" "*.DS_Store*" "Tools/*" "*.zip" "*.sh" "*.md" ".kombai/*" ".qodo/*" "~*" "create-release.sh"

echo "✅ Release preparation complete!"
echo ""
echo "📦 ZIP file created: $ZIP_NAME"
echo "🏷️  Tag created: v$VERSION"
echo ""
echo "🌐 Next steps:"
echo "1. Go to: https://github.com/hansjoergJL/kaplan-gottesdienste/releases"
echo "2. Click 'Create a new release'"
echo "3. Select tag: v$VERSION"
echo "4. Title: KaPlan Gottesdienste v$VERSION"
echo "5. Upload the ZIP file: $ZIP_NAME"
echo "6. Add release notes describing the changes"
echo "7. Click 'Publish release'"
echo ""
echo "🔄 After publishing, WordPress sites will receive update notifications!"
