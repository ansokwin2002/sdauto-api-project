# 🏷️ Brand & Product API Documentation

## 🆕 **What's New**

### **Brand API**
- **New Table**: `brands` with `id`, `brand_name`, and `slug`
- **Full CRUD**: Create, Read, Update, Delete brands
- **Auto-slug generation**: Slugs automatically created from brand names
- **Public & Admin APIs**: Both authenticated and public endpoints
- **Slug-based public access**: SEO-friendly URLs for public endpoints
- **Product Relationships**: Brands linked to products via `brand_id`

### **Updated Product API**
- **New Field**: `brand_id` (foreign key to brands table)
- **Backward Compatible**: Still accepts `brand` string for compatibility
- **Auto Brand Creation**: If brand string provided, brand is created automatically
- **Enhanced Responses**: Includes brand relationship data

---

## 🏷️ **Brand API Endpoints**

### **🔓 Public Endpoints**
```
GET /api/public/brands           # Get all brands
GET /api/public/brands/{slug}    # Get single brand by slug
```

### **🔐 Admin Endpoints**
```
GET    /api/admin/brands         # List all brands
POST   /api/admin/brands         # Create brand
GET    /api/admin/brands/{id}    # Get single brand
PUT    /api/admin/brands/{id}    # Update brand
PATCH  /api/admin/brands/{id}    # Update brand
DELETE /api/admin/brands/{id}    # Delete brand
GET    /api/admin/brands/{id}/products  # Get brand with products
```

### **Brand API Examples**

#### **Create Brand**
```bash
curl -X POST http://your-domain/api/admin/brands \
  -H "Content-Type: application/json" \
  -d '{
    "brand_name": "Apple",
    "slug": "apple"
  }'
```

**Note**: If `slug` is not provided, it will be auto-generated from `brand_name`.

#### **Get All Brands**
```bash
curl -X GET http://your-domain/api/public/brands
```

#### **Response Format**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "brand_name": "Apple",
      "slug": "apple",
      "products_count": 15,
      "created_at": "2025-10-29T14:30:22.000000Z",
      "updated_at": "2025-10-29T14:30:22.000000Z"
    }
  ]
}
```

---

## 📦 **Updated Product API**

### **New Product Fields**
- `brand_id`: Foreign key to brands table
- `brand`: String field (kept for backward compatibility)
- `brand_info`: Nested brand object in responses

### **Creating Products**

#### **Option 1: Using brand_id (Recommended)**
```bash
curl -X POST http://your-domain/api/admin/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "iPhone 15",
    "brand_id": 1,
    "category": "Smartphones",
    "part_number": "IP15-001",
    "condition": "New",
    "quantity": 10,
    "price": 999.99,
    "description": "Latest iPhone model"
  }'
```

#### **Option 2: Using brand string (Auto-creates brand)**
```bash
curl -X POST http://your-domain/api/admin/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "iPhone 15",
    "brand": "Apple",
    "category": "Smartphones",
    "part_number": "IP15-002",
    "condition": "New",
    "quantity": 10,
    "price": 999.99,
    "description": "Latest iPhone model"
  }'
```

### **Enhanced Product Response**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "iPhone 15",
    "brand": "Apple",
    "brand_id": 1,
    "brand_info": {
      "id": 1,
      "brand_name": "Apple"
    },
    "category": "Smartphones",
    "part_number": "IP15-001",
    "condition": "New",
    "quantity": 10,
    "price": 999.99,
    "formatted_price": "$999.99",
    "description": "Latest iPhone model",
    "is_active": true,
    "created_at": "2025-10-29T14:30:22",
    "updated_at": "2025-10-29T14:30:22"
  }
}
```

---

## 🔄 **Migration Process**

### **Database Changes**
1. **New Table**: `brands` (id, brand_name, timestamps)
2. **Updated Table**: `products` gets `brand_id` column
3. **Data Migration**: Existing brand strings converted to brand records
4. **Foreign Key**: `products.brand_id` references `brands.id`

### **Migration Steps**
```bash
# 1. Run migrations
php artisan migrate

# 2. Verify data migration
# Check that existing products now have brand_id values

# 3. Optional: Remove old brand column (after testing)
# Uncomment lines in migration file and run again
```

---

## 🎯 **Key Features**

### **✅ Backward Compatibility**
- Existing API calls with `brand` string still work
- Old `brand` field still returned in responses
- Automatic brand creation from strings

### **✅ Enhanced Functionality**
- Proper brand management with CRUD operations
- Brand-product relationships
- Brand statistics (product counts)
- Consistent brand naming

### **✅ Public & Admin APIs**
- Public endpoints for frontend display
- Admin endpoints for management
- Proper authentication separation

---

## 🚀 **Frontend Integration**

### **React Example - Brand Selector**
```jsx
function BrandSelector({ selectedBrandId, onBrandChange }) {
  const [brands, setBrands] = useState([]);

  useEffect(() => {
    fetch('/api/public/brands')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          setBrands(data.data);
        }
      });
  }, []);

  return (
    <select value={selectedBrandId} onChange={(e) => onBrandChange(e.target.value)}>
      <option value="">Select Brand</option>
      {brands.map(brand => (
        <option key={brand.id} value={brand.id}>
          {brand.brand_name} ({brand.products_count} products)
        </option>
      ))}
    </select>
  );
}
```

### **Product Display with Brand**
```jsx
function ProductCard({ product }) {
  return (
    <div className="product-card">
      <h3>{product.name}</h3>
      <p>Brand: {product.brand_info?.brand_name || product.brand}</p>
      <p>Price: {product.formatted_price}</p>
    </div>
  );
}
```

---

## 📋 **Validation Rules**

### **Brand Validation**
```php
'brand_name' => 'required|string|max:255|unique:brands,brand_name',
'slug' => 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:brands,slug'
```

### **Auto-Slug Generation**
- If no slug is provided, it's automatically generated from the brand name
- Slugs are converted to lowercase and spaces/special characters become hyphens
- Duplicate slugs get a numeric suffix (e.g., "apple-2")

### **Product Validation**
```php
'brand' => 'sometimes|string|max:100',
'brand_id' => 'nullable|exists:brands,id',
// Either brand or brand_id must be provided
```

---

## 🔗 **Slug Usage Examples**

### **Frontend Integration**
```javascript
// Get all brands
fetch('/api/public/brands')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.data.forEach(brand => {
                console.log(`${brand.brand_name} - ${brand.slug}`);
            });
        }
    });

// Get specific brand by slug
fetch('/api/public/brands/apple')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Brand:', data.data.brand_name);
            console.log('Products:', data.data.products);
        }
    });
```

### **Admin Panel Integration**
```javascript
// Create brand with custom slug
fetch('/api/admin/brands', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        brand_name: 'Mercedes-Benz',
        slug: 'mercedes-benz'
    })
});

// Create brand with auto-generated slug
fetch('/api/admin/brands', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        brand_name: 'BMW Parts'
        // slug will be auto-generated as 'bmw-parts'
    })
});
```

---

## 🎉 **Ready to Use!**

Your Brand and Product APIs are now fully integrated with:
- ✅ Complete brand management
- ✅ Auto-slug generation for SEO-friendly URLs
- ✅ Slug-based public access
- ✅ Product-brand relationships
- ✅ Backward compatibility
- ✅ Public & admin endpoints
- ✅ Auto brand creation
- ✅ Enhanced responses
