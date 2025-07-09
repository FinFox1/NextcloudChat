### 18. Package Build Scripts (scripts/build.sh)

```bash
#!/bin/bash

# Nextcloud Chat Build Script

set -e

APP_NAME="nextcloud_chat"
BUILD_DIR="build"
DIST_DIR="$BUILD_DIR/dist"

echo "Building Nextcloud Chat..."

# Clean previous builds
rm -rf $BUILD_DIR
mkdir -p $DIST_DIR

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install and build JS dependencies
echo "Installing and building JavaScript..."
npm ci
npm run build

# Create distribution directory structure
echo "Creating distribution package..."
mkdir -p $DIST_DIR/$APP_NAME

# Copy application files
cp -r appinfo $DIST_DIR/$APP_NAME/
cp -r lib $DIST_DIR/$APP_NAME/
cp -r templates $DIST_DIR/$APP_NAME/
cp -r css $DIST_DIR/$APP_NAME/
cp -r js $DIST_DIR/$APP_NAME/
cp -r l10n $DIST_DIR/$APP_NAME/
cp -r img $DIST_DIR/$APP_NAME/
cp -r vendor $DIST_DIR/$APP_NAME/

# Copy configuration files
cp composer.json $DIST_DIR/$APP_NAME/
cp CHANGELOG.md $DIST_DIR/$APP_NAME/ 2>/dev/null || echo "CHANGELOG.md not found, skipping..."
cp README.md $DIST_DIR/$APP_NAME/

# Remove development files
rm -rf $DIST_DIR/$APP_NAME/vendor/*/tests
rm -rf $DIST_DIR/$APP_NAME/vendor/*/test
rm -rf $DIST_DIR/$APP_NAME/vendor/*/.git*

# Create tarball
cd $DIST_DIR
tar -czf $APP_NAME.tar.gz $APP_NAME/
cd ../..

echo "Build complete! Package available at: $DIST_DIR/$APP_NAME.tar.gz"
