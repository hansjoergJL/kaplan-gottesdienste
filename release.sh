#!/bin/bash

# KaPlan Plugin Release Script
# Automatically reads version from plugin file and creates GitHub release
# 
# Usage: ./release.sh [commit-message]
#
# Author: Hans-Joerg Joedike
# Version: 1.0.0

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PLUGIN_FILE="kaplan_gottesdienste.php"
REPO_URL="https://github.com/hansjoergJL/kaplan-gottesdienste"

# Functions
print_header() {
    echo -e "${BLUE}"
    echo "╔════════════════════════════════════════╗"
    echo "║         KaPlan Plugin Release          ║"
    echo "║              Automated Script          ║"
    echo "╚════════════════════════════════════════╝"
    echo -e "${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Check if plugin file exists
check_plugin_file() {
    if [[ ! -f "$PLUGIN_FILE" ]]; then
        print_error "Plugin file '$PLUGIN_FILE' not found!"
        print_info "Make sure you're running this script from the plugin directory."
        exit 1
    fi
    print_success "Plugin file found: $PLUGIN_FILE"
}

# Extract version from plugin file
get_plugin_version() {
    # Extract version from plugin header
    HEADER_VERSION=$(grep -E "^\s*\*\s*Version:" "$PLUGIN_FILE" | head -1 | sed -E 's/.*Version:\s*([0-9]+\.[0-9]+\.[0-9]+).*/\1/')
    
    # Extract version from define statement
    DEFINE_VERSION=$(grep -E "define\('KAPLAN_PLUGIN_VERSION'" "$PLUGIN_FILE" | sed -E "s/.*'([0-9]+\.[0-9]+\.[0-9]+)'.*/\1/")
    
    # Check if versions were found
    if [[ -z "$HEADER_VERSION" ]]; then
        print_error "Could not find version in plugin header!"
        print_info "Make sure the plugin file contains: * Version: X.Y.Z"
        exit 1
    fi
    
    if [[ -z "$DEFINE_VERSION" ]]; then
        print_error "Could not find version in define statement!"
        print_info "Make sure the plugin file contains: define('KAPLAN_PLUGIN_VERSION', 'X.Y.Z');"
        exit 1
    fi
    
    # Check if versions match
    if [[ "$HEADER_VERSION" != "$DEFINE_VERSION" ]]; then
        print_error "Version mismatch!"
        print_info "Plugin header version: $HEADER_VERSION"
        print_info "Define version: $DEFINE_VERSION"
        print_info "Please update both versions to match."
        exit 1
    fi
    
    VERSION="$HEADER_VERSION"
    print_success "Plugin version detected: $VERSION"
}

# Validate version format
validate_version() {
    if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        print_error "Invalid version format: $VERSION"
        print_info "Version must be in format X.Y.Z (e.g., 1.8.0)"
        exit 1
    fi
    print_success "Version format is valid"
}

# Check git status
check_git_status() {
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        print_error "Not a git repository!"
        print_info "Initialize git first: git init"
        exit 1
    fi
    
    # Check if there are uncommitted changes
    if [[ -n $(git status --porcelain) ]]; then
        print_warning "You have uncommitted changes:"
        git status --short
        echo
        read -p "Do you want to commit these changes first? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            return 0  # Will commit in next step
        else
            print_error "Please commit your changes before releasing."
            exit 1
        fi
    else
        print_success "Working directory is clean"
    fi
}

# Check if tag already exists
check_existing_tag() {
    TAG="v$VERSION"
    if git tag -l | grep -q "^$TAG$"; then
        print_error "Tag $TAG already exists!"
        print_info "Existing tags:"
        git tag -l | tail -5
        print_info "Please update the version number or delete the existing tag:"
        print_info "git tag -d $TAG && git push origin :refs/tags/$TAG"
        exit 1
    fi
    print_success "Tag $TAG is available"
}

# Get commit message
get_commit_message() {
    if [[ -n "$1" ]]; then
        COMMIT_MESSAGE="$1"
    else
        COMMIT_MESSAGE="Version $VERSION"
        
        echo
        print_info "Default commit message: '$COMMIT_MESSAGE'"
        read -p "Enter custom commit message (or press Enter to use default): " -r CUSTOM_MESSAGE
        if [[ -n "$CUSTOM_MESSAGE" ]]; then
            COMMIT_MESSAGE="$CUSTOM_MESSAGE"
        fi
    fi
    
    print_success "Commit message: '$COMMIT_MESSAGE'"
}

# Perform pre-release checks
perform_checks() {
    print_info "Performing pre-release checks..."
    
    # Check PHP syntax
    if command -v php > /dev/null 2>&1; then
        if php -l "$PLUGIN_FILE" > /dev/null 2>&1; then
            print_success "PHP syntax check passed"
        else
            print_error "PHP syntax error in $PLUGIN_FILE"
            php -l "$PLUGIN_FILE"
            exit 1
        fi
        
        if php -l "includes/class-kaplan-updater.php" > /dev/null 2>&1; then
            print_success "Updater class syntax check passed"
        else
            print_error "PHP syntax error in updater class"
            php -l "includes/class-kaplan-updater.php"
            exit 1
        fi
    else
        print_warning "PHP not found in PATH - skipping syntax check"
    fi
    
    # Check if GitHub workflow exists
    if [[ -f ".github/workflows/release.yml" ]]; then
        print_success "GitHub Actions workflow found"
    else
        print_warning "GitHub Actions workflow not found - manual release creation needed"
    fi
}

# Create release
create_release() {
    print_info "Creating release for version $VERSION..."
    
    # Stage all changes
    git add .
    
    # Check if there's anything to commit
    if [[ -n $(git diff --cached --name-only) ]]; then
        # Commit changes
        git commit -m "$COMMIT_MESSAGE"
        print_success "Changes committed"
    else
        print_info "No changes to commit"
    fi
    
    # Create tag
    TAG="v$VERSION"
    git tag -a "$TAG" -m "Release version $VERSION"
    print_success "Created tag: $TAG"
    
    # Push to origin
    print_info "Pushing to GitHub..."
    git push origin main
    git push origin "$TAG"
    print_success "Pushed to GitHub repository"
}

# Show success message
show_success() {
    echo
    echo -e "${GREEN}"
    echo "╔════════════════════════════════════════╗"
    echo "║            🎉 SUCCESS! 🎉              ║"
    echo "╚════════════════════════════════════════╝"
    echo -e "${NC}"
    
    print_success "Release v$VERSION created successfully!"
    echo
    print_info "Next steps:"
    echo "  1. 📋 Check GitHub Actions: $REPO_URL/actions"
    echo "  2. 🏷️  View Release: $REPO_URL/releases/tag/v$VERSION"
    echo "  3. 📦 Download ZIP: Available in ~2-3 minutes"
    echo "  4. 🔄 WordPress Updates: Available to users in ~12 hours"
    echo
    print_info "GitHub Actions will automatically:"
    echo "  • Create release ZIP file"
    echo "  • Generate release notes"
    echo "  • Publish to GitHub releases"
    echo "  • Notify WordPress sites with your plugin"
}

# Main execution
main() {
    print_header
    
    # Check prerequisites
    check_plugin_file
    get_plugin_version
    validate_version
    check_git_status
    check_existing_tag
    
    # Get user input
    get_commit_message "$1"
    
    # Perform checks
    perform_checks
    
    # Confirm release
    echo
    echo "📋 Release Summary:"
    echo "   Version: $VERSION"
    echo "   Tag: v$VERSION"
    echo "   Message: $COMMIT_MESSAGE"
    echo "   Repository: $REPO_URL"
    echo
    
    read -p "🚀 Create release? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_warning "Release cancelled by user"
        exit 0
    fi
    
    # Create the release
    create_release
    show_success
}

# Handle script arguments
if [[ "$1" == "--help" ]] || [[ "$1" == "-h" ]]; then
    echo "KaPlan Plugin Release Script"
    echo
    echo "Usage: $0 [commit-message]"
    echo
    echo "This script will:"
    echo "  1. Read version from $PLUGIN_FILE"
    echo "  2. Validate version format and git status"
    echo "  3. Create git commit and tag"
    echo "  4. Push to GitHub repository"
    echo "  5. Trigger automated release workflow"
    echo
    echo "Examples:"
    echo "  $0                                    # Interactive mode"
    echo "  $0 \"Add new community events feature\" # With custom message"
    echo
    exit 0
fi

# Run main function
main "$1"
