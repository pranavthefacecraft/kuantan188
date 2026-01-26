#!/bin/bash

# Billplz Sandbox Test Script
# Replace these with your actual credentials
API_KEY="your_api_key_here"
COLLECTION_ID="your_collection_id_here"
SANDBOX_URL="https://www.billplz-sandbox.com/api/v3"

echo "Testing Billplz Sandbox API..."
echo "================================"

echo -e "\n1. Testing API Authentication (Collections):"
curl -u $API_KEY: -X GET "$SANDBOX_URL/collections" \
  -H "Accept: application/json" \
  --silent --show-error | jq '.' || echo "Failed"

echo -e "\n2. Testing Collection Access:"
curl -u $API_KEY: -X GET "$SANDBOX_URL/collections/$COLLECTION_ID" \
  -H "Accept: application/json" \
  --silent --show-error | jq '.' || echo "Failed"

echo -e "\n3. Testing Laravel Billplz Service:"
curl -X GET "http://127.0.0.1:8000/api/public/payment/billplz/test" \
  -H "Accept: application/json" \
  --silent --show-error | jq '.' || echo "Failed"

echo -e "\n4. Creating Test Bill:"
curl -u $API_KEY: \
  -X POST \
  -d "collection_id=$COLLECTION_ID" \
  -d "description=Test Bill from Kuantan188" \
  -d "email=test@kuantan188.com" \
  -d "name=Test User" \
  -d "amount=100" \
  "$SANDBOX_URL/bills" \
  -H "Accept: application/json" \
  --silent --show-error | jq '.' || echo "Failed"

echo -e "\nTest completed!"