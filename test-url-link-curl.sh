#!/bin/bash

# Test script for URL Link field
# This demonstrates the correct usage of url_link vs image_url

echo "🧪 Testing Delivery Partners URL Link Field"
echo "============================================="

# Test 1: Create with url_link only (no image)
echo ""
echo "Test 1: Create with url_link only (should work)"
echo "-----------------------------------------------"
curl -X POST http://localhost:8000/api/admin/delivery-partners \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Partner 1",
    "description": "Partner with website link only",
    "url_link": "https://www.google.com"
  }' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s | jq '.' 2>/dev/null || cat

echo ""
echo "================================================="

# Test 2: Create with both url_link and image_url (using /url endpoint)
echo ""
echo "Test 2: Create with both url_link and image_url"
echo "-----------------------------------------------"
curl -X POST http://localhost:8000/api/admin/delivery-partners/url \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Partner 2", 
    "description": "Partner with both website and image URL",
    "url_link": "https://www.github.com",
    "image_url": "https://via.placeholder.com/300x200.png"
  }' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s | jq '.' 2>/dev/null || cat

echo ""
echo "================================================="

# Test 3: Wrong usage - trying to use image_url in regular endpoint
echo ""
echo "Test 3: Wrong usage - image_url in regular endpoint (should fail)"
echo "----------------------------------------------------------------"
curl -X POST http://localhost:8000/api/admin/delivery-partners \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Partner 3",
    "description": "Wrong usage example", 
    "url_link": "https://www.stackoverflow.com",
    "image_url": "https://via.placeholder.com/300x200.png"
  }' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s | jq '.' 2>/dev/null || cat

echo ""
echo "================================================="

# Test 4: Multipart form with url_link
echo ""
echo "Test 4: Multipart form with url_link (should work)"
echo "-------------------------------------------------"
curl -X POST http://localhost:8000/api/admin/delivery-partners \
  -F "title=Test Partner 4" \
  -F "description=Multipart form with website link" \
  -F "url_link=https://www.reddit.com" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s | jq '.' 2>/dev/null || cat

echo ""
echo "================================================="
echo "✅ Tests completed!"
echo ""
echo "📋 Summary:"
echo "- url_link: Partner's website (stored as text)"
echo "- image_url: Image to download (only for /url endpoints)"
echo "- Use /api/admin/delivery-partners for regular creation"
echo "- Use /api/admin/delivery-partners/url for image URL creation"
