# Mobile API Documentation (V1)

## Base URL
```
Production: https://api.yourdomain.com/api/v1
Development: http://localhost:8000/api/v1
```

## Authentication

All authenticated endpoints require a Bearer token in the Authorization header:

```http
Authorization: Bearer {your_access_token}
```

### Obtain Token

**POST** `/api/auth/login`

```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

Response:
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  },
  "token": "1|abc123..."
}
```

---

## Listings

### Get User's Listings
**GET** `/api/v1/listings`

Headers: `Authorization: Bearer {token}`

Response:
```json
{
  "data": [
    {
      "id": 1,
      "title": "Industrial Generator 500KW",
      "type": "product",
      "price": 50000.00,
      "currency": "USD",
      "status": "published",
      "photos": ["url1", "url2"],
      "created_at": "2025-10-20T10:00:00Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Get Public Listings (Marketplace)
**GET** `/api/v1/public/listings`

Query Parameters:
- `type` - Filter by listing type (product, service, vacancy, etc.)
- `category` - Filter by category
- `location` - Filter by location (partial match)
- `min_price` - Minimum price
- `max_price` - Maximum price
- `currency` - Filter by currency (USD, EUR, etc.)
- `q` - Search query (searches title and description)
- `sort_by` - Sort field (default: created_at)
- `sort_order` - Sort order (asc/desc, default: desc)
- `per_page` - Results per page (default: 20)

Example:
```
GET /api/v1/public/listings?type=product&category=drilling&location=Dubai&min_price=1000&max_price=100000&currency=USD&q=generator&sort_by=price&sort_order=asc&per_page=10
```

### Create Listing
**POST** `/api/v1/listings`

Headers: `Authorization: Bearer {token}`

Body:
```json
{
  "type": "product",
  "title": "Industrial Generator 500KW",
  "description": "High-performance industrial generator...",
  "price": 50000.00,
  "currency": "USD",
  "location": "Dubai, UAE",
  "category": "power_generation",
  "subcategories": ["generators", "diesel"],
  "photos": ["https://...", "https://..."],
  "deal_type": "sale",
  "payment_options": ["cash", "bank_transfer", "crypto"]
}
```

### Update Listing
**PUT** `/api/v1/listings/{id}`

Headers: `Authorization: Bearer {token}`

Body: (same as create, but all fields optional)

### Publish Listing
**POST** `/api/v1/listings/{id}/publish`

Headers: `Authorization: Bearer {token}`

Response:
```json
{
  "message": "Listing submitted for review",
  "listing": {...}
}
```

### Delete Listing
**DELETE** `/api/v1/listings/{id}`

Headers: `Authorization: Bearer {token}`

---

## Search & Filters

### Search Listings
**GET** `/api/v1/search/listings`

Query Parameters:
- `q` - Search query
- All filters from public listings endpoint

### Get Available Filters
**GET** `/api/v1/search/filters`

Response:
```json
{
  "categories": ["drilling", "power_generation", "logistics", ...],
  "countries": ["UAE", "USA", "UK", ...],
  "currencies": ["USD", "EUR", "GBP", ...],
  "types": ["product", "service", "tender", "auction"]
}
```

### Get Search Suggestions
**GET** `/api/v1/search/suggestions?q=gener`

Response:
```json
{
  "suggestions": ["generator", "generation equipment", "general contractor"]
}
```

---

## Packages & Subscriptions

### List Packages
**GET** `/api/v1/packages`

Response:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Basic Package",
      "slug": "basic",
      "price": 99.00,
      "currency": "USD",
      "duration_days": 30,
      "features": ["10 listings", "Basic support", "Email notifications"]
    },
    {
      "id": 2,
      "name": "Premium Package",
      "slug": "premium",
      "price": 299.00,
      "currency": "USD",
      "duration_days": 30,
      "features": ["Unlimited listings", "Priority support", "Featured placement"]
    }
  ]
}
```

### Subscribe to Package
**POST** `/api/v1/packages/subscribe`

Headers: `Authorization: Bearer {token}`

Body:
```json
{
  "package_id": 2,
  "payment_method": "card",
  "auto_renew": true
}
```

### Get My Subscriptions
**GET** `/api/v1/packages/my-subscriptions`

Headers: `Authorization: Bearer {token}`

---

## Wallet

### Get Wallet Balance
**GET** `/api/v1/wallet/balance`

Headers: `Authorization: Bearer {token}`

Response:
```json
{
  "balance": 1500.00,
  "currency": "USD",
  "wallet_id": 1
}
```

### Get Wallet Transactions
**GET** `/api/v1/wallet/transactions`

Headers: `Authorization: Bearer {token}`

Query Parameters:
- `page` - Page number
- `per_page` - Results per page

Response:
```json
{
  "data": [
    {
      "id": 1,
      "type": "credit",
      "amount": 500.00,
      "description": "Wallet top-up",
      "status": "completed",
      "created_at": "2025-10-20T10:00:00Z"
    }
  ]
}
```

### Top Up Wallet
**POST** `/api/v1/wallet/topup`

Headers: `Authorization: Bearer {token}`

Body:
```json
{
  "amount": 500.00,
  "payment_method": "card",
  "currency": "USD"
}
```

---

## Regional Agents

### List Agents
**GET** `/api/v1/agents`

Headers: `Authorization: Bearer {token}`

Query Parameters:
- `country_id` - Filter by country
- `state_id` - Filter by state
- `city_id` - Filter by city
- `service_type` - Filter by service type
- `min_rating` - Minimum rating (0-5)

Response:
```json
{
  "data": [
    {
      "id": 1,
      "business_name": "Dubai Equipment Services",
      "region_coverage": "Dubai, UAE",
      "performance_rating": 4.8,
      "total_services_completed": 150,
      "service_types": ["listing_support", "verification", "logistics"],
      "commission_rate": 10.00,
      "languages": ["en", "ar"]
    }
  ]
}
```

### Get Agent Details
**GET** `/api/v1/agents/{id}`

Headers: `Authorization: Bearer {token}`

### Request Agent Service
**POST** `/api/v1/agents/{id}/request-service`

Headers: `Authorization: Bearer {token}`

Body:
```json
{
  "service_type": "listing_support",
  "description": "Need help listing my equipment",
  "listing_id": 123
}
```

### Get Agent Reviews
**GET** `/api/v1/agents/{id}/reviews`

### Submit Agent Review
**POST** `/api/v1/agents/{id}/review`

Headers: `Authorization: Bearer {token}`

Body:
```json
{
  "rating": 5,
  "comment": "Excellent service!",
  "communication_rating": 5,
  "professionalism_rating": 5,
  "response_time_rating": 5,
  "quality_rating": 5
}
```

---

## Favorites

### List Favorites
**GET** `/api/v1/favorites`

Headers: `Authorization: Bearer {token}`

### Add to Favorites
**POST** `/api/v1/favorites/{listing_id}`

Headers: `Authorization: Bearer {token}`

### Remove from Favorites
**DELETE** `/api/v1/favorites/{listing_id}`

Headers: `Authorization: Bearer {token}`

### Check if Favorited
**GET** `/api/v1/favorites/check/{listing_id}`

Headers: `Authorization: Bearer {token}`

Response:
```json
{
  "is_favorited": true
}
```

---

## Notifications

### List Notifications
**GET** `/api/v1/notifications`

Headers: `Authorization: Bearer {token}`

Response:
```json
{
  "data": [
    {
      "id": "abc-123",
      "type": "listing_approved",
      "data": {
        "message": "Your listing has been approved",
        "listing_id": 123
      },
      "read_at": null,
      "created_at": "2025-10-20T10:00:00Z"
    }
  ]
}
```

### Get Unread Count
**GET** `/api/v1/notifications/unread-count`

Headers: `Authorization: Bearer {token}`

Response:
```json
{
  "count": 5
}
```

### Mark as Read
**POST** `/api/v1/notifications/{id}/read`

Headers: `Authorization: Bearer {token}`

### Mark All as Read
**POST** `/api/v1/notifications/mark-all-read`

Headers: `Authorization: Bearer {token}`

---

## Companies

### List My Companies
**GET** `/api/v1/companies`

Headers: `Authorization: Bearer {token}`

### Create Company
**POST** `/api/v1/companies`

Headers: `Authorization: Bearer {token}`

Body:
```json
{
  "name": "ABC Equipment Ltd",
  "description": "Leading equipment provider in UAE",
  "website": "https://example.com",
  "email": "info@example.com",
  "phone": "+971-50-123-4567",
  "address": {
    "street": "123 Business Street",
    "city": "Dubai",
    "state": "Dubai",
    "country": "UAE",
    "postal_code": "12345"
  },
  "sectors": ["drilling", "power_generation"]
}
```

### Update Company
**PUT** `/api/v1/companies/{id}`

Headers: `Authorization: Bearer {token}`

### Request Verification
**POST** `/api/v1/companies/{id}/verify`

Headers: `Authorization: Bearer {token}`

---

## Tenders

### List Tenders
**GET** `/api/v1/tenders`

Headers: `Authorization: Bearer {token}`

Query Parameters:
- `status` - Filter by status (open, closed, awarded)
- `category` - Filter by category
- `location` - Filter by location

### Apply to Tender
**POST** `/api/v1/tenders/{id}/apply`

Headers: `Authorization: Bearer {token}`

Body:
```json
{
  "proposal": "Our company can deliver...",
  "documents": ["url1", "url2"],
  "price_quote": 150000.00
}
```

---

## Jobs

### List Jobs
**GET** `/api/v1/jobs`

Query Parameters:
- `category` - Job category
- `location` - Job location
- `type` - Job type (full-time, part-time, contract)

### Apply to Job
**POST** `/api/v1/jobs/{id}/apply`

Headers: `Authorization: Bearer {token}`

Body:
```json
{
  "cover_letter": "I am interested in this position...",
  "resume_url": "https://...",
  "expected_salary": 5000.00
}
```

---

## Auctions

### List Active Auctions
**GET** `/api/v1/auctions`

### Place Bid
**POST** `/api/v1/auctions/{listing_id}/bid`

Headers: `Authorization: Bearer {token}`

Body:
```json
{
  "amount": 55000.00,
  "currency": "USD"
}
```

### Get My Bids
**GET** `/api/v1/auctions/my-bids`

Headers: `Authorization: Bearer {token}`

---

## Error Responses

All endpoints follow standard HTTP status codes and return errors in this format:

```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

Common Status Codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## Rate Limiting

API requests are rate-limited to:
- **Authenticated users**: 300 requests per minute
- **Guest users**: 60 requests per minute

Rate limit headers are included in all responses:
```
X-RateLimit-Limit: 300
X-RateLimit-Remaining: 299
```

---

## Pagination

All list endpoints support pagination:

Query Parameters:
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 20, max: 100)

Response includes:
```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
```

---

## Webhooks (For Agent/Platform Integration)

Webhooks can be configured to receive real-time updates:

Events:
- `listing.published`
- `listing.sold`
- `agent.service_requested`
- `payment.completed`
- `tender.application_received`

Webhook payload example:
```json
{
  "event": "listing.published",
  "data": {
    "listing_id": 123,
    "user_id": 45,
    "published_at": "2025-10-20T10:00:00Z"
  },
  "timestamp": "2025-10-20T10:00:00Z"
}
```
