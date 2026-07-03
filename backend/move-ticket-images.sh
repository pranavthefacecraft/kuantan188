#!/bin/bash

# Script to move ticket images from old location to new location
# Run this on the production server

echo "=== Ticket Image Migration Script ==="
echo ""

# Define paths
OLD_PATH="public/storage/tickets"
NEW_PATH="public/uploads/tickets"

# Check if old directory exists
if [ ! -d "$OLD_PATH" ]; then
    echo "❌ Old directory $OLD_PATH does not exist"
    echo "Checking if images are already in correct location..."
    if [ -d "$NEW_PATH" ]; then
        echo "✅ New directory $NEW_PATH exists"
        ls -lah "$NEW_PATH"
    else
        echo "❌ New directory $NEW_PATH also does not exist. Creating it..."
        mkdir -p "$NEW_PATH"
        chmod 755 "$NEW_PATH"
        echo "✅ Created $NEW_PATH"
    fi
    exit 0
fi

# Check if new directory exists, create if not
if [ ! -d "$NEW_PATH" ]; then
    echo "Creating new directory: $NEW_PATH"
    mkdir -p "$NEW_PATH"
    chmod 755 "$NEW_PATH"
    echo "✅ Created $NEW_PATH"
fi

# Count files in old directory
FILE_COUNT=$(ls -1 "$OLD_PATH" 2>/dev/null | wc -l)
echo "Found $FILE_COUNT files in $OLD_PATH"

if [ "$FILE_COUNT" -eq 0 ]; then
    echo "⚠️  No files to move"
    exit 0
fi

# List files to be moved
echo ""
echo "Files to be moved:"
ls -lh "$OLD_PATH"

echo ""
read -p "Proceed with moving files? (y/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    # Move files (preserving originals as backup)
    echo "Copying files to new location..."
    cp -v "$OLD_PATH"/* "$NEW_PATH/" 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo "✅ Files copied successfully"
        
        # Set proper permissions
        chmod 644 "$NEW_PATH"/*
        echo "✅ Permissions set to 644"
        
        echo ""
        echo "Files in new location:"
        ls -lh "$NEW_PATH"
        
        echo ""
        echo "⚠️  Old files still exist in $OLD_PATH (kept as backup)"
        echo "You can manually delete them later with: rm -rf $OLD_PATH"
    else
        echo "❌ Error copying files"
        exit 1
    fi
else
    echo "Operation cancelled"
    exit 0
fi

echo ""
echo "=== Migration Complete ==="
