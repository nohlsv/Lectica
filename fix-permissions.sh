#!/bin/bash

# Laravel File Permissions Fix Script for Production Server
# Run this on your VPS as root: sudo ./fix-permissions.sh

echo "=== Laravel Permission Fix Script ==="
echo "Fixing permissions for: /home/lectica/htdocs/lectica.tech"

# Navigate to Laravel project directory
cd /home/lectica/htdocs/lectica.tech

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "ERROR: artisan file not found. Make sure you're running this from the Laravel root directory."
    exit 1
fi

echo "Current directory: $(pwd)"

# Find the web server user
WEB_USER="www-data"
if id "nginx" &>/dev/null; then
    WEB_USER="nginx"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
elif id "www-data" &>/dev/null; then
    WEB_USER="www-data"
else
    echo "WARNING: Could not detect web server user. Using www-data as default."
fi

echo "Using web server user: $WEB_USER"

# Set ownership
echo "Setting ownership to $WEB_USER:$WEB_USER..."
chown -R $WEB_USER:$WEB_USER .

# Set base permissions
echo "Setting base file permissions..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Set special permissions for Laravel directories
echo "Setting Laravel-specific permissions..."

# Storage directory (needs to be writable)
chmod -R 775 storage/
chown -R $WEB_USER:$WEB_USER storage/

# Bootstrap cache directory (needs to be writable)
mkdir -p bootstrap/cache
chmod -R 775 bootstrap/cache/
chown -R $WEB_USER:$WEB_USER bootstrap/cache/

# Make artisan executable
chmod +x artisan

# Create storage subdirectories if they don't exist
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p storage/app/public

# Set permissions on storage subdirectories
chmod -R 775 storage/framework/
chmod -R 775 storage/logs/
chmod -R 775 storage/app/

# Set ownership on storage subdirectories
chown -R $WEB_USER:$WEB_USER storage/framework/
chown -R $WEB_USER:$WEB_USER storage/logs/
chown -R $WEB_USER:$WEB_USER storage/app/

echo ""
echo "=== Permission Summary ==="
echo "Project root owner: $(stat -c '%U:%G' .)"
echo "Storage directory permissions: $(stat -c '%a' storage/)"
echo "Storage directory owner: $(stat -c '%U:%G' storage/)"
echo "Bootstrap cache permissions: $(stat -c '%a' bootstrap/cache/)"
echo "Bootstrap cache owner: $(stat -c '%U:%G' bootstrap/cache/)"

echo ""
echo "=== Testing Write Permissions ==="
# Test if we can write to storage
if [ -w storage/framework/views/ ]; then
    echo "✓ storage/framework/views/ is writable"
else
    echo "✗ storage/framework/views/ is NOT writable"
fi

if [ -w bootstrap/cache/ ]; then
    echo "✓ bootstrap/cache/ is writable"
else
    echo "✗ bootstrap/cache/ is NOT writable"
fi

echo ""
echo "=== Next Steps ==="
echo "1. Clear Laravel caches:"
echo "   php artisan config:clear"
echo "   php artisan view:clear"
echo "   php artisan cache:clear"
echo ""
echo "2. If you still get permission errors, check:"
echo "   - Your web server user with: ps aux | grep -E 'nginx|apache|httpd'"
echo "   - SELinux status (if applicable): sestatus"
echo ""
echo "Permission fix completed!"
