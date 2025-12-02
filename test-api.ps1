# API Test Script for Book Now Events (PowerShell)
# This script directly tests the API endpoint and shows the raw response

Write-Host "🧪 Testing Book Now Events API..." -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

$apiUrl = "https://admin.tfcmockup.com/api/public/events/book-now"

Write-Host "📡 API Endpoint: $apiUrl" -ForegroundColor White
Write-Host "🕐 Test Time: $(Get-Date)" -ForegroundColor White
Write-Host ""

Write-Host "📋 Raw API Response:" -ForegroundColor Yellow
Write-Host "====================" -ForegroundColor Yellow

try {
    # Test the API endpoint
    $response = Invoke-WebRequest -Uri $apiUrl -Method GET -UseBasicParsing
    
    Write-Host "✅ HTTP Status Code: $($response.StatusCode)" -ForegroundColor Green
    Write-Host ""
    
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ API Response (Success):" -ForegroundColor Green
        
        # Try to format JSON nicely
        try {
            $jsonContent = $response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
            Write-Host $jsonContent
        }
        catch {
            # If JSON parsing fails, show raw content
            Write-Host $response.Content
        }
    }
    else {
        Write-Host "❌ API Error (HTTP $($response.StatusCode)):" -ForegroundColor Red
        Write-Host $response.Content
    }
}
catch {
    Write-Host "❌ Error connecting to API:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}

Write-Host ""
Write-Host "🔍 What to check in the response:" -ForegroundColor Magenta
Write-Host "=================================" -ForegroundColor Magenta
Write-Host "1. 'success': Should be true"
Write-Host "2. 'data': Array of events with these fields:"
Write-Host "   - id, title, description, location"
Write-Host "   - event_date, event_date_formatted, event_time_formatted"
Write-Host "   - image_url (should be full URL starting with https://)"
Write-Host "   - price, price_display, category"
Write-Host "   - is_booking_open, slug"
Write-Host "   - ticket_pricing (with base_price and countries_available)"
Write-Host "3. 'total': Number of events returned"
Write-Host "4. 'available_categories': Array of event categories"
Write-Host ""
Write-Host "🐛 Troubleshooting:" -ForegroundColor Yellow
Write-Host "=================="
Write-Host "- If HTTP 404: Check if Laravel routes are working"
Write-Host "- If HTTP 500: Check Laravel logs for errors" 
Write-Host "- If CORS error: Check browser console (not visible in this test)"
Write-Host "- If images don't load: Check storage symlink and file paths"
Write-Host ""
Write-Host "💡 To run React test page: Visit /api-test in your browser" -ForegroundColor Cyan