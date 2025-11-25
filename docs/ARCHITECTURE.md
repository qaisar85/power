# Platform Architecture Documentation

## Overview
Global Equipment & Services Platform - A comprehensive B2B marketplace operating in 200+ countries with multiple service modules.

## Current Tech Stack
- **Framework**: Laravel 12.x
- **Frontend**: Inertia.js + Vue.js/React
- **Authentication**: Laravel Sanctum + Jetstream
- **Permissions**: Spatie Laravel Permission
- **Payments**: Stripe, PayPal, Laravel Cashier
- **PDF Generation**: DomPDF
- **Search**: Laravel Scout
- **Notifications**: Twilio integration

## Application Structure

### 1. Core Modules Implemented
- ✅ Marketplace (Equipment/Products)
- ✅ Jobs & Recruitment
- ✅ Tenders & Procurement
- ✅ Auctions
- ✅ Business for Sale
- ✅ Freelance Services & Projects
- ✅ Drilling Services
- ✅ Logistics & Transportation
- ✅ Stock/Investment Platform

### 2. Key Models & Database Structure

#### Users & Companies
```
users
├── id, name, email, password
├── user_type (individual, company, regional_agent)
├── user_package
├── phone, country, timezone
├── is_active
└── Relationships: companies, modules, wallet

companies
├── id, user_id, name, slug
├── description, website, email, phone
├── address (json), logo, size
├── sectors (json)
├── is_verified, is_active
├── plan_id, plan_expires_at
└── Relationships: services, documents, routes, reviews
```

#### Listings System
```
listings
├── id, user_id, role
├── type (product, service, vacancy, news, tender, auction)
├── title, description
├── photos (json), documents (json)
├── price, currency
├── status (draft, under_review, published, rejected)
├── location, category, subcategories (json)
├── deal_type (sale, rent, auction)
├── payment_options (json)
├── publish_in_rent, publish_in_auction
├── rent_fields (json), auction_fields (json)
├── logistics_fields (json), product_fields (json)
├── business_fields (json)
├── preview_comment, package
└── Relationships: user, bids, requests
```

#### Packages & Monetization
```
packages
├── id, name, slug, description
├── price, currency
├── duration_days
├── features (json)
└── is_active

user_packages
├── id, user_id, package_id
├── starts_at, expires_at
├── is_active, auto_renew
└── payment_method

wallets
├── id, user_id
├── balance, currency
└── is_active

wallet_transactions
├── id, wallet_id
├── type (credit, debit)
├── amount, currency
├── description, reference
└── status
```

#### Geographic Support
```
countries
├── id, name, code, iso2, iso3
├── phone_code, currency
├── is_active, sort_order
└── Relationships: states, cities

states
├── id, country_id, name, code
└── is_active

cities
├── id, state_id, country_id, name
└── is_active
```

#### Admin & Moderation
```
admins
├── id, name, email, password
├── is_super_admin
└── Relationships: permissions, roles

moderation_tasks
├── id, listing_id, admin_id
├── status (pending, in_review, approved, declined)
├── comments, assigned_at, reviewed_at
└── Relationships: listing, admin

admin_action_logs
├── id, admin_id
├── action, description
├── ip_address
└── Relationships: admin
```

### 3. Missing/Recommended Enhancements

#### A. Regional Agent System (NEW)
```php
// Migration needed
regional_agents table:
├── id, user_id
├── region_type (country, state, city)
├── country_id, state_id, city_id
├── commission_rate (decimal)
├── is_verified, is_active
├── service_types (json) // What services they offer
├── performance_rating
└── total_services_completed

agent_services table:
├── id, agent_id, company_id
├── service_type
├── price, currency
├── status
├── commission_amount
└── completed_at
```

#### B. Revenue Sharing System (NEW)
```php
revenue_shares table:
├── id, listing_id, user_id
├── share_type (demo, commission)
├── percentage
├── amount_earned
├── status (pending, paid)
└── payment_date

sales_transactions table:
├── id, listing_id, buyer_id, seller_id
├── agent_id (nullable)
├── amount, currency
├── platform_fee, agent_commission, seller_amount
├── payment_status
└── payment_method
```

#### C. Multi-Currency Enhancement
```php
// Existing: currency_rates table
// Enhancement needed:
├── Add live API integration (e.g., ExchangeRate-API)
├── Add automatic rate updates
├── Add currency conversion helpers
└── Add user preferred currency setting
```

#### D. Analytics & Reporting
```php
platform_analytics table:
├── id, date
├── total_listings, active_listings
├── total_users, new_users
├── revenue_generated, revenue_pending
├── top_countries (json)
├── top_categories (json)
└── performance_metrics (json)

user_analytics table:
├── id, user_id, date
├── views, clicks, inquiries
├── conversions, revenue
└── engagement_score
```

## API Structure for Mobile Apps

### Authentication Endpoints
```
POST /api/auth/login
POST /api/auth/register
POST /api/auth/refresh
POST /api/auth/logout
POST /api/auth/verify-token
```

### Core API Endpoints (Already Implemented)
```
GET  /api/user (profile)
GET  /api/modules (available modules)
GET  /api/sectors (business sectors)
GET  /api/sectors/{code}
POST /api/notify
```

### Recommended Mobile API Additions
```
# Listings
GET    /api/v1/listings
GET    /api/v1/listings/{id}
POST   /api/v1/listings
PUT    /api/v1/listings/{id}
DELETE /api/v1/listings/{id}

# Search & Filters
GET    /api/v1/listings/search
GET    /api/v1/listings/filters

# Packages
GET    /api/v1/packages
POST   /api/v1/packages/subscribe

# Wallet
GET    /api/v1/wallet/balance
POST   /api/v1/wallet/topup
GET    /api/v1/wallet/transactions

# Regional Agents
GET    /api/v1/agents
GET    /api/v1/agents/{id}
POST   /api/v1/agents/request-service

# Favorites
GET    /api/v1/favorites
POST   /api/v1/favorites/{listing_id}
DELETE /api/v1/favorites/{listing_id}

# Notifications
GET    /api/v1/notifications
POST   /api/v1/notifications/{id}/read
```

## Payment Integration Architecture

### Current Implementation
- ✅ Stripe Checkout for wallet topup
- ✅ PayPal for wallet topup
- ✅ Stripe webhooks for payment confirmation
- ✅ PayPal webhooks
- ✅ Manual payment requests

### Recommended Enhancements

#### 1. Package Purchase Flow
```
User selects package → Payment gateway → Webhook confirms → 
→ Activate package → Deduct from wallet or charge card
```

#### 2. Revenue Sharing Flow
```
Sale completed → Calculate splits:
├── Platform fee (fixed %)
├── Regional agent commission (if applicable)
└── Seller amount (remainder)
→ Distribute to respective wallets
→ Log transaction for all parties
```

#### 3. Multi-Payment Method Support
- Credit/Debit Cards (Stripe)
- PayPal
- Bank Transfer (manual approval)
- Cryptocurrency (future)
- Local payment methods per country (Yookassa, etc.)

## Multi-Language Support

### Current Setup
- ✅ Basic i18n with Laravel localization
- ✅ Supported: en, ru, ar, fr
- ✅ Session-based locale switching
- ✅ Translation files in /lang directory

### Enhancements Needed
1. Add 20+ major languages (es, de, zh, hi, pt, ja, ko, it, etc.)
2. Database-driven translations for dynamic content
3. RTL support enhancement for Arabic/Hebrew
4. Auto-detect user language from IP/browser
5. Per-user language preference storage
6. Translation management interface for admins

## Security & Performance

### Implemented
- ✅ Sanctum API authentication
- ✅ Two-factor authentication (Jetstream)
- ✅ Role-based permissions (Spatie)
- ✅ CSRF protection
- ✅ Query caching (listings, categories, countries)

### Recommended
- [ ] Rate limiting for API endpoints
- [ ] Redis for session/cache management
- [ ] CDN integration for static assets
- [ ] Database query optimization & indexing
- [ ] Image optimization & lazy loading
- [ ] API versioning (v1, v2)
- [ ] Monitoring & logging (Sentry, Telescope)

## Scalability Considerations

### Database
- Implement read replicas for high traffic
- Partition large tables (listings, transactions)
- Use Redis for caching frequently accessed data
- Consider separate databases per region

### Application
- Horizontal scaling with load balancer
- Queue workers for background jobs
- Microservices architecture for specific modules
- Separate admin panel into subdomain

### Storage
- Use S3/equivalent for file storage
- Implement CDN for images and assets
- Compress and optimize all uploads

## Deployment Architecture
```
                    [Load Balancer]
                          |
        +-----------------+-----------------+
        |                 |                 |
   [Web Server 1]   [Web Server 2]   [Web Server 3]
        |                 |                 |
        +-----------------+-----------------+
                          |
                   [Database Cluster]
                    (Master + Replicas)
                          |
        +-----------------+-----------------+
        |                 |                 |
    [Redis Cache]    [Queue Workers]    [File Storage]
```

## Next Steps Priority

### Phase 1: Core Enhancements (Week 1-2)
1. ✅ Implement Regional Agent system (models, migrations, controllers)
2. ✅ Add Revenue Sharing functionality
3. ✅ Enhance Multi-currency support
4. ✅ Create comprehensive API for mobile apps

### Phase 2: Features & Polish (Week 3-4)
5. ✅ Add Analytics dashboard
6. ✅ Implement advanced search & filters
7. ✅ Add bulk upload functionality
8. ✅ Create email notification system

### Phase 3: Scale & Deploy (Week 5-6)
9. ✅ Performance optimization
10. ✅ Security audit
11. ✅ Load testing
12. ✅ Production deployment setup
