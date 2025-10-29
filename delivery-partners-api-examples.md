# Delivery Partners API Examples

## 🌐 Public API Endpoints (Frontend Website)

### Get All Delivery Partners (Public)
```bash
curl -X GET http://your-domain/api/public/delivery-partners
```

### Get Specific Delivery Partner (Public)
```bash
curl -X GET http://your-domain/api/public/delivery-partners/1
```

### JavaScript/Frontend Examples (Public API)
```javascript
// Get all delivery partners for frontend display
fetch('/api/public/delivery-partners')
  .then(response => response.json())
  .then(data => {
    console.log('Delivery Partners:', data.data);
    // Display in your frontend
  });

// Get specific delivery partner
fetch('/api/public/delivery-partners/1')
  .then(response => response.json())
  .then(data => {
    console.log('Delivery Partner:', data.data);
  });
```

### React Example
```jsx
import { useState, useEffect } from 'react';

function DeliveryPartners() {
  const [partners, setPartners] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/api/public/delivery-partners')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          setPartners(data.data);
        }
        setLoading(false);
      })
      .catch(error => {
        console.error('Error:', error);
        setLoading(false);
      });
  }, []);

  if (loading) return <div>Loading...</div>;

  return (
    <div className="delivery-partners">
      <h2>Our Delivery Partners</h2>
      <div className="partners-grid">
        {partners.map(partner => (
          <div key={partner.id} className="partner-card">
            {partner.image && (
              <img src={partner.image} alt={partner.title} />
            )}
            <h3>
              {partner.url_link ? (
                <a href={partner.url_link} target="_blank" rel="noopener noreferrer">
                  {partner.title}
                </a>
              ) : (
                partner.title
              )}
            </h3>
            <p>{partner.description}</p>
          </div>
        ))}
      </div>
    </div>
  );
}
```

---

## 🔐 Admin API Endpoints (Management)

### 1. Create with File Upload

```bash
curl -X POST http://your-domain/api/admin/delivery-partners \
  -H "Content-Type: multipart/form-data" \
  -F "title=DHL Express" \
  -F "description=Fast and reliable international delivery service" \
  -F "url_link=https://www.dhl.com" \
  -F "image=@/path/to/dhl-logo.png"
```

### 2. Create with Image URL

```bash
curl -X POST http://your-domain/api/admin/delivery-partners/url \
  -H "Content-Type: application/json" \
  -d '{
    "title": "FedEx",
    "description": "Global courier delivery services",
    "url_link": "https://www.fedex.com",
    "image_url": "https://example.com/fedex-logo.png"
  }'
```

### 3. Get All Delivery Partners (Admin)

```bash
curl -X GET http://your-domain/api/admin/delivery-partners
```

### 4. Get Specific Delivery Partner (Admin)

```bash
curl -X GET http://your-domain/api/admin/delivery-partners/1
```

### 5. Update with File Upload

```bash
curl -X PUT http://your-domain/api/admin/delivery-partners/1 \
  -H "Content-Type: multipart/form-data" \
  -F "title=DHL Express Updated" \
  -F "description=Updated description" \
  -F "url_link=https://www.dhl.com/tracking" \
  -F "image=@/path/to/new-logo.png"
```

### 6. Update with Image URL

```bash
curl -X PATCH http://your-domain/api/admin/delivery-partners/1/url \
  -H "Content-Type: application/json" \
  -d '{
    "title": "FedEx Updated",
    "url_link": "https://www.fedex.com/tracking",
    "image_url": "https://example.com/new-fedex-logo.png"
  }'
```

### 7. Delete Delivery Partner

```bash
curl -X DELETE http://your-domain/api/admin/delivery-partners/1
```

## Response Examples

### Success Response
```json
{
  "success": true,
  "message": "Delivery partner created successfully",
  "data": {
    "id": 1,
    "title": "DHL Express",
    "description": "Fast and reliable international delivery service",
    "image": "/storage/delivery-partners/20251029_143022_abc123def4.png",
    "url_link": "https://www.dhl.com",
    "created_at": "2025-10-29T14:30:22.000000Z",
    "updated_at": "2025-10-29T14:30:22.000000Z"
  }
}
```

### Error Response
```json
{
  "success": false,
  "errors": {
    "title": ["The title field is required."],
    "image": ["The image must be an image.", "The image may not be greater than 5120 kilobytes."],
    "url_link": ["The url link must be a valid URL."]
  }
}
```

## JavaScript/Frontend Examples

### Create with File Upload (JavaScript)
```javascript
const formData = new FormData();
formData.append('title', 'UPS');
formData.append('description', 'United Parcel Service');
formData.append('url_link', 'https://www.ups.com');
formData.append('image', fileInput.files[0]);

fetch('/api/admin/delivery-partners', {
  method: 'POST',
  body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

### Create with Image URL (JavaScript)
```javascript
fetch('/api/admin/delivery-partners/url', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    title: 'USPS',
    description: 'United States Postal Service',
    url_link: 'https://www.usps.com',
    image_url: 'https://example.com/usps-logo.png'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

## Notes

1. **File Size Limit**: Maximum 5MB for uploaded images
2. **Supported Formats**: JPG, JPEG, PNG, WebP, GIF
3. **Storage**: Images are stored in `storage/app/public/delivery-partners/`
4. **URL Access**: Images accessible via `/storage/delivery-partners/filename.ext`
5. **Validation**: All fields are validated according to the rules defined in the controller
6. **Cleanup**: Old images are automatically deleted when updating or deleting delivery partners
