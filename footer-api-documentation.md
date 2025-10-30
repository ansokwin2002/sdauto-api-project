# Footer API Documentation

## Overview
The Footer API provides endpoints to manage website footer information including title, description, contact details, and business hours.

## Database Schema
The `footers` table contains the following fields:
- `id` (Primary Key)
- `title` (String, nullable) - Footer title
- `description` (Text, nullable) - Footer description
- `contact` (Text, nullable) - Contact information
- `business_hour` (Text, nullable) - Business hours information
- `created_at` (Timestamp)
- `updated_at` (Timestamp)

## API Endpoints

### 🔓 Public Endpoints (Frontend Website)

#### Get Footer Information
```
GET /api/public/footer
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Company Name",
            "description": "Brief description about the company",
            "contact": "Email: info@company.com\nPhone: +1-234-567-8900",
            "business_hour": "Mon-Fri: 9:00 AM - 6:00 PM\nSat: 10:00 AM - 4:00 PM\nSun: Closed",
            "created_at": "2025-10-30T00:00:00.000000Z",
            "updated_at": "2025-10-30T00:00:00.000000Z"
        }
    ]
}
```

### 🔒 Admin Endpoints (Authentication Required)

#### Get All Footers
```
GET /api/admin/footer
Authorization: Bearer {token}
```

#### Create New Footer
```
POST /api/admin/footer
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Company Name",
    "description": "Brief description about the company",
    "contact": "Email: info@company.com\nPhone: +1-234-567-8900",
    "business_hour": "Mon-Fri: 9:00 AM - 6:00 PM\nSat: 10:00 AM - 4:00 PM\nSun: Closed"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Footer created successfully",
    "data": {
        "id": 1,
        "title": "Company Name",
        "description": "Brief description about the company",
        "contact": "Email: info@company.com\nPhone: +1-234-567-8900",
        "business_hour": "Mon-Fri: 9:00 AM - 6:00 PM\nSat: 10:00 AM - 4:00 PM\nSun: Closed",
        "created_at": "2025-10-30T00:00:00.000000Z",
        "updated_at": "2025-10-30T00:00:00.000000Z"
    }
}
```

#### Get Single Footer
```
GET /api/admin/footer/{id}
Authorization: Bearer {token}
```

#### Update Footer
```
PUT /api/admin/footer/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Updated Company Name",
    "description": "Updated description",
    "contact": "Updated contact info",
    "business_hour": "Updated business hours"
}
```

#### Delete Footer
```
DELETE /api/admin/footer/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Footer deleted successfully"
}
```

## Validation Rules

All fields are optional (nullable), but when provided:
- `title`: String, maximum 255 characters
- `description`: Text (unlimited length)
- `contact`: Text (unlimited length)
- `business_hour`: Text (unlimited length)

## Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "errors": {
        "title": ["The title field must not be greater than 255 characters."]
    }
}
```

### Not Found Error (404)
```json
{
    "success": false,
    "message": "Footer not found"
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
Use the public endpoint to fetch footer data for your website:
```javascript
fetch('/api/public/footer')
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.length > 0) {
            const footer = data.data[0]; // Usually there's one footer
            document.getElementById('footer-title').textContent = footer.title;
            document.getElementById('footer-description').textContent = footer.description;
            document.getElementById('footer-contact').innerHTML = footer.contact.replace(/\n/g, '<br>');
            document.getElementById('footer-hours').innerHTML = footer.business_hour.replace(/\n/g, '<br>');
        }
    });
```

### Admin Panel Integration
Create or update footer information:
```javascript
// Create footer
fetch('/api/admin/footer', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        title: 'My Company',
        description: 'We provide excellent services',
        contact: 'Email: contact@mycompany.com\nPhone: +1-555-0123',
        business_hour: 'Mon-Fri: 9AM-5PM\nWeekends: Closed'
    })
});
```

## Files Created/Modified

### New Files:
1. `database/migrations/2025_10_30_000000_create_footers_table.php` - Database migration
2. `app/Models/Footer.php` - Eloquent model
3. `app/Http/Controllers/Api/FooterController.php` - API controller

### Modified Files:
1. `routes/api.php` - Added footer routes

## Next Steps

1. Run the migration: `php artisan migrate`
2. Test the API endpoints using Postman or similar tool
3. Integrate with your frontend application
4. Consider adding seeder data if needed
