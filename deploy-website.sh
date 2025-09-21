#!/bin/bash

# Website Deployment Script for KaPlan Plugin Page
#
# This script securely uploads the plugin's website (index.html)
# to the specified server using SFTP. It reads credentials from
# environment variables: SERVER_JL_DOMAIN, SERVER_JL_USER, SERVER_JL_PASSWORD.
#
# Author: Gemini Code Assist & Hans-Joerg Joedike
# Version: 1.2.0

set -e # Exit immediately if a command exits with a non-zero status.
set -o pipefail # Ensure that a pipeline command returns a non-zero status if any command in the pipeline fails.
# --- Configuration ---
# Local file to upload
LOCAL_FILE="plugin-website/index.html"

# Remote path on the server
REMOTE_PATH="/public_html/software/kaplan-plugin/"

# --- Colors for output ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# --- Helper Functions ---
print_header() {
    echo -e "${BLUE}"
    echo "============================================="
    echo "  KaPlan Plugin Website Deployment Script"
    echo "============================================="
    echo -e "${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}" >&2
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# --- Main Script Logic ---

print_header

# 1. Check if the local file exists
if [ ! -f "$LOCAL_FILE" ]; then
    print_error "Local file not found at '$LOCAL_FILE'."
    print_info "Please ensure you are running this script from the plugin's root directory."
    exit 1
fi
print_success "Local file found: $LOCAL_FILE"

# 2. Check for environment variables and required tools
echo
print_info "Checking for credentials and required tools..."
if [ -z "$SERVER_JL_DOMAIN" ] || [ -z "$SERVER_JL_USER" ] || [ -z "$SERVER_JL_PASSWORD" ]; then
    print_error "One or more required environment variables are not set."
    print_info "Please set SERVER_JL_DOMAIN, SERVER_JL_USER, and SERVER_JL_PASSWORD."
    print_info 'Example: export SERVER_JL_DOMAIN="sftp.jlsoftware.de"'
    exit 1
fi
print_success "Credentials found in environment variables."

if ! command -v sshpass &> /dev/null; then
    print_error "'sshpass' is not installed, but is required for non-interactive SFTP."
    print_info "On macOS, you can install it via Homebrew: brew install hudochenkov/sshpass/sshpass"
    exit 1
fi
print_success "Required tool 'sshpass' is installed."

# 3. Test SFTP connection and credentials
echo
print_info "Testing SFTP connection to '$SERVER_JL_USER@$SERVER_JL_DOMAIN'..."
if ! sshpass -p "$SERVER_JL_PASSWORD" sftp -o StrictHostKeyChecking=no -b - "$SERVER_JL_USER@$SERVER_JL_DOMAIN" >/dev/null 2>&1; then
    print_error "Authentication failed! The server rejected your credentials."
    print_info "Please double-check your SERVER_JL_USER and SERVER_JL_PASSWORD environment variables."
    print_info "Common issues: typos, special characters in password needing quotes (e.g., export SERVER_JL_PASSWORD='p@ss!word')."
    exit 1
fi
print_success "SFTP connection successful."


echo
print_info "Preparing to upload '$LOCAL_FILE' to '$SERVER_JL_USER@$SERVER_JL_DOMAIN:$REMOTE_PATH'"
echo

# 4. Perform the SFTP upload
# We use 'sshpass' to provide the password non-interactively.
print_info "Starting SFTP upload..."

# The `-` prefix before a command tells SFTP to continue even if the command fails.
# This is useful for `mkdir` as the directory may already exist.
sshpass -p "$SERVER_JL_PASSWORD" sftp -o StrictHostKeyChecking=no "$SERVER_JL_USER@$SERVER_JL_DOMAIN" <<'EOF'
-mkdir /public_html/software
-mkdir /public_html/software/kaplan-plugin/
cd /public_html/software/kaplan-plugin/
put "$LOCAL_FILE" "index.html"
bye
EOF

if [ $? -eq 0 ]; then
    print_success "Website deployment complete!"
    # Assuming the server domain is the same for web access
    WEB_DOMAIN=$(echo "$SERVER_JL_DOMAIN" | sed 's/sftp\.//')
    print_info "Page updated at: https://$WEB_DOMAIN/software/kaplan-plugin/"
else
    print_error "SFTP file operation failed."
    print_info "Authentication was successful, but a file/directory operation failed."
    print_info "This is likely a file permission issue on the server. Please check that the user '$SERVER_JL_USER' has write permissions for:"
    print_info "  - /public_html/software/"
    print_info "  - /public_html/software/kaplan-plugin/"
    exit 1
fi