#!/bin/bash

# Website Deployment Script for KaPlan Plugin Page v2.0
#
# This script uploads the plugin's website (index.html) to the web server
# using multiple connection methods (FTP, SFTP, SCP) with automatic fallback.
# It reads credentials from environment variables: SERVER_JL_DOMAIN, SERVER_JL_USER, SERVER_JL_PASSWORD.
#
# Author: Hans-Joerg Joedike
# Version: 2.0.0

set -e # Exit immediately if a command exits with a non-zero status.

# --- Configuration ---
LOCAL_FILE="plugin-website/index.html"
REMOTE_PATH="software/kaplan-plugin/"
REMOTE_FILE="index.htm"

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
    echo "  KaPlan Plugin Website Deployment v2.0"
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

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Function to try FTP upload
try_ftp() {
    print_info "Attempting FTP deployment to $SERVER_JL_DOMAIN..."
    
    # Try with ftp command
    if command -v ftp >/dev/null 2>&1; then
        cat > /tmp/ftp_script <<EOF
open $SERVER_JL_DOMAIN
user $SERVER_JL_USER $SERVER_JL_PASSWORD
binary
mkdir $REMOTE_PATH
cd $REMOTE_PATH
put $LOCAL_FILE $REMOTE_FILE
quit
EOF
        
        if ftp -n < /tmp/ftp_script 2>/dev/null; then
            rm -f /tmp/ftp_script
            return 0
        fi
        rm -f /tmp/ftp_script
    fi
    
    return 1
}

# Function to try LFTP (more robust FTP client)
try_lftp() {
    print_info "Attempting LFTP deployment to $SERVER_JL_DOMAIN..."
    
    if command -v lftp >/dev/null 2>&1; then
        if lftp -c "set ftp:ssl-allow no; open -u $SERVER_JL_USER,$SERVER_JL_PASSWORD $SERVER_JL_DOMAIN; mkdir -p $REMOTE_PATH; cd $REMOTE_PATH; put $LOCAL_FILE -o $REMOTE_FILE; quit" 2>/dev/null; then
            return 0
        fi
    else
        print_warning "lftp not available (install with: brew install lftp)"
    fi
    
    return 1
}

# Function to try curl FTP upload with multiple paths and extensions
try_curl_ftp() {
    print_info "Attempting cURL FTP deployment to $SERVER_JL_DOMAIN..."
    
    if command -v curl >/dev/null 2>&1; then
        # Try different path and file combinations
        local paths=("software/kaplan-plugin/" "software/" "kaplan-plugin/" "")
        local files=("index.htm" "index.html")
        
        for path in "${paths[@]}"; do
            for file in "${files[@]}"; do
                print_info "Trying path: $path with file: $file"
                if curl -T "$LOCAL_FILE" "ftp://$SERVER_JL_USER:$SERVER_JL_PASSWORD@$SERVER_JL_DOMAIN/$path$file" --create-dirs -s 2>/dev/null; then
                    print_success "Success! Uploaded to: $path$file"
                    SUCCESSFUL_PATH="$path"
                    SUCCESSFUL_FILE="$file"
                    return 0
                fi
            done
        done
    fi
    
    return 1
}

# Function to try SFTP
try_sftp() {
    print_info "Attempting SFTP deployment to $SERVER_JL_DOMAIN..."
    
    if command -v sshpass >/dev/null 2>&1; then
        # Try different SFTP server addresses
        for sftp_host in "sftp.$SERVER_JL_DOMAIN" "$SERVER_JL_DOMAIN" "ftp.$SERVER_JL_DOMAIN"; do
            print_info "Trying SFTP connection to $sftp_host..."
            
            if sshpass -p "$SERVER_JL_PASSWORD" sftp -o StrictHostKeyChecking=no -o ConnectTimeout=10 "$SERVER_JL_USER@$sftp_host" <<EOF 2>/dev/null
-mkdir $REMOTE_PATH
cd $REMOTE_PATH
put $LOCAL_FILE $REMOTE_FILE
bye
EOF
            then
                return 0
            fi
        done
    else
        print_warning "sshpass not available (install with: brew install hudochenkov/sshpass/sshpass)"
    fi
    
    return 1
}

# Function to display final result
show_result() {
    if [ $1 -eq 0 ]; then
        print_success "Website deployment successful!"
        echo
        print_info "🌐 Website URLs:"
        if [ -n "$SUCCESSFUL_PATH" ] && [ -n "$SUCCESSFUL_FILE" ]; then
            # Extract web path from FTP path
            WEB_PATH=$(echo "$SUCCESSFUL_PATH" | sed 's|^[^/]*/||' | sed 's|/$||')
            print_info "   • http://$SERVER_JL_DOMAIN/$WEB_PATH/"
            print_info "   • http://www.$SERVER_JL_DOMAIN/$WEB_PATH/"
            print_info "   • Direct file: http://$SERVER_JL_DOMAIN/$WEB_PATH/$SUCCESSFUL_FILE"
        else
            print_info "   • http://$SERVER_JL_DOMAIN/software/kaplan-plugin/"
            print_info "   • http://www.$SERVER_JL_DOMAIN/software/kaplan-plugin/"
        fi
        echo
        print_info "📄 Updated file: ${SUCCESSFUL_FILE:-$REMOTE_FILE}"
        print_info "📅 Deployment time: $(date)"
        echo
    else
        print_error "All deployment methods failed!"
        echo
        print_info "🔧 Troubleshooting steps:"
        print_info "1. Verify credentials are correct"
        print_info "2. Check if the server is accessible"
        print_info "3. Ensure the remote directory exists"
        print_info "4. Try manual FTP connection to test"
        echo
        print_info "💡 Manual test command:"
        print_info "   ftp $SERVER_JL_DOMAIN"
        print_info "   (then login with: $SERVER_JL_USER / [password])"
        exit 1
    fi
}

# --- Main Script Logic ---

print_header

# 1. Check if the local file exists
if [ ! -f "$LOCAL_FILE" ]; then
    print_error "Local file not found at '$LOCAL_FILE'."
    print_info "Please ensure you are running this script from the plugin's root directory."
    exit 1
fi
print_success "Local file found: $LOCAL_FILE ($(du -h "$LOCAL_FILE" | cut -f1))"

# 2. Check for environment variables
echo
print_info "Checking credentials..."
if [ -z "$SERVER_JL_DOMAIN" ] || [ -z "$SERVER_JL_USER" ] || [ -z "$SERVER_JL_PASSWORD" ]; then
    print_error "Required environment variables are not set."
    print_info "Please set: SERVER_JL_DOMAIN, SERVER_JL_USER, SERVER_JL_PASSWORD"
    exit 1
fi
print_success "Credentials found for $SERVER_JL_USER@$SERVER_JL_DOMAIN"

# 3. Try multiple deployment methods
echo
print_info "Starting deployment process..."
DEPLOYED=1
SUCCESSFUL_PATH=""
SUCCESSFUL_FILE=""

# Method 1: Try cURL FTP (most reliable)
if try_curl_ftp; then
    print_success "Deployment successful via cURL FTP!"
    DEPLOYED=0
elif try_lftp; then
    print_success "Deployment successful via LFTP!"
    DEPLOYED=0
elif try_ftp; then
    print_success "Deployment successful via FTP!"
    DEPLOYED=0
elif try_sftp; then
    print_success "Deployment successful via SFTP!"
    DEPLOYED=0
fi

# 4. Show final result
echo
show_result $DEPLOYED