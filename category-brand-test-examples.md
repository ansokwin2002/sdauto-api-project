# Category Brand API Test Examples

## Test Data Creation

### Using Tinker (Recommended)
```bash
php artisan tinker
```

```php
// Create test category brands
App\Models\CategoryBrand::create([
    'brand' => 'Toyota Parts',
    'ordering' => 1
]);

App\Models\CategoryBrand::create([
    'brand' => 'Honda Parts', 
    'ordering' => 2
]);

App\Models\CategoryBrand::create([
    'brand' => 'Ford Parts',
    'ordering' => 3
]);

App\Models\CategoryBrand::create([
    'brand' => 'BMW Parts',
    'slug' => 'bmw-premium-parts', // Custom slug
    'logo' => '/storage/category-brands/bmw-logo.png', // Optional logo
    'ordering' => 4
]);

// Test auto-slug generation
App\Models\CategoryBrand::create([
    'brand' => 'Mercedes-Benz Parts' // Will auto-generate slug: mercedes-benz-parts
]);
```

## cURL Test Commands

### Public Endpoints (No Authentication)

#### Get All Category Brands
```bash
curl -X GET "http://localhost:8000/api/public/category-brands" \
  -H "Accept: application/json"
```

#### Get Category Brand by Slug
```bash
curl -X GET "http://localhost:8000/api/public/category-brands/toyota-parts" \
  -H "Accept: application/json"
```

### Admin Endpoints (Authentication Required)

#### Get All Category Brands (Admin)
```bash
curl -X GET "http://localhost:8000/api/admin/category-brands" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Create Category Brand (JSON)
```bash
curl -X POST "http://localhost:8000/api/admin/category-brands" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "brand": "Audi Parts",
    "ordering": 5
  }'
```

#### Create Category Brand with Logo Upload
```bash
curl -X POST "http://localhost:8000/api/admin/category-brands" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -F "brand=Audi Parts" \
  -F "ordering=5" \
  -F "logo=@/path/to/logo.png"
```

#### Create Category Brand from Logo URL
```bash
curl -X POST "http://localhost:8000/api/admin/category-brands/url" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "brand": "Audi Parts",
    "logo_url": "https://example.com/audi-logo.png",
    "ordering": 5
  }'
```

#### Create Category Brand with Custom Slug
```bash
curl -X POST "http://localhost:8000/api/admin/category-brands" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "brand": "Volkswagen Parts",
    "slug": "vw-parts",
    "ordering": 6
  }'
```

#### Update Category Brand
```bash
curl -X PUT "http://localhost:8000/api/admin/category-brands/1" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "brand": "Toyota Premium Parts",
    "slug": "toyota-premium-parts",
    "ordering": 1
  }'
```

#### Update Only Ordering
```bash
curl -X PATCH "http://localhost:8000/api/admin/category-brands/1/ordering" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "ordering": 10
  }'
```

#### Get Single Category Brand
```bash
curl -X GET "http://localhost:8000/api/admin/category-brands/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Delete Category Brand
```bash
curl -X DELETE "http://localhost:8000/api/admin/category-brands/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Postman Collection

### Environment Variables
```json
{
  "base_url": "http://localhost:8000/api",
  "auth_token": "YOUR_BEARER_TOKEN_HERE"
}
```

### Test Requests

#### 1. Get All Category Brands (Public)
- **Method:** GET
- **URL:** `{{base_url}}/public/category-brands`
- **Headers:** `Accept: application/json`

#### 2. Get Category Brand by Slug (Public)
- **Method:** GET
- **URL:** `{{base_url}}/public/category-brands/toyota-parts`
- **Headers:** `Accept: application/json`

#### 3. Create Category Brand (Admin)
- **Method:** POST
- **URL:** `{{base_url}}/admin/category-brands`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {{auth_token}}`
- **Body (JSON):**
```json
{
    "brand": "Lexus Parts",
    "ordering": 7
}
```

## Expected Responses

### Successful Creation
```json
{
    "success": true,
    "message": "Category brand created successfully",
    "data": {
        "id": 1,
        "brand": "Toyota Parts",
        "slug": "toyota-parts",
        "ordering": 1,
        "created_at": "2025-10-30T12:00:00.000000Z",
        "updated_at": "2025-10-30T12:00:00.000000Z"
    }
}
```

### Successful List (Ordered)
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "brand": "Toyota Parts",
            "slug": "toyota-parts",
            "ordering": 1,
            "created_at": "2025-10-30T12:00:00.000000Z",
            "updated_at": "2025-10-30T12:00:00.000000Z"
        },
        {
            "id": 2,
            "brand": "Honda Parts",
            "slug": "honda-parts",
            "ordering": 2,
            "created_at": "2025-10-30T12:01:00.000000Z",
            "updated_at": "2025-10-30T12:01:00.000000Z"
        }
    ]
}
```

### Validation Error
```json
{
    "success": false,
    "errors": {
        "brand": ["The brand field is required."],
        "slug": ["The slug has already been taken."]
    }
}
```

### Not Found Error
```json
{
    "success": false,
    "message": "Category brand not found"
}
```

## Testing Checklist

- [ ] Migration runs successfully
- [ ] Can create category brand with auto-generated slug
- [ ] Can create category brand with custom slug
- [ ] Slug uniqueness is enforced
- [ ] Ordering works correctly (items sorted by ordering then brand)
- [ ] Public endpoints work without authentication
- [ ] Admin endpoints require authentication
- [ ] Can update category brand
- [ ] Can update only ordering
- [ ] Can delete category brand
- [ ] Can retrieve by slug (public endpoint)
- [ ] Validation errors are properly returned
- [ ] 404 errors for non-existent items

## Integration with Frontend

### React Example
```jsx
import { useState, useEffect } from 'react';

function CategoryBrandList() {
    const [categoryBrands, setCategoryBrands] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch('/api/public/category-brands')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setCategoryBrands(data.data);
                }
                setLoading(false);
            });
    }, []);

    if (loading) return <div>Loading...</div>;

    return (
        <div>
            <h2>Category Brands</h2>
            {categoryBrands.map(brand => (
                <div key={brand.id}>
                    <h3>{brand.brand}</h3>
                    <p>Slug: {brand.slug}</p>
                    <p>Order: {brand.ordering}</p>
                </div>
            ))}
        </div>
    );
}
```

### Vue.js Example
```vue
<template>
    <div>
        <h2>Category Brands</h2>
        <div v-for="brand in categoryBrands" :key="brand.id">
            <h3>{{ brand.brand }}</h3>
            <p>Slug: {{ brand.slug }}</p>
            <p>Order: {{ brand.ordering }}</p>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            categoryBrands: []
        }
    },
    async mounted() {
        try {
            const response = await fetch('/api/public/category-brands');
            const data = await response.json();
            if (data.success) {
                this.categoryBrands = data.data;
            }
        } catch (error) {
            console.error('Error fetching category brands:', error);
        }
    }
}
</script>
```
