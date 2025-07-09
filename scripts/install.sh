#!/bin/bash

# Nextcloud Chat Installation Script

set -e

APP_NAME="nextcloud_chat"
NEXTCLOUD_PATH="${1:-/var/www/nextcloud}"
APPS_PATH="$NEXTCLOUD_PATH/apps"

if [ ! -d "$NEXTCLOUD_PATH" ]; then
    echo "Error: Nextcloud directory not found at $NEXTCLOUD_PATH"
    echo "Usage: $0 [nextcloud_path]"
    exit 1
fi

echo "Installing Nextcloud Chat to $APPS_PATH..."

# Check if app already exists
if [ -d "$APPS_PATH/$APP_NAME" ]; then
    echo "Warning: App already exists. Removing old version..."
    rm -rf "$APPS_PATH/$APP_NAME"
fi

# Copy app files
echo "Copying application files..."
cp -r . "$APPS_PATH/$APP_NAME"

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data "$APPS_PATH/$APP_NAME"
chmod -R 755 "$APPS_PATH/$APP_NAME"

# Install dependencies if needed
if [ -f "$APPS_PATH/$APP_NAME/composer.json" ] && [ ! -d "$APPS_PATH/$APP_NAME/vendor" ]; then
    echo "Installing PHP dependencies..."
    cd "$APPS_PATH/$APP_NAME"
    composer install --no-dev --optimize-autoloader
    cd -
fi

# Build frontend if needed
if [ -f "$APPS_PATH/$APP_NAME/package.json" ] && [ ! -d "$APPS_PATH/$APP_NAME/js" ]; then
    echo "Building frontend..."
    cd "$APPS_PATH/$APP_NAME"
    npm ci
    npm run build
    cd -
fi

# Enable the app
echo "Enabling the app..."
cd "$NEXTCLOUD_PATH"
sudo -u www-data php occ app:enable $APP_NAME

echo "Installation complete!"
echo "Please configure the app in Settings → Administration → Nextcloud Chat"
