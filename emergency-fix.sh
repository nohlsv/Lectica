#!/bin/bash

# Emergency Laravel Permissions Fix - Aggressive Mode
# Run this on your VPS as root: sudo ./emergency-fix.sh

echo "=== EMERGENCY LARAVEL PERMISSIONS FIX ==="
echo "Target: /home/lectica/htdocs/lectica.tech"
echo "Timestamp: $(date)"

# Navigate to Laravel project
cd /home/lectica/htdocs/lectica.tech

if [ ! -f "artisan" ]; then
    echo "ERROR: Not in Laravel directory. Current location: $(pwd)"
    exit 1
fi

echo "✓ Confirmed Laravel directory"

# Show current problematic permissions
echo ""
echo "=== CURRENT PERMISSION PROBLEMS ==="
echo "Storage framework views directory:"
ls -la storage/framework/ 2>/dev/null || echo "storage/framework/ does not exist"
ls -la storage/framework/views/ 2>/dev/null || echo "storage/framework/views/ does not exist"

echo ""
echo "Current ownership:"
echo "Project root: $(stat -c '%U:%G (%a)' . 2>/dev/null)"
echo "Storage dir: $(stat -c '%U:%G (%a)' storage/ 2>/dev/null)"

# Find web server process
echo ""
echo "=== DETECTING WEB SERVER ==="
WEB_PROCESSES=$(ps aux | grep -E 'nginx|apache|httpd' | grep -v grep | head -3)
echo "Running web server processes:"
echo "$WEB_PROCESSES"

# Determine web server user
WEB_USER="www-data"
if pgrep nginx > /dev/null; then
    WEB_USER=$(ps aux | grep 'nginx: worker' | grep -v grep | head -1 | awk '{print $1}')
    echo "Detected nginx with user: $WEB_USER"
elif pgrep apache2 > /dev/null; then
    WEB_USER=$(ps aux | grep 'apache2' | grep -v grep | head -1 | awk '{print $1}')
    echo "Detected apache2 with user: $WEB_USER"
elif pgrep httpd > /dev/null; then
    WEB_USER=$(ps aux | grep 'httpd' | grep -v grep | head -1 | awk '{print $1}')
    echo "Detected httpd with user: $WEB_USER"
else
    echo "WARNING: Could not detect web server. Using default: www-data"
fi

echo "Using web server user: $WEB_USER"

# Nuclear option - complete ownership fix
echo ""
echo "=== APPLYING NUCLEAR PERMISSIONS FIX ==="

# Stop web server temporarily to avoid file locks
echo "Stopping web server..."
systemctl stop nginx 2>/dev/null || systemctl stop apache2 2>/dev/null || systemctl stop httpd 2>/dev/null || echo "Could not stop web server (might be ok)"

# Remove any existing problematic files
echo "Removing existing compiled views..."
rm -rf storage/framework/views/*
rm -rf storage/framework/cache/*
rm -rf bootstrap/cache/*

# Recreate directory structure
echo "Recreating storage structure..."
mkdir -p storage/framework/{sessions,views,cache,testing}
mkdir -p storage/logs
mkdir -p storage/app/{public,private}
mkdir -p bootstrap/cache

# Set ownership of EVERYTHING
echo "Setting ownership of entire project..."
chown -R $WEB_USER:$WEB_USER .

# Set aggressive permissions
echo "Setting aggressive permissions..."
chmod -R 755 .
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/

# Make sure key files are executable/readable
chmod +x artisan
chmod 644 app/Providers/AppServiceProvider.php
chmod 644 config/*.php

# Restart web server
echo "Starting web server..."
systemctl start nginx 2>/dev/null || systemctl start apache2 2>/dev/null || systemctl start httpd 2>/dev/null || echo "Could not start web server"

echo ""
echo "=== VERIFICATION ==="
echo "After fix ownership:"
echo "Project root: $(stat -c '%U:%G (%a)' .)"
echo "Storage: $(stat -c '%U:%G (%a)' storage/)"
echo "Storage/framework: $(stat -c '%U:%G (%a)' storage/framework/)"
echo "Storage/framework/views: $(stat -c '%U:%G (%a)' storage/framework/views/)"
echo "Bootstrap/cache: $(stat -c '%U:%G (%a)' bootstrap/cache/)"

# Test write permissions
echo ""
echo "Testing write permissions:"
if touch storage/framework/views/test_write.tmp 2>/dev/null; then
    echo "✓ CAN write to storage/framework/views/"
    rm storage/framework/views/test_write.tmp
else
    echo "✗ CANNOT write to storage/framework/views/"
fi

if touch bootstrap/cache/test_write.tmp 2>/dev/null; then
    echo "✓ CAN write to bootstrap/cache/"
    rm bootstrap/cache/test_write.tmp
else
    echo "✗ CANNOT write to bootstrap/cache/"
fi

echo ""
echo "=== FINAL COMMANDS TO RUN ==="
echo "Now run these commands as the web server user:"
echo "sudo -u $WEB_USER php artisan config:clear"
echo "sudo -u $WEB_USER php artisan view:clear"
echo "sudo -u $WEB_USER php artisan cache:clear"
echo "sudo -u $WEB_USER php artisan route:clear"

echo ""
echo "If the problem persists, check:"
echo "1. SELinux: sestatus"
echo "2. AppArmor: aa-status"
echo "3. Disk space: df -h"
echo "4. File system: mount | grep $(df . | tail -1 | awk '{print $1}')"

echo ""
echo "EMERGENCY FIX COMPLETED!"
