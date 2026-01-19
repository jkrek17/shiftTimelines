#!/bin/bash
# Deploy script for Shift Timeline
# Copies repository files to web server

SOURCE="/export/opc_home/jkrekeler/Github/shiftTimelines/"
DEST="/export/opc_home/jkrekeler/git/ocean-weather-gov/www/htdocs/jason/timeline/"

echo "🚀 Deploying Shift Timeline..."
echo "   From: $SOURCE"
echo "   To:   $DEST"
echo ""

rsync -av \
    --exclude='.git' \
    --exclude='*.backup.*' \
    --exclude='task_states.json' \
    "$SOURCE" "$DEST"

if [ $? -eq 0 ]; then
    echo ""
    echo "📁 Setting permissions..."
    
    # Set directory permissions (755 = rwxr-xr-x)
    find "$DEST" -type d -exec chmod 755 {} \;
    
    # Set file permissions (644 = rw-r--r--)
    find "$DEST" -type f -exec chmod 644 {} \;
    
    # Make config.js writable (for config editor saves)
    if [ -f "${DEST}config.js" ]; then
        chmod 666 "${DEST}config.js"
        echo "   config.js -> 666 (rw-rw-rw-)"
    fi
    
    # Make task_states.json writable (for saving task states)
    if [ -f "${DEST}task_states.json" ]; then
        chmod 666 "${DEST}task_states.json"
        echo "   task_states.json -> 666 (rw-rw-rw-)"
    fi
    
    echo "✅ Deployment complete!"
else
    echo ""
    echo "❌ Deployment failed!"
    exit 1
fi
