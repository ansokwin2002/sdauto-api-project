# Category Brand API Documentation

## Overview
The Category Brand API provides endpoints to manage category brands with fields for brand name, slug, and ordering. This system allows you to organize and display brands in a categorized manner with custom ordering and SEO-friendly URLs.

## Database Schema
The `category_brands` table contains the following fields:
- `id` (Primary Key)
- `brand` (String, required) - Brand name
- `slug` (String, unique) - SEO-friendly URL slug (auto-generated if not provided)
- `logo` (String, nullable) - Brand logo image path
- `ordering` (Integer, default: 0) - Display order (lower numbers appear first)
- `created_at` (Timestamp)
- `updated_at` (Timestamp)

## Key Features
- ✅ **Auto-slug generation** - Slugs are automatically created from brand names
- ✅ **Unique slug validation** - Ensures no duplicate slugs
- ✅ **Logo upload support** - Upload logos via file or remote URL
- ✅ **Ordering system** - Control display order with integer values
- ✅ **Public & Admin APIs** - Separate endpoints for frontend and management
- ✅ **Slug-based public access** - SEO-friendly URLs for public endpoints

## API Endpoints

### 🔓 Public Endpoints (Frontend Website)

#### Get All Category Brands
```
GET /api/public/category-brands
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "brand": "Toyota Parts",
            "slug": "toyota-parts",
            "logo": "/storage/category-brands/20251030_120000_abc123.png",
            "ordering": 1,
            "created_at": "2025-10-30T00:00:00.000000Z",
            "updated_at": "2025-10-30T00:00:00.000000Z"
        },
        {
            "id": 2,
            "brand": "Honda Parts",
            "slug": "honda-parts",
            "logo": "/storage/category-brands/20251030_120100_def456.jpg",
            "ordering": 2,
            "created_at": "2025-10-30T00:00:00.000000Z",
            "updated_at": "2025-10-30T00:00:00.000000Z"
        }
    ]
}
```

#### Get Single Category Brand by Slug
```
GET /api/public/category-brands/{slug}
```

**Example:** `GET /api/public/category-brands/toyota-parts`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "brand": "Toyota Parts",
        "slug": "toyota-parts",
        "logo": "/storage/category-brands/20251030_120000_abc123.png",
        "ordering": 1,
        "created_at": "2025-10-30T00:00:00.000000Z",
        "updated_at": "2025-10-30T00:00:00.000000Z"
    }
}
```

### 🔒 Admin Endpoints (Authentication Required)

#### Get All Category Brands
```
GET /api/admin/category-brands
Authorization: Bearer {token}
```

#### Create New Category Brand (JSON)
```
POST /api/admin/category-brands
Authorization: Bearer {token}
Content-Type: application/json

{
    "brand": "Toyota Parts",
    "slug": "toyota-parts",
    "ordering": 1
}
```

#### Create New Category Brand (with Logo Upload)
```
POST /api/admin/category-brands
Authorization: Bearer {token}
Content-Type: multipart/form-data

brand: Toyota Parts
slug: toyota-parts (optional)
logo: [file upload]
ordering: 1 (optional)
```

#### Create Category Brand from Logo URL
```
POST /api/admin/category-brands/url
Authorization: Bearer {token}
Content-Type: application/json

{
    "brand": "Toyota Parts",
    "slug": "toyota-parts",
    "logo_url": "https://example.com/logo.png",
    "ordering": 1
}
```

**Response:**
```json
{
    "success": true,
    "message": "Category brand created successfully",
    "data": {
        "id": 1,
        "brand": "Toyota Parts",
        "slug": "toyota-parts",
        "logo": "/storage/category-brands/20251030_120000_abc123.png",
        "ordering": 1,
        "created_at": "2025-10-30T00:00:00.000000Z",
        "updated_at": "2025-10-30T00:00:00.000000Z"
    }
}
```

#### Get Single Category Brand
```
GET /api/admin/category-brands/{id}
Authorization: Bearer {token}
```

#### Update Category Brand (JSON)
```
PUT /api/admin/category-brands/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "brand": "Updated Toyota Parts",
    "slug": "updated-toyota-parts",
    "ordering": 5
}
```

#### Update Category Brand (with Logo Upload)
```
PUT /api/admin/category-brands/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data

brand: Updated Toyota Parts
slug: updated-toyota-parts
logo: [file upload]
ordering: 5
```

#### Update Logo from URL
```
PATCH /api/admin/category-brands/{id}/url
Authorization: Bearer {token}
Content-Type: application/json

{
    "logo_url": "https://example.com/new-logo.png"
}
```

#### Update Only Ordering
```
PATCH /api/admin/category-brands/{id}/ordering
Authorization: Bearer {token}
Content-Type: application/json

{
    "ordering": 10
}
```

#### Delete Category Brand
```
DELETE /api/admin/category-brands/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Category brand deleted successfully"
}
```

## Validation Rules

### Create/Update Category Brand
- `brand`: Required, string, maximum 255 characters
- `slug`: Optional, string, maximum 255 characters, must match pattern `/^[a-z0-9]+(?:-[a-z0-9]+)*$/`, unique
- `logo`: Optional, image file (jpg, jpeg, png, webp, gif, svg), maximum 5MB
- `ordering`: Optional, integer, minimum 0

### Create from URL
- `brand`: Required, string, maximum 255 characters
- `slug`: Optional, string, maximum 255 characters, must match pattern `/^[a-z0-9]+(?:-[a-z0-9]+)*$/`, unique
- `logo_url`: Required, valid URL
- `ordering`: Optional, integer, minimum 0

### Update Logo from URL
- `logo_url`: Required, valid URL

### Update Ordering Only
- `ordering`: Required, integer, minimum 0

## Auto-Features

### Automatic Slug Generation
- If no slug is provided, it's automatically generated from the brand name
- Slugs are converted to lowercase and spaces/special characters become hyphens
- Duplicate slugs get a numeric suffix (e.g., "toyota-parts-2")

### Automatic Ordering
- If no ordering is provided during creation, it's set to `max(ordering) + 1`
- This ensures new items appear at the end by default

## Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "errors": {
        "brand": ["The brand field is required."],
        "slug": ["The slug has already been taken."]
    }
}
```

### Not Found Error (404)
```json
{
    "success": false,
    "message": "Category brand not found"
}
```

### Unauthorized Error (401)
```json
{
    "message": "Unauthorized."
}
```

## Usage Examples

### Frontend Integration
```javascript
// Get all category brands ordered by their ordering field
fetch('/api/public/category-brands')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.data.forEach(categoryBrand => {
                console.log(`${categoryBrand.brand} - ${categoryBrand.slug}`);
                if (categoryBrand.logo) {
                    console.log('Logo:', categoryBrand.logo);
                }
            });
        }
    });

// Get specific category brand by slug
fetch('/api/public/category-brands/toyota-parts')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Brand:', data.data.brand);
            console.log('Logo:', data.data.logo);
        }
    });
```

### Admin Panel Integration
```javascript
// Create new category brand
fetch('/api/admin/category-brands', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        brand: 'Nissan Parts',
        ordering: 3
        // slug will be auto-generated as 'nissan-parts'
    })
});

// Create with logo upload (multipart form)
const formData = new FormData();
formData.append('brand', 'BMW Parts');
formData.append('ordering', '4');
formData.append('logo', logoFile); // File input

fetch('/api/admin/category-brands', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token
    },
    body: formData
});

// Create from logo URL
fetch('/api/admin/category-brands/url', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        brand: 'Mercedes Parts',
        logo_url: 'https://example.com/mercedes-logo.png',
        ordering: 5
    })
});

// Update ordering only
fetch('/api/admin/category-brands/1/ordering', {
    method: 'PATCH',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        ordering: 10
    })
});
```

## Files Created/Modified

### New Files:
1. `database/migrations/2025_10_30_000001_create_category_brands_table.php` - Database migration
2. `database/migrations/2025_10_30_000002_add_logo_to_category_brands_table.php` - Logo column migration
3. `app/Models/CategoryBrand.php` - Eloquent model with auto-slug generation
4. `app/Http/Controllers/Api/CategoryBrandController.php` - API controller with logo upload support

### Modified Files:
1. `routes/api.php` - Added category brand routes

## Next Steps

1. **Run the migration:**
   ```bash
   php artisan migrate
   ```

2. **Create sample data:**
   ```bash
   php artisan tinker
   ```
   Then in tinker:
   ```php
   App\Models\CategoryBrand::create(['brand' => 'Toyota Parts', 'ordering' => 1]);
   App\Models\CategoryBrand::create(['brand' => 'Honda Parts', 'ordering' => 2]);
   App\Models\CategoryBrand::create(['brand' => 'Ford Parts', 'ordering' => 3]);
   ```

3. **Test the endpoints** using Postman or similar tool

The Category Brand API is now ready to use with full CRUD operations, automatic slug generation, and ordering capabilities!
