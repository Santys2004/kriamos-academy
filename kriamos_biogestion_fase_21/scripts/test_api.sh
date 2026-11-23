#!/usr/bin/env bash
set -e

BASE_URL="${1:-https://biogestion.kriamos.com.co}"
TOKEN="${2:-TOKEN_AQUI}"

echo "Probando API Kriamos BioGestión ICA"
echo "Base URL: $BASE_URL"

curl -s -H "Authorization: Bearer $TOKEN" "$BASE_URL/api/v1/health" | jq .
curl -s -H "Authorization: Bearer $TOKEN" "$BASE_URL/api/v1/granjas" | jq .
curl -s -H "Authorization: Bearer $TOKEN" "$BASE_URL/api/v1/bi" | jq .
