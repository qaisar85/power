# World Oil Portal - Complete Implementation Specification

## Project Overview

**Main Domain**: worldoilportal.com  
**Demo**: worldoilportal.online  
**Current Laravel Project**: D:\Laravel\Power

## 18 Categories Structure

### Priority 1 (GREEN) - Core Business Functions
1. **Home** - Landing page and dashboard
2. **Sale** - Equipment for sale
3. **Rent** - Equipment rental
4. **Inspection Company** - Equipment inspection services
5. **Oil Drilling Company** - Drilling services and equipment
6. **Logistics Company** - Transportation and shipping
7. **Tenders** - Procurement and tender management
8. **Branch (Regional Managers)** - Regional agent services
9. **Contacts** - Contact management and communication

### Priority 2 (YELLOW) - Additional Features
10. **Business for Sale** - Complete business listings
11. **Auction** - Bidding system for equipment
12. **Stocks** - Share trading platform
13. **Investment** - Investment opportunities
14. **Job** - Employment listings
15. **Freelancer** - Freelance marketplace
16. **News** - Industry news and updates
17. **Partnership** - Partnership opportunities
18. **Forum** - Community discussion

---

## Database Schema Design

### Core Tables

#### 1. Users Table (Enhanced)
```sql
users
├── id
├── name
├── email (unique)
├── phone (unique, with country code)
├── password
├── email_verified_at
├── phone_verified_at
├── oauth_provider (google, linkedin, facebook)
├── oauth_id
├── two_factor_secret
├── two_factor_recovery_codes
├── primary_role_id (FK to roles)
├── is_active
├── last_login_at
└── timestamps
```

#### 2. Roles Table
```sql
roles
├── id
├── name (Company, Drilling, Inspection, Logistics, Journalist, Branch, Investor, Freelancer, Admin)
├── slug
├── description
├── requires_verification
├── is_active
└── timestamps
```

#### 3. User Roles (Pivot)
```sql
user_roles
├── id
├── user_id (FK)
├── role_id (FK)
├── is_primary
├── verification_status (pending, verified, rejected)
├── verified_at
├── verified_by (FK to admins)
├── verification_documents (JSON)
└── timestamps
```

#### 4. Categories Table
```sql
categories
├── id
├── name
├── slug
├── priority (1=green, 2=yellow)
├── parent_id (nullable, for subcategories)
├── icon
├── description
├── sort_order
├── is_active
└── timestamps
```

#### 5. Listings Table (Universal)
```sql
listings
├── id
├── user_id (FK)
├── category_id (FK)
├── type (sale, rent, service, tender, job, news, auction, etc.)
├── title
├── slug
├── description
├── specifications (JSON)
├── photos (JSON array)
├── documents (JSON array)
├── price
├── currency (USD, EUR, etc.)
├── location (JSON: country, city, address)
├── status (draft, pending, published, sold, expired, rejected)
├── deal_type (sale, rent, lease, auction)
├── payment_options (JSON)
├── publish_in_rent (boolean)
├── publish_in_auction (boolean)
├── rent_fields (JSON)
├── auction_fields (JSON)
├── package_id (FK)
├── expires_at
├── published_at
├── contacts_visibility (visible, hidden, paid_only)
├── view_count
├── inquiry_count
└── timestamps, soft_deletes
```

#### 6. Listing Custom Fields (Dynamic)
```sql
listing_fields
├── id
├── listing_id (FK)
├── field_name
├── field_value (JSON)
└── timestamps
```

#### 7. Packages Table
```sql
packages
├── id
├── name (Test, Package 1-10, VIP, Regional Manager Services)
├── slug
├── description
├── price
├── currency
├── duration_days
├── listing_limit (number of listings allowed)
├── features (JSON)
├── category_restrictions (JSON - which categories allowed)
├── is_active
├── sort_order
└── timestamps
```

#### 8. User Packages (Subscriptions)
```sql
user_packages
├── id
├── user_id (FK)
├── package_id (FK)
├── starts_at
├── expires_at
├── listings_used
├── listings_remaining
├── is_active
├── auto_renew
├── payment_reference
└── timestamps
```

#### 9. Wallets Table (Already exists)
```sql
wallets
├── id
├── user_id (FK)
├── balance
├── currency
├── is_active
└── timestamps
```

#### 10. Wallet Transactions (Enhanced)
```sql
wallet_transactions
├── id
├── wallet_id (FK)
├── type (credit, debit, deposit, withdrawal, payment, refund)
├── amount
├── currency
├── description
├── reference (order_id, listing_id, etc.)
├── reference_type (listing_payment, package_purchase, deposit, etc.)
├── status (pending, completed, failed, refunded)
├── payment_method (card, bank_transfer, etc.)
├── payment_provider (stripe, paypal, etc.)
└── timestamps
```

#### 11. Auction Bids (Enhanced)
```sql
auction_bids
├── id
├── listing_id (FK)
├── user_id (FK)
├── amount
├── currency
├── deposit_amount
├── deposit_paid (boolean)
├── deposit_transaction_id (FK to wallet_transactions)
├── status (active, outbid, won, cancelled)
├── bid_time
└── timestamps
```

#### 12. Auction Deposits
```sql
auction_deposits
├── id
├── auction_id (listing_id FK)
├── user_id (FK)
├── amount
├── status (held, released, forfeited)
├── transaction_id (FK)
└── timestamps
```

#### 13. Inspection Orders
```sql
inspection_orders
├── id
├── listing_id (FK)
├── requester_id (user_id FK)
├── inspector_company_id (FK to companies with Inspection role)
├── status (requested, accepted, in_progress, completed, cancelled)
├── inspection_date
├── inspection_report (JSON)
├── report_documents (JSON)
├── price
├── payment_status
└── timestamps
```

#### 14. Logistics Quotes
```sql
logistics_quotes
├── id
├── listing_id (FK)
├── requester_id (FK)
├── logistics_company_id (FK)
├── from_location (JSON)
├── to_location (JSON)
├── cargo_details (JSON)
├── quoted_price
├── currency
├── estimated_days
├── status (requested, quoted, accepted, completed)
└── timestamps
```

#### 15. Tender Applications
```sql
tender_applications
├── id
├── tender_id (listing_id FK)
├── applicant_id (user_id FK)
├── company_id (FK)
├── proposal_document (JSON)
├── quoted_price
├── currency
├── estimated_completion_days
├── status (submitted, under_review, shortlisted, accepted, rejected)
├── submitted_at
└── timestamps
```

#### 16. Listing Inquiries
```sql
listing_inquiries
├── id
├── listing_id (FK)
├── inquirer_id (FK)
├── message
├── contact_phone
├── contact_email
├── status (new, replied, closed)
├── replied_at
└── timestamps
```

#### 17. Subscriptions (Notifications)
```sql
user_subscriptions
├── id
├── user_id (FK)
├── subscription_type (category, search, company)
├── subscription_data (JSON: categories, keywords, etc.)
├── notification_channels (JSON: email, sms, whatsapp)
├── language
├── is_active
└── timestamps
```

#### 18. Jobs Table
```sql
jobs
├── id
├── company_id (FK)
├── user_id (FK)
├── title
├── description
├── requirements (JSON)
├── location
├── job_type (full_time, part_time, contract, remote)
├── salary_min
├── salary_max
├── currency
├── category
├── status (open, closed, filled)
├── expires_at
└── timestamps
```

#### 19. Job Applications
```sql
job_applications
├── id
├── job_id (FK)
├── applicant_id (FK)
├── cover_letter
├── resume_url
├── expected_salary
├── status (submitted, reviewing, shortlisted, accepted, rejected)
├── applied_at
└── timestamps
```

#### 20. Stocks/Shares (New)
```sql
company_shares
├── id
├── company_id (FK)
├── total_shares
├── available_shares
├── price_per_share
├── currency
├── minimum_investment
├── is_active
└── timestamps
```

#### 21. Share Transactions (Already exists)
```sql
share_transactions
├── id
├── company_share_id (FK)
├── buyer_id (FK)
├── seller_id (FK, nullable)
├── shares_amount
├── price_per_share
├── total_amount
├── transaction_status
└── timestamps
```

#### 22. Freelance Services (Already exists)
```sql
freelance_services
├── id
├── freelancer_id (FK)
├── title
├── description
├── category
├── price
├── currency
├── delivery_time_days
├── status
└── timestamps
```

#### 23. Freelance Projects (Already exists)
```sql
freelance_projects
├── id
├── client_id (FK)
├── title
├── description
├── budget
├── currency
├── deadline
├── status
└── timestamps
```

#### 24. News Articles
```sql
news_articles
├── id
├── author_id (FK)
├── title
├── slug
├── content (text)
├── featured_image
├── category
├── tags (JSON)
├── is_featured
├── is_paid
├── views_count
├── published_at
└── timestamps
```

#### 25. Forum Categories
```sql
forum_categories
├── id
├── name
├── slug
├── description
├── sort_order
└── timestamps
```

#### 26. Forum Topics
```sql
forum_topics
├── id
├── category_id (FK)
├── user_id (FK)
├── title
├── slug
├── is_pinned
├── is_locked
├── views_count
├── replies_count
└── timestamps
```

#### 27. Forum Posts
```sql
forum_posts
├── id
├── topic_id (FK)
├── user_id (FK)
├── content
├── is_solution
└── timestamps
```

---

## User Roles and Permissions

### Role Definitions

1. **Company** (Equipment Seller/Renter)
   - Create listings (sale/rent)
   - Manage products
   - View inquiries
   - Participate in auctions (buyer)
   - Access dashboard

2. **Drilling Company**
   - All Company permissions
   - Create drilling service listings
   - Manage fleet (rigs)
   - Upload HSE documents
   - Create portfolio cases

3. **Inspection Company**
   - Receive inspection requests
   - Upload inspection reports
   - Manage certifications
   - Set pricing

4. **Logistics Company**
   - Receive shipping quotes
   - Manage routes
   - Provide cost estimates
   - Track shipments

5. **Journalist**
   - Publish news articles
   - Manage content
   - View analytics

6. **Branch (Regional Manager/Agent)**
   - Provide regional services
   - Earn commissions
   - Manage service area
   - View performance metrics

7. **Investor**
   - Browse investment opportunities
   - Purchase shares
   - View portfolio
   - Receive dividends

8. **Freelancer**
   - Create service offerings
   - Apply to projects
   - Manage orders
   - Receive payments

9. **Admin**
   - Full system access
   - User moderation
   - Content moderation
   - Financial management
   - Role assignment

---

## Key Features Implementation

### 1. Registration & Authentication

**OAuth Integration:**
```php
// config/services.php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URL'),
],
'linkedin' => [...],
'facebook' => [...],
```

**2FA Implementation:**
- Use existing Laravel Jetstream 2FA
- Add SMS/WhatsApp verification via Twilio

### 2. Universal Dashboard Structure

**Components:**
```
Dashboard/
├── Profile
│   ├── Personal Info
│   ├── Password Change
│   └── 2FA Settings
├── Finance
│   ├── Wallet Balance
│   ├── Deposit/Withdrawal
│   ├── Transaction History
│   └── Packages
├── Notifications
│   ├── Email Settings
│   ├── SMS/WhatsApp Settings
│   └── Notification History
├── Messages
│   ├── Inbox
│   ├── Sent
│   └── Support Chat
├── My Content
│   ├── Listings
│   ├── Applications
│   └── Favorites
└── Role-Specific Sections
    ├── (Dynamically loaded based on role)
```

### 3. Post Advertisement Wizard

**Steps:**
1. Select Role (if not selected)
2. Select Category
3. Select Type (Sale/Rent/Service/etc.)
4. **Cross-posting Options:**
   - ☐ Also publish in Rent section
   - ☐ Also publish in Auction section
5. Fill Dynamic Fields
6. Upload Photos/Documents
7. Set Pricing
8. Choose Package
9. Preview & Publish

**Dynamic Fields by Type:**

**Sale/Rent Equipment:**
- Photos (up to 10)
- Title
- Description
- Category
- Specifications
- Price/Rental rate
- Location
- Condition
- Year of manufacture
- Documents (certificates, manuals)

**Additional for Rent:**
- Rental period (daily/weekly/monthly)
- Minimum rental period
- Security deposit
- Availability calendar

**Additional for Auction:**
- Starting bid
- Reserve price
- Auction duration
- Deposit amount
- Buy Now price (optional)

### 4. Package System

**Package Types:**
```php
packages:
- Test Package (Free, 1 listing, 7 days)
- Basic Package ($99, 10 listings, 30 days)
- Standard Package ($299, 50 listings, 30 days)
- Premium Package ($599, 200 listings, 30 days)
- VIP Package ($1499, Unlimited, 90 days)
- Regional Manager Services (Custom pricing)
```

**Features Matrix:**
| Feature | Test | Basic | Standard | Premium | VIP |
|---------|------|-------|----------|---------|-----|
| Listings | 1 | 10 | 50 | 200 | ∞ |
| Duration | 7d | 30d | 30d | 30d | 90d |
| Featured | No | No | 5 | 20 | All |
| Priority | No | No | Yes | Yes | Yes |
| Analytics | Basic | Basic | Advanced | Advanced | Premium |
| Support | Email | Email | Priority | Priority | Dedicated |

### 5. Auction System

**Bidding Flow:**
```
1. User views auction
2. User deposits % of bid amount
3. User places bid
4. System validates bid > current + increment
5. Previous bidder gets outbid notification
6. Timer extends if bid in last 5 minutes
7. Auction ends → Winner notification
8. Winner pays full amount
9. Deposits refunded to non-winners
```

**Auction Fields:**
```php
auction_fields: {
    start_price: decimal,
    reserve_price: decimal,
    current_bid: decimal,
    bid_increment: decimal,
    deposit_percentage: integer (default 10%),
    start_time: datetime,
    end_time: datetime,
    buy_now_price: decimal (optional),
    auto_extend: boolean,
    extend_minutes: integer
}
```

---

## API Endpoints Structure

### Public Endpoints
```
GET  /api/v1/public/categories
GET  /api/v1/public/listings?category={id}&type={sale|rent|auction}
GET  /api/v1/public/listings/{id}
GET  /api/v1/public/companies
GET  /api/v1/public/news
```

### Authenticated Endpoints

**Dashboard:**
```
GET  /api/v1/dashboard
GET  /api/v1/dashboard/stats
GET  /api/v1/notifications
POST /api/v1/notifications/{id}/read
```

**Listings:**
```
GET    /api/v1/listings
POST   /api/v1/listings
PUT    /api/v1/listings/{id}
DELETE /api/v1/listings/{id}
POST   /api/v1/listings/{id}/publish
```

**Auctions:**
```
GET  /api/v1/auctions
POST /api/v1/auctions/{id}/deposit
POST /api/v1/auctions/{id}/bid
POST /api/v1/auctions/{id}/buy-now
GET  /api/v1/auctions/my-bids
```

**Packages:**
```
GET  /api/v1/packages
POST /api/v1/packages/purchase
GET  /api/v1/packages/my-subscriptions
```

---

## Frontend Structure (Inertia.js)

```
resources/js/Pages/
├── Welcome.vue (Home)
├── Dashboard/
│   ├── Index.vue
│   ├── Profile.vue
│   ├── Finance.vue
│   └── Notifications.vue
├── Listings/
│   ├── Index.vue
│   ├── Create.vue (Wizard)
│   ├── Edit.vue
│   └── Show.vue
├── Auction/
│   ├── Index.vue
│   ├── Show.vue
│   └── MyBids.vue
├── Packages/
│   └── Index.vue
├── Roles/
│   └── Select.vue
└── Categories/
    ├── Sale.vue
    ├── Rent.vue
    ├── Tender.vue
    ├── Jobs.vue
    ├── News.vue
    └── Forum.vue
```

---

## Implementation Priority

### Phase 1: Foundation (Week 1-2)
- ✅ Enhanced user authentication with OAuth
- ✅ Role system with verification
- ✅ Universal dashboard framework
- ✅ Package system integration
- ✅ Wallet enhancements

### Phase 2: Core Categories (Week 3-4)
- Sale listings (Priority 1)
- Rent listings (Priority 1)
- Auction system (Priority 2)
- Tenders (Priority 1)
- Cross-posting functionality

### Phase 3: Service Providers (Week 5-6)
- Inspection company integration
- Logistics company integration
- Drilling company features
- Branch/Agent system (already implemented)

### Phase 4: Additional Features (Week 7-8)
- Jobs & Freelancer marketplace
- Stocks/Investment platform
- News & Forum
- Partnership section

### Phase 5: Polish & Launch (Week 9-10)
- Testing
- Performance optimization
- Documentation
- Deployment

---

## Next Steps

1. Run existing migrations
2. Create new migrations for missing tables
3. Implement role verification system
4. Build universal dashboard
5. Create listing wizard with cross-posting
6. Implement auction system
7. Add package restrictions and validation
8. Build category-specific pages
9. Test end-to-end flows
10. Deploy to production
