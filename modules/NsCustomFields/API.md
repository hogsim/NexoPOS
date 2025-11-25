# Custom Fields API Documentation

This module provides REST API endpoints to manage custom fields for customers in NexoPOS.

## Authentication

The Customer Custom Fields API endpoints support two authentication methods:

### 1. Session Authentication (for browser/web requests)
When making requests from the browser where you're already logged into NexoPOS:
```javascript
fetch('/api/customers/67/custom-fields', {
  credentials: 'include'  // Include session cookie
}).then(r => r.json());
```

### 2. API Token Authentication (for external apps/scripts)
For external applications, mobile apps, or scripts, use Laravel Sanctum API tokens:

1. **Create an API token** in NexoPOS:
   - Go to your user profile settings
   - Generate a new API token
   - Copy the token (you'll only see it once)

2. **Use the token in requests**:
```bash
curl -X GET 'https://your-nexopos.com/api/customers/67/custom-fields' \
  -H 'Authorization: Bearer YOUR_API_TOKEN'
```

```javascript
fetch('/api/customers/67/custom-fields', {
  headers: {
    'Authorization': 'Bearer YOUR_API_TOKEN'
  }
}).then(r => r.json());
```

**Note:** The configuration endpoints (`/api/custom-fields/config`) only support session authentication as they're intended for the admin UI.


## Endpoints

### 1. Get Custom Fields Configuration

Retrieve the list of configured custom fields.

**Endpoint:** `GET /api/custom-fields/config`

**Response:**
```json
{
  "status": "success",
  "value": [
    {
      "label": "Badge Number",
      "name": "badge_number",
      "type": "number",
      "description": ""
    },
    {
      "label": "Badge UID",
      "name": "badge_uid",
      "type": "text",
      "description": ""
    }
  ]
}
```

### 2. Get Customer Custom Fields

Retrieve custom field values for a specific customer.

**Endpoint:** `GET /api/customers/{customerId}/custom-fields`

**Parameters:**
- `customerId` (path parameter): The customer ID

**Response:**
```json
{
  "status": "success",
  "data": {
    "badge_number": 1001,
    "badge_uid": "049f50824f1390"
  }
}
```

###3. Update Customer Custom Fields

Update custom field values for a specific customer.

**Endpoint:** `PUT /api/customers/{customerId}/custom-fields` or `POST /api/customers/{customerId}/custom-fields`

**Parameters:**
- `customerId` (path parameter): The customer ID

**Request Body:**
```json
{
  "custom_fields": {
    "badge_number": 1001,
    "badge_uid": "049f50824f1390"
  }
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Custom fields updated successfully.",
  "data": {
    "badge_number": 1001,
    "badge_uid": "049f50824f1390"
  }
}
```

### 4. Search Customers by Custom Field

Search for customers by a custom field value and get their full customer information including custom fields.

**Endpoint:** `GET /api/customers/search-by-custom-field` or `POST /api/customers/search-by-custom-field`

**Parameters:**
- `field` (query/body parameter): The name of the custom field to search by
- `value` (query/body parameter): The value to search for

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 67,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "phone": null,
      "group_id": 1,
      "group": {
        "id": 1,
        "name": "Default"
      },
      "billing": {
        "address_1": "123 Main St",
        "city": "New York"
      },
      "shipping": {
        "address_1": "123 Main St"
      },
      "custom_fields": {
        "badge_number": 1001,
        "badge_uid": "049f50824f1390"
      }
    }
  ]
}
```

**Note:** Returns an array of matching customers. If no customers match, returns an empty array.


## Usage Examples

### Using cURL with Session Cookie

#### Get configuration:
```bash
curl -X GET 'https://your-nexopos.com/api/custom-fields/config' \
  -H 'Cookie: your_session_cookie'
```

#### Get customer custom fields:
```bash
curl -X GET 'https://your-nexopos.com/api/customers/67/custom-fields' \
  -H 'Cookie: your_session_cookie'
```

#### Update customer custom fields:
```bash
curl -X PUT 'https://your-nexopos.com/api/customers/67/custom-fields' \
  -H 'Content-Type: application/json' \
  -H 'Cookie: your_session_cookie' \
  -d '{
    "custom_fields": {
      "badge_number": 1001,
      "badge_uid": "049f50824f1390"
    }
  }'
```

### Using cURL with API Token

#### Get customer custom fields:
```bash
curl -X GET 'https://your-nexopos.com/api/customers/67/custom-fields' \
  -H 'Authorization: Bearer YOUR_API_TOKEN'
```

#### Update customer custom fields:
```bash
curl -X PUT 'https://your-nexopos.com/api/customers/67/custom-fields' \
  -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer YOUR_API_TOKEN' \
  -d '{
    "custom_fields": {
      "badge_number": 1001,
      "badge_uid": "049f50824f1390"
    }
  }'
```

### Using JavaScript/Fetch

```javascript
// Get configuration
const config = await fetch('/api/custom-fields/config', {
  credentials: 'include'
}).then(r => r.json());

// Get customer custom fields
const fields = await fetch('/api/customers/67/custom-fields', {
  credentials: 'include'
}).then(r => r.json());

// Update customer custom fields
const result = await fetch('/api/customers/67/custom-fields', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json'
  },
  credentials: 'include',
  body: JSON.stringify({
    custom_fields: {
      badge_number: 1001,
      badge_uid: '049f50824f1390'
    }
  })
}).then(r => r.json());

// Search customers by custom field
const searchResults = await fetch('/api/customers/search-by-custom-field?field=badge_uid&value=049f50824f1390', {
  credentials: 'include'
}).then(r => r.json());
```


## Notes

- Custom fields are stored as JSON in the `nexopos_users_attributes` table
- The `custom_fields` data is merged with existing fields when updating (existing fields not included in the request are preserved)
- Only configured custom fields (defined in Settings > Custom Fields) should be used
- CSRF protection is disabled for these API endpoints to allow external API access
