#!/bin/bash

# API Test Script for Book Now Events
# This script directly tests the API endpoint and shows the raw response

echo "🧪 Testing Book Now Events API..."
echo "=================================="
echo ""

API_URL="https://admin.tfcmockup.com/api/public/events/book-now"

echo "📡 API Endpoint: $API_URL"
echo "🕐 Test Time: $(date)"
echo ""

echo "📋 Raw API Response:"
echo "===================="

# Test the API endpoint
if command -v curl &> /dev/null; then
    echo "Using curl to fetch data..."
    echo ""
    
    response=$(curl -s -w "\n%{http_code}" "$API_URL")
    http_code=$(echo "$response" | tail -n1)
    content=$(echo "$response" | head -n -1)
    
    echo "HTTP Status Code: $http_code"
    echo ""
    
    if [ "$http_code" = "200" ]; then
        echo "✅ API Response (Success):"
        echo "$content" | python3 -m json.tool 2>/dev/null || echo "$content"
    else
        echo "❌ API Error (HTTP $http_code):"
        echo "$content"
    fi
    
elif command -v wget &> /dev/null; then
    echo "Using wget to fetch data..."
    echo ""
    
    wget -q -O - "$API_URL" | python3 -m json.tool 2>/dev/null || wget -q -O - "$API_URL"
    
else
    echo "❌ Neither curl nor wget is available"
    echo "Please install curl or wget to test the API"
fi

echo ""
echo "🔍 What to check in the response:"
echo "================================="
echo "1. 'success': Should be true"
echo "2. 'data': Array of events with these fields:"
echo "   - id, title, description, location"
echo "   - event_date, event_date_formatted, event_time_formatted"
echo "   - image_url (should be full URL starting with https://)"
echo "   - price, price_display, category"
echo "   - is_booking_open, slug"
echo "   - ticket_pricing (with base_price and countries_available)"
echo "3. 'total': Number of events returned"
echo "4. 'available_categories': Array of event categories"
echo ""
echo "🐛 Troubleshooting:"
echo "=================="
echo "- If HTTP 404: Check if Laravel routes are working"
echo "- If HTTP 500: Check Laravel logs for errors"
echo "- If CORS error: Check browser console (not visible in this test)"
echo "- If images don't load: Check storage symlink and file paths"