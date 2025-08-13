# 1. **Register New User**
```
Method: POST
URL: http://127.0.0.1:8000/api/v1/register
Headers: Content-Type: application/json
Body (JSON):
{
  "name": "Admin User",
  "email": "admin@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "is_admin": true
}
```
**Expected Response (201):**
```json
{
  "status": "success",
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "is_admin": true
    },
    "token": "1|abcd1234efgh5678..."
  }
}
```
# 2. **Login User**
```
    Method: POST
    URL: http://localhost:8000/api/login
    Headers: Content-Type: application/json
    Body (JSON):
    {
    "email": "test@gmail.com",
    "password": "test123@95"
    }
```

**Expected Response (200):**
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "user": {
      "id": 4,
      "name": "Test User",
      "email": "test@gmail.com",
      "is_admin": true
    },
    "token": "11|QlPHCRuERvjYRXEHwN7q5ZNiVRYvs50wAjxNyEYHa3d52ddb"
  }
}
```

# Copy the token from login add it to each API

# 3. **Get Current User Info**
```
Method: GET
URL: http://localhost:8000/api/me
Headers: 
  Authorization: Bearer YOUR_TOKEN_HERE
  Content-Type: application/json
```

**Expected Response (200):**
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 4,
      "name": "Test User",
      "email": "test@gmail.com",
      "is_admin": true
    }
  }
}
```
# 4. **Create Nodes** 

**A. Create Corporation (Root Node)**

    ```
    Method: POST
    URL: http://localhost:8000/api/nodes
    Headers: 
    Authorization: Bearer YOUR_TOKEN_HERE
    Content-Type: application/json
    Body (JSON):
    {
    "name": "ABC Corporation",
    "type": "Corporation"
    }
    ```

**B. Create Building (Child of Corporation)**

    ```
    Method: POST
    URL: http://localhost:8000/api/nodes
    Headers: 
    Authorization: Bearer YOUR_TOKEN_HERE
    Content-Type: application/json
    Body (JSON):
    {
    "name": "Main Building",
    "type": "Building",
    "parent_id": 1,
    "zip_code": "12345"
    }
    ```

**C. Create Property (Child of Building)**

    ```
    Method: POST
    URL: http://localhost:8000/api/nodes
    Headers: 
    Authorization: Bearer YOUR_TOKEN_HERE
    Content-Type: application/json
    Body (JSON):
    {
    "name": "Apartment 101",
    "type": "Property",
    "parent_id": 2,
    "monthly_rent": 1500.00
    }
    ```

**D. Create Tenancy Period (Child of Property)**

    ```
    Method: POST
    URL: http://localhost:8000/api/nodes
    Headers: 
    Authorization: Bearer YOUR_TOKEN_HERE
    Content-Type: application/json
    Body (JSON):
    {
    "name": "2025 Lease Period",
    "type": "Tenancy Period",
    "parent_id": 3,
    "tenancy_active": true
    }
    ```

**E. Create Tenant (Child of Tenancy Period)**

    ```
    Method: POST
    URL: http://localhost:8000/api/nodes
    Headers: 
    Authorization: Bearer YOUR_TOKEN_HERE
    Content-Type: application/json
    Body (JSON):
    {
    "name": "John Doe",
    "type": "Tenant",
    "parent_id": 4,
    "move_in_date": "2025-01-01"
    }
    ```

# 5. **Get Child Nodes**
    ```
    Method: GET
    URL: http://localhost:8000/api/nodes/{node_id}/children
    Headers: 
    Authorization: Bearer YOUR_TOKEN_HERE
    Content-Type: application/json

    Example: http://localhost:8000/api/nodes/1/children
    ```

**Expected Response (200):**

    ```json
    {
    "data": [
        {
        "id": 2,
        "name": "Main Building",
        "type": "Building",
        "parent_id": 1,
        "height": 1,
        "zip_code": "12345"
        }
    ]
    }
    ```

# 6. **Update Node Parent**
    ```
    Method: PUT
    URL: http://localhost:8000/api/nodes/{node_id}/change-parent
    Headers: 
    Authorization: Bearer YOUR_TOKEN_HERE
    Content-Type: application/json

    Example: http://localhost:8000/api/nodes/5/change-parent
    Body (JSON):
    {
    "name": "John Doe",
    "type": "Tenant",
    "parent_id": 4,
    "move_in_date": "2025-02-01"
    }
    ```

# Validation Testing Scenarios

## Test Missing Required Fields

**Missing zip_code for Building:**
```json
{
  "name": "Test Building",
  "type": "Building",
  "parent_id": 1
}
```
**Expected Error (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "zip_code": ["Zip code is required when creating a Building."]
  }
}
```

**Missing monthly_rent for Property:**
```json
{
  "name": "Test Property",
  "type": "Property",
  "parent_id": 2
}
```

**Missing tenancy_active for Tenancy Period:**
```json
{
  "name": "Test Tenancy",
  "type": "Tenancy Period",
  "parent_id": 3
}
```

**Missing move_in_date for Tenant:**
```json
{
  "name": "Test Tenant",
  "type": "Tenant",
  "parent_id": 4
}
```

## Test Business Rule Violations

**Too Many Tenants (5th tenant in same tenancy period):**
```json
{
  "name": "5th Tenant",
  "type": "Tenant",
  "parent_id": 4,
  "move_in_date": "2025-01-01"
}
```
**Expected Error (422):**
```json
{
  "status": "Tenancy rules violation",
  "message": "Maximum of 4 tenants allowed per tenancy period"
}
```

**Invalid Parent-Child Relationship:**
```json
{
  "name": "Invalid Building",
  "type": "Building",
  "parent_id": 3,
  "zip_code": "12345"
}
```
**Expected Error (422):**
```json
{
  "error": "Invalid parent-child relationship"
}
```
