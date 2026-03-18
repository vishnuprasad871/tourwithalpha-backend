# Tourwithalpha BookingCount - Admin REST API Documentation

## Overview

This module provides a complete REST API for managing offline bookings. All endpoints require admin authentication and proper ACL permissions.

## Authentication

All API endpoints require **admin authorization** using:
- Admin username/password (Basic Auth)
- OAuth tokens (for admin users)

## Base URL

```
https://yoursite.com/rest/V1/tourwithalpha/bookings
```

## API Endpoints

### 1. List All Bookings

**Endpoint:** `GET /rest/V1/tourwithalpha/bookings`

**Description:** Retrieve all offline bookings with filtering and pagination support.

**Headers:**
```
Authorization: Bearer {admin-token}
Content-Type: application/json
```

**Query Parameters:**
```
?searchCriteria[filterGroups][0][filters][0][field]=sku&searchCriteria[filterGroups][0][filters][0][value]=SKU-001&searchCriteria[filterGroups][0][filters][0][conditionType]=eq
?searchCriteria[pageSize]=10&searchCriteria[currentPage]=1
?searchCriteria[sortOrders][0][field]=created_at&searchCriteria[sortOrders][0][direction]=DESC
```

**cURL Example:**
```bash
curl -X GET "https://yoursite.com/rest/V1/tourwithalpha/bookings" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

**Response Success (200):**
```json
{
  "items": [
    {
      "id": 1,
      "sku": "TOUR-BASIC",
      "booking_date": "2026-07-16",
      "qty": 50,
      "notes": "Sold out",
      "created_at": "2026-03-18 10:30:00",
      "updated_at": "2026-03-18 10:30:00"
    }
  ],
  "search_criteria": {
    "filter_groups": [],
    "page_size": 20,
    "current_page": 1
  },
  "total_count": 1
}
```

---

### 2. Get Single Booking

**Endpoint:** `GET /rest/V1/tourwithalpha/bookings/{id}`

**Description:** Retrieve a specific booking by ID.

**cURL Example:**
```bash
curl -X GET "https://yoursite.com/rest/V1/tourwithalpha/bookings/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

**Response Success (200):**
```json
{
  "id": 1,
  "sku": "TOUR-BASIC",
  "booking_date": "2026-07-16",
  "qty": 50,
  "notes": "Sold out",
  "created_at": "2026-03-18 10:30:00",
  "updated_at": "2026-03-18 10:30:00"
}
```

**Response Error (404):**
```json
{
  "message": "The booking with ID 999 does not exist."
}
```

---

### 3. Create New Booking

**Endpoint:** `POST /rest/V1/tourwithalpha/bookings`

**Description:** Create a new offline booking.

**Request Body:**
```json
{
  "sku": "TOUR-BASIC",
  "booking_date": "2026-07-16",
  "qty": 50,
  "notes": "Sold out"
}
```

**cURL Example:**
```bash
curl -X POST "https://yoursite.com/rest/V1/tourwithalpha/bookings" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "TOUR-BASIC",
    "booking_date": "2026-07-16",
    "qty": 50,
    "notes": "Sold out"
  }'
```

**Response Success (201):**
```json
{
  "id": 1,
  "sku": "TOUR-BASIC",
  "booking_date": "2026-07-16",
  "qty": 50,
  "notes": "Sold out",
  "created_at": "2026-03-18 10:30:00",
  "updated_at": "2026-03-18 10:30:00"
}
```

**Response Error (400):**
```json
{
  "message": "Could not save booking: SKU is required"
}
```

**Required Fields:**
- `sku` (string, max 64 chars) - Product SKU
- `booking_date` (string, format: YYYY-MM-DD) - Booking date
- `qty` (integer, > 0) - Quantity

**Optional Fields:**
- `notes` (string) - Admin notes about the booking

---

### 4. Update Booking

**Endpoint:** `PUT /rest/V1/tourwithalpha/bookings/{id}`

**Description:** Update an existing booking.

**Request Body:**
```json
{
  "id": 1,
  "sku": "TOUR-BASIC",
  "booking_date": "2026-05-11",
  "qty": 2,
  "notes": "2 seats available"
}
```

**cURL Example:**
```bash
curl -X PUT "https://yoursite.com/rest/V1/tourwithalpha/bookings/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "id": 1,
    "sku": "TOUR-BASIC",
    "booking_date": "2026-05-11",
    "qty": 2,
    "notes": "2 seats available"
  }'
```

**Response Success (200):**
```json
{
  "id": 1,
  "sku": "TOUR-BASIC",
  "booking_date": "2026-05-11",
  "qty": 2,
  "notes": "2 seats available",
  "created_at": "2026-03-18 10:30:00",
  "updated_at": "2026-03-18 11:45:00"
}
```

---

### 5. Delete Booking

**Endpoint:** `DELETE /rest/V1/tourwithalpha/bookings/{id}`

**Description:** Delete a booking by ID.

**cURL Example:**
```bash
curl -X DELETE "https://yoursite.com/rest/V1/tourwithalpha/bookings/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

**Response Success (200):**
```json
{
  "message": "Booking deleted successfully"
}
```

**Response Error (404):**
```json
{
  "message": "The booking with ID 999 does not exist."
}
```

---

## Complete Usage Examples

### Insert July 16 Sold Out Record

```bash
curl -X POST "https://tourwithalpha.shop/rest/V1/tourwithalpha/bookings" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "TOUR-BASIC",
    "booking_date": "2026-07-16",
    "qty": 50,
    "notes": "Sold out"
  }'
```

### Insert May 11 - 2 Seats Available

```bash
curl -X POST "https://yoursite.com/rest/V1/tourwithalpha/bookings" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "TOUR-BASIC",
    "booking_date": "2026-05-11",
    "qty": 2,
    "notes": "2 seats available"
  }'
```

### Get All Bookings Filtered by SKU

```bash
curl -X GET "https://yoursite.com/rest/V1/tourwithalpha/bookings?searchCriteria[filterGroups][0][filters][0][field]=sku&searchCriteria[filterGroups][0][filters][0][value]=TOUR-BASIC&searchCriteria[filterGroups][0][filters][0][conditionType]=eq" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

### Update Booking

```bash
curl -X PUT "https://yoursite.com/rest/V1/tourwithalpha/bookings/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "id": 1,
    "sku": "TOUR-BASIC",
    "booking_date": "2026-07-16",
    "qty": 45,
    "notes": "Updated - 45 seats sold"
  }'
```

### Delete Booking

```bash
curl -X DELETE "https://yoursite.com/rest/V1/tourwithalpha/bookings/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Authorization & Security

### Required ACL Permission:
- `Tourwithalpha_BookingCount::offline_sales` - Required for all endpoints

### Admin Token Generation

**Get admin token:**
```bash
curl -X POST "https://yoursite.com/rest/V1/admin/token" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "admin_password"
  }'
```

Response:
```json
"admin_token_xyz..."
```

Use this token in all subsequent requests.

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "You do not have authorization to perform this operation."
}
```

### 403 Forbidden
```json
{
  "message": "You do not have the required permissions."
}
```

### 404 Not Found
```json
{
  "message": "The booking with ID {id} does not exist."
}
```

### 400 Bad Request
```json
{
  "message": "Could not save booking: {error_details}"
}
```

---

## Response Status Codes

| Status Code | Meaning |
|----------|---------|
| 200 | OK - Request succeeded |
| 201 | Created - Resource created |
| 400 | Bad Request - Invalid data |
| 401 | Unauthorized - Missing/invalid token |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource doesn't exist |
| 500 | Internal Server Error |

---

## Field Validation

### SKU
- Required
- String, max 64 characters
- Must be a valid product SKU

### Booking Date
- Required
- Format: YYYY-MM-DD
- Must be a valid date

### Quantity
- Required
- Integer, must be > 0
- No maximum limit

### Notes
- Optional
- String, up to 64KB
- For admin reference only

---

## Filtering Examples

### Filter by date range:
```
?searchCriteria[filterGroups][0][filters][0][field]=booking_date
&searchCriteria[filterGroups][0][filters][0][value]=2026-01-01
&searchCriteria[filterGroups][0][filters][0][conditionType]=gteq
```

### Sort by created date descending:
```
?searchCriteria[sortOrders][0][field]=created_at
&searchCriteria[sortOrders][0][direction]=DESC
```

### Pagination:
```
?searchCriteria[pageSize]=20&searchCriteria[currentPage]=1
```

---

## Integration Tips

1. Always store the admin token securely
2. Implement token refresh logic (tokens expire)
3. Handle all error responses gracefully
4. Log all API calls for audit purposes
5. Validate data on client-side before sending
6. Use proper content-type headers
7. Implement retry logic for network failures

---

## Files Created

- `etc/webapi.xml` - REST API route definitions
- `Api/OfflineSalesRepositoryInterface.php` - Repository interface
- `Api/Data/OfflineSalesInterface.php` - Data interface
- `Api/Data/OfflineSalesSearchResultsInterface.php` - Search results interface
- `Model/OfflineSalesRepository.php` - Repository implementation
- `Model/OfflineSalesSearchResults.php` - Search results implementation
