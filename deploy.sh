#!/bin/bash
# Deploy script for Shift Timeline
# Copies repository files to web server

SOURCE="/export/opc_home/jkrekeler/Github/shiftTimelines/"
DEST="/export/opc_home/jkrekeler/git/ocean-weather-gov/www/htdocs/jason/timeline/"

echo "🚀 Deploying Shift Timeline..."
echo "   From: $SOURCE"
echo "   To:   $DEST"
echo ""

rsync -av --exclude='.git' --exclude='*.backup.*' "$SOURCE" "$DEST"

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Deployment complete!"
else
    echo ""
    echo "❌ Deployment failed!"
    exit 1
fi
