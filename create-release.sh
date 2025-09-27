#!/bin/bash

# KaPlan WordPress Plugin Release Creator
# Fully automated GitHub release script with ZIP upload
# Usage: ./create-release.sh [version] [title]
# Example: ./create-release.sh 1.8.5 "Critical fix: Smart quotes normalization"

set -e

# Handle help flag
if [[ "$1" == "--help" ]] || [[ "$1" == "-h" ]]; then
    echo "KaPlan WordPress Plugin Release Creator v2.0.0"
    echo ""
    echo "Usage: $0 [version] [title]"
    echo ""
    echo "This script will:"
    echo "  1. Update version in plugin file"
    echo "  2. Create git commit and tag"
    echo "  3. Push to GitHub repository"
    echo "  4. Create release ZIP file"
    echo "  5. Create GitHub release (if GitHub CLI available)"
    echo "  6. Upload ZIP file to release"
    echo ""
    echo "Examples:"
    echo "  $0                                              # Interactive mode"
    echo "  $0 1.8.6 \"Bug fixes and improvements\"           # With parameters"
    echo "  $0 1.9.0 \"New features and enhancements\"       # Major release"
    echo ""
    echo "Requirements:"
    echo "  • Git repository with remote origin"
    echo "  • Write access to the repository"
    echo "  • GitHub CLI (gh) for automatic release creation (optional)"
    echo ""
    exit 0
fi

# Function for manual release steps
show_manual_steps() {
    echo ""
    echo "✅ Release preparation complete!"
    echo ""
    echo "📦 ZIP file created: $ZIP_NAME"
    echo "🏷️  Tag created: v$VERSION"
    echo ""
    echo "🌐 Manual steps (GitHub CLI not available):"
    echo "1. Go to: https://github.com/hansjoergJL/kaplan-gottesdienste/releases"
    echo "2. Click 'Create a new release'"
    echo "3. Select tag: v$VERSION"
    echo "4. Title: KaPlan Gottesdienste v$VERSION"
    echo "5. Upload the ZIP file: $ZIP_NAME"
    echo "6. Add release notes describing the changes"
    echo "7. Click 'Publish release'"
    echo ""
    echo "🔄 After publishing, WordPress sites will receive update notifications!"
}

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

# Check if version is already set correctly
echo "Checking current version in plugin file..."
CURRENT_VERSION=$(grep "^\s*\*\s*Version:" kaplan_gottesdienste.php | sed 's/.*Version:\s*//' | tr -d ' ')
CURRENT_CONST_VERSION=$(grep "define('KAPLAN_PLUGIN_VERSION'" kaplan_gottesdienste.php | sed "s/.*'\([^']*\)'.*/\1/")

if [ "$CURRENT_VERSION" = "$VERSION" ] && [ "$CURRENT_CONST_VERSION" = "$VERSION" ]; then
    echo "✅ Version $VERSION already set correctly in plugin file - skipping update"
    SKIP_COMMIT=true
else
    echo "Updating version in plugin file from $CURRENT_VERSION to $VERSION..."
    # Use more precise patterns to avoid changing debug comments
    sed -i.bak "s/^\( \* Version: \).*/\1$VERSION/" kaplan_gottesdienste.php
    sed -i.bak "s/define('KAPLAN_PLUGIN_VERSION', '.*')/define('KAPLAN_PLUGIN_VERSION', '$VERSION')/" kaplan_gottesdienste.php
    rm -f *.bak
    SKIP_COMMIT=false
fi

# Commit the version update only if changes were made
if [ "$SKIP_COMMIT" != "true" ]; then
    echo "Committing version update..."
    git add kaplan_gottesdienste.php
    git commit -m "Version $VERSION - $TITLE"
else
    echo "No version changes to commit"
fi

# Create and push tag
echo "Creating and pushing tag v$VERSION..."
if git tag -l | grep -q "^v$VERSION$"; then
    echo "Warning: Tag v$VERSION already exists locally. Deleting and recreating..."
    git tag -d "v$VERSION"
fi
git tag "v$VERSION" -m "Version $VERSION - $TITLE"
git push origin main
git push origin "v$VERSION"

# Create ZIP file for release
echo "Creating ZIP file..."
ZIP_NAME="kaplan-gottesdienste-$VERSION.zip"
zip -r "$ZIP_NAME" . -x "*.git*" "*.DS_Store*" "Tools/*" "*.zip" "*.sh" "*.md" ".kombai/*" ".qodo/*" "~*" "create-release.sh" "*.bak"

# Check if GitHub CLI is available
echo "Checking for GitHub CLI..."
if command -v gh >/dev/null 2>&1; then
    echo "📱 GitHub CLI found - checking authentication..."
    
    # Check if authenticated with GitHub
    if gh auth status >/dev/null 2>&1; then
        echo "✅ GitHub CLI authenticated - creating release automatically..."
    else
        echo "⚠️  GitHub CLI not authenticated. Please run: gh auth login"
        echo "   Falling back to manual steps..."
        show_manual_steps
        exit 0
    fi
    
    # Check if release notes file exists
    NOTES_FILE="release-notes-$VERSION.md"
    if [ -f "$NOTES_FILE" ]; then
        echo "📋 Using release notes from: $NOTES_FILE"
        NOTES_ARG="--notes-file $NOTES_FILE"
    else
        echo "📝 Creating basic release notes..."
        # Create temporary notes file to avoid shell escaping issues
        TEMP_NOTES=".temp-release-notes.md"
        cat > "$TEMP_NOTES" << EOF
Version $VERSION - $TITLE

Download the plugin ZIP file from the assets below.
EOF
        NOTES_ARG="--notes-file $TEMP_NOTES"
    fi
    
    # Create GitHub release
    echo "🚀 Creating GitHub release..."
    if gh release create "v$VERSION" --title "KaPlan Gottesdienste v$VERSION" $NOTES_ARG "$ZIP_NAME"; then
        echo "✅ GitHub release created successfully!"
        echo "✅ ZIP file uploaded successfully!"
        
        # Clean up temporary notes file if created
        if [ -n "$TEMP_NOTES" ] && [ -f "$TEMP_NOTES" ]; then
            rm -f "$TEMP_NOTES"
        fi

        # Get release URL
        RELEASE_URL=$(gh release view "v$VERSION" --json url --jq '.url')

        echo ""
        echo "🎉 RELEASE COMPLETED SUCCESSFULLY!"
        echo ""
        echo "📦 ZIP file created: $ZIP_NAME"
        echo "🏷️  Tag created and pushed: v$VERSION"
        echo "🌐 GitHub release: $RELEASE_URL"
        echo ""
        echo "🔄 WordPress sites will receive update notifications within 12 hours!"
    else
        echo "❌ Failed to create GitHub release!"
        
        # Clean up temporary notes file if created
        if [ -n "$TEMP_NOTES" ] && [ -f "$TEMP_NOTES" ]; then
            rm -f "$TEMP_NOTES"
        fi
        
        echo "   Please create the release manually:"
        echo "   1. Go to: https://github.com/hansjoergJL/kaplan-gottesdienste/releases"
        echo "   2. Click 'Create a new release'"
        echo "   3. Select tag: v$VERSION"
        echo "   4. Title: KaPlan Gottesdienste v$VERSION"
        echo "   5. Upload the ZIP file: $ZIP_NAME"
        echo "   6. Click 'Publish release'"
        exit 1
    fi
else
    echo "⚠️  GitHub CLI not found. Showing manual steps..."
    show_manual_steps
fi
