# Slider API - Quick Reference Guide

## Overview
The Slider API allows you to manage slider images for your application. Images are automatically stored in `public/storage/sliders/` and accessible via `/storage/sliders/` URLs.

## Endpoints

### 1. Get All Sliders
**GET** `/api/sliders`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "image": "/storage/sliders/20250120_123456_abc123.jpg",
      "ordering": 1,
      "created_at": "2025-01-20T12:34:56.000000Z",
      "updated_at": "2025-01-20T12:34:56.000000Z"
    }
  ]
}
```

---

### 2. Create Slider (File Upload)
**POST** `/api/sliders`

**Content-Type:** `multipart/form-data`

**Parameters:**
- `image` (required): Image file (jpg, jpeg, png, webp, gif, max 5MB)
- `ordering` (optional): Integer for display order

**Example (cURL):**
```bash
curl -X POST http://yourdomain.com/api/sliders \
  -F "image=@/path/to/image.jpg" \
  -F "ordering=1"
```

**Example (JavaScript/Fetch):**
```javascript
const formData = new FormData();
formData.append('image', fileInput.files[0]);
formData.append('ordering', 1);

fetch('/api/sliders', {
  method: 'POST',
  body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

**Response:**
```json
{
  "success": true,
  "message": "Slider created successfully",
  "data": {
    "id": 1,
    "image": "/storage/sliders/20250120_123456_abc123.jpg",
    "ordering": 1,
    "created_at": "2025-01-20T12:34:56.000000Z",
    "updated_at": "2025-01-20T12:34:56.000000Z"
  }
}
```

---

### 3. Create Slider (From URL)
**POST** `/api/sliders/url`

**Content-Type:** `application/json`

**Parameters:**
- `url` (required): URL of the image to download
- `ordering` (optional): Integer for display order

**Example (cURL):**
```bash
curl -X POST http://yourdomain.com/api/sliders/url \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/image.jpg",
    "ordering": 2
  }'
```

**Example (JavaScript/Fetch):**
```javascript
fetch('/api/sliders/url', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    url: 'https://example.com/image.jpg',
    ordering: 2
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

**Response:**
```json
{
  "success": true,
  "message": "Slider created successfully",
  "data": {
    "id": 2,
    "image": "/storage/sliders/20250120_123500_xyz789.jpg",
    "ordering": 2,
    "created_at": "2025-01-20T12:35:00.000000Z",
    "updated_at": "2025-01-20T12:35:00.000000Z"
  }
}
```

---

### 4. Get Single Slider
**GET** `/api/sliders/{id}`

**Example:**
```bash
curl http://yourdomain.com/api/sliders/1
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "image": "/storage/sliders/20250120_123456_abc123.jpg",
    "ordering": 1,
    "created_at": "2025-01-20T12:34:56.000000Z",
    "updated_at": "2025-01-20T12:34:56.000000Z"
  }
}
```

---

### 5. Update Slider
**PUT/PATCH** `/api/sliders/{id}`

**Content-Type:** `multipart/form-data`

**Parameters:**
- `image` (optional): New image file
- `ordering` (optional): New ordering value

**Example (cURL):**
```bash
curl -X PUT http://yourdomain.com/api/sliders/1 \
  -F "image=@/path/to/new-image.jpg" \
  -F "ordering=5"
```

**Response:**
```json
{
  "success": true,
  "message": "Slider updated successfully",
  "data": {
    "id": 1,
    "image": "/storage/sliders/20250120_124000_def456.jpg",
    "ordering": 5,
    "created_at": "2025-01-20T12:34:56.000000Z",
    "updated_at": "2025-01-20T12:40:00.000000Z"
  }
}
```

---

### 6. Update Slider Ordering Only
**PATCH** `/api/sliders/{id}/ordering`

**Content-Type:** `application/json`

**Parameters:**
- `ordering` (required): New ordering value

**Example (cURL):**
```bash
curl -X PATCH http://yourdomain.com/api/sliders/1/ordering \
  -H "Content-Type: application/json" \
  -d '{"ordering": 3}'
```

**Response:**
```json
{
  "success": true,
  "message": "Ordering updated successfully",
  "data": {
    "id": 1,
    "image": "/storage/sliders/20250120_123456_abc123.jpg",
    "ordering": 3,
    "created_at": "2025-01-20T12:34:56.000000Z",
    "updated_at": "2025-01-20T12:45:00.000000Z"
  }
}
```

---

### 7. Delete Slider
**DELETE** `/api/sliders/{id}`

**Example (cURL):**
```bash
curl -X DELETE http://yourdomain.com/api/sliders/1
```

**Response:**
```json
{
  "success": true,
  "message": "Slider deleted successfully"
}
```

---

## Image Storage Details

### Storage Locations
- **Original Storage**: `storage/app/public/sliders/`
- **Public Access**: `public/storage/sliders/`
- **URL Path**: `/storage/sliders/filename.ext`

### Supported Formats
- JPEG (.jpg, .jpeg)
- PNG (.png)
- WebP (.webp)
- GIF (.gif)

### File Size Limit
- Maximum: 5 MB (5120 KB)

### Automatic Features
✅ Directories are created automatically
✅ Files are copied to public directory
✅ Old images are deleted when updated
✅ Unique filenames prevent conflicts
✅ No symlink setup required

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "errors": {
    "image": ["The image field is required."]
  }
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Slider not found"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Unable to download image"
}
```

---

## Complete Example: Creating a Slider with HTML Form

```html
<!DOCTYPE html>
<html>
<head>
    <title>Upload Slider Image</title>
</head>
<body>
    <h1>Upload Slider Image</h1>
    
    <form id="sliderForm">
        <div>
            <label>Image:</label>
            <input type="file" name="image" id="imageInput" accept="image/*" required>
        </div>
        <div>
            <label>Ordering:</label>
            <input type="number" name="ordering" id="orderingInput" min="0" value="1">
        </div>
        <button type="submit">Upload</button>
    </form>

    <div id="result"></div>

    <script>
        document.getElementById('sliderForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('image', document.getElementById('imageInput').files[0]);
            formData.append('ordering', document.getElementById('orderingInput').value);
            
            try {
                const response = await fetch('/api/sliders', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('result').innerHTML = `
                        <h3>Success!</h3>
                        <p>Image uploaded: <a href="${data.data.image}" target="_blank">${data.data.image}</a></p>
                        <img src="${data.data.image}" style="max-width: 300px;">
                    `;
                } else {
                    document.getElementById('result').innerHTML = `
                        <h3>Error</h3>
                        <pre>${JSON.stringify(data.errors, null, 2)}</pre>
                    `;
                }
            } catch (error) {
                document.getElementById('result').innerHTML = `
                    <h3>Error</h3>
                    <p>${error.message}</p>
                `;
            }
        });
    </script>
</body>
</html>
```

---

## Notes

- Images are ordered by the `ordering` field (ascending) and then by `id`
- If `ordering` is not provided, it will be auto-incremented
- Deleting a slider also deletes the physical image file
- Updating a slider's image deletes the old image file
- All operations are atomic and include proper error handling
