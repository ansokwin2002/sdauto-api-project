# 🚚 Delivery Partners API - Field Explanation

## 📋 **Field Definitions**

### **Two Different URL Fields:**

#### 1. **`url_link`** - Partner Website URL
- **Purpose**: Link to the delivery partner's official website
- **Usage**: Displayed as clickable link on frontend
- **Validation**: `nullable|url|max:500`
- **Example**: `"https://www.dhl.com"`
- **Storage**: Stored as plain text in database
- **No Download**: This URL is NOT downloaded or processed

#### 2. **`image_url`** - Image Download URL  
- **Purpose**: URL pointing to an image file to be downloaded and stored
- **Usage**: Only for creating/updating with remote images
- **Validation**: `required|url` (when using URL endpoints)
- **Example**: `"https://example.com/dhl-logo.png"`
- **Storage**: Image is downloaded and stored locally
- **Download**: This URL IS downloaded and processed as image

## 🛣️ **API Endpoints & Field Usage**

### **Regular CRUD (File Upload)**
```bash
# POST /api/admin/delivery-partners
curl -X POST http://your-domain/api/admin/delivery-partners \
  -F "title=DHL Express" \
  -F "description=Fast delivery service" \
  -F "url_link=https://www.dhl.com" \
  -F "image=@logo.png"
```
**Fields**: `title`, `description`, `url_link`, `image` (file)

### **URL-Based Image Creation**
```bash
# POST /api/admin/delivery-partners/url
curl -X POST http://your-domain/api/admin/delivery-partners/url \
  -H "Content-Type: application/json" \
  -d '{
    "title": "FedEx",
    "description": "Global courier services",
    "url_link": "https://www.fedex.com",
    "image_url": "https://example.com/fedex-logo.png"
  }'
```
**Fields**: `title`, `description`, `url_link`, `image_url`

## ⚠️ **Common Confusion**

### **❌ Wrong Understanding:**
- Thinking `url_link` should point to an image
- Expecting `url_link` to be downloaded

### **✅ Correct Understanding:**
- `url_link` = Partner's website (just stored as text)
- `image_url` = Image file to download (only for `/url` endpoints)

## 🔧 **Troubleshooting "Unable to Download Image" Error**

### **If you get this error, check:**

1. **Are you using the wrong endpoint?**
   - Use `/api/admin/delivery-partners` for file uploads
   - Use `/api/admin/delivery-partners/url` for image URLs

2. **Are you confusing the fields?**
   - `url_link` should be the partner's website
   - `image_url` should point to an actual image file

3. **Is your `image_url` valid?**
   - Must point to an actual image file
   - Must be accessible (not behind authentication)
   - Must be a supported format (jpg, png, gif, webp)

## 📝 **Correct Examples**

### **Example 1: Create with File Upload**
```json
{
  "title": "DHL Express",
  "description": "Fast and reliable delivery",
  "url_link": "https://www.dhl.com"
}
```
+ File: `image` (multipart upload)

### **Example 2: Create with Image URL**
```json
{
  "title": "FedEx",
  "description": "Global courier services", 
  "url_link": "https://www.fedex.com",
  "image_url": "https://logos.example.com/fedex.png"
}
```

### **Example 3: Update Basic Info Only**
```json
{
  "title": "Updated Title",
  "description": "Updated description",
  "url_link": "https://www.newwebsite.com"
}
```

## 🎯 **Key Points**

1. **`url_link`** is just stored text - no processing
2. **`image_url`** triggers image download and storage
3. Both fields are optional
4. Use correct endpoint for your use case
5. Don't mix up the field purposes

## 🚀 **Frontend Usage**

```jsx
// url_link is used for clickable links
<a href={partner.url_link} target="_blank">
  {partner.title}
</a>

// image is used for display
<img src={partner.image} alt={partner.title} />
```
