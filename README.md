# PROPERTY API

A Laravel-based REST API for managing corporations, buildings, properties, tenancy periods, and tenants.


## Endpoints 
1.Add a new node to the tree.

2.Get all child nodes of a given node from the tree (only 1 layer of children).

3.Change the parent node of a given node to another valid node.

## Technology Stack 

**Backend**: Laravel 10.48.29 
**Database**:MySQL
**Authentication**: Laravel Sanctum
**API Documentation** : Swagger
**Testing** : PHPUnit
**API Testing** : Postman
**PHP Version** : 8.1

### Set up the project on your local repo
    git clone git@github.com:binibenny95/property_api.git

    cd property_api

    composer install

    cp .env.example .env

    php artisan key:generate


#### Set Up the .env file

    APP_NAME="Property Management API"
    APP_ENV=local
    APP_KEY=base64:your-generated-key
    APP_DEBUG=true
    APP_URL=http://localhost:8000

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=property_api
    DB_USERNAME=your_username
    DB_PASSWORD=your_password



#### Set up testing database for unit tests in .env.testing
    APP_ENV=testing
    APP_DEBUG=true

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=property_api_test
    DB_USERNAME=your_username
    DB_PASSWORD=your_password

## Database setup
    run these commands on your terminal:

    mysql -u username -p

    CREATE DATABASE property_api; //for API testing

    CREATE DATABASE property_api_test; // for unit testing

### Migrations
    php artisan migrate 

## API Documentation

### Generate Swagger Documentation


    # Install Swagger package
    composer require darkaonline/l5-swagger

    # Publish configuration
    php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

    # Generate documentation
    php artisan l5-swagger:generate


### Access Documentation

Visit: `http://localhost:8000/api/documentation`


## Authentication

The API uses Laravel Sanctum for token-based authentication.

### Authentication Flow

1. **Register** or **Login** to get access token
2. Include token in requests: `Authorization: Bearer {token}`.

### User Roles

- **Admin**: Can create, update, and delete nodes
- **User**: Can only view nodes

## API Endpoints

### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/register` | Register new user | No |
| POST | `/api/login` | User login | No |
| POST | `/api/logout` | User logout | Yes |
| GET | `/api/me` | Get current user | Yes |

### Node Management Endpoints

| Method | Endpoint | Description | Auth Required | Admin Only |
|--------|----------|-------------|---------------|------------|
| POST | `/api/nodes` | Create new node | Yes | Yes |
| GET | `/api/nodes/{id}/children` | Get node children | Yes | No |
| PUT | `/api/nodes/{id}/parent` | Update node parent | Yes | Yes |


## Task Logic

Corporation (root)->
Building (requires zip_code)->
Property (requires monthly_rent)->
Tenancy Period (requires tenancy_active)->
Tenant (requires move_in_date)


### Validation Rules

1. **Parent-Child Relationships**: Each node type can only have specific parent types
2. **Active Tenancy Limit**: Only one active tenancy period per property
3. **Tenant Limit**: Maximum 4 tenants per tenancy period
4. **Required Fields**: Each node type has specific required fields

### Field Requirements by Node Type

| Node Type | Required Fields |
|-----------|----------------|
| Corporation | name, type |
| Building | name, type, parent_id, zip_code |
| Property | name, type, parent_id, monthly_rent |
| Tenancy Period | name, type, parent_id, tenancy_active |
| Tenant | name, type, parent_id, move_in_date |

## Testing

### Run Tests


    # Run all tests
    php artisan test

    # Run specific test file
    php artisan test tests/Feature/NodeControllerTest.php

# Run the solutions
## Using postman
I have imported files from postman .You can see that on postman folder.
#### 1. **Register New User**
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

#### 2. **Login User**
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

### Copy the token from login add it to each API

#### 3. **Get Current User Info**
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

#### 4. **Create Nodes** (Following Hierarchy)

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

    #### 5. **Get Child Nodes**
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

    #### 6. **Update Node Parent**
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

### Validation Testing Scenarios

#### Test Missing Required Fields

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

#### Test Business Rule Violations

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
### Node Hierarchy Rules

**Valid Parent-Child Relationships:**
- Corporation → Building
- Building → Property  
- Property → Tenancy Period
- Tenancy Period → Tenant

**Business Rules:**
- Maximum 4 tenants per tenancy period
- Only 1 active tenancy period per property
- Buildings require zip_code
- Properties require monthly_rent
- Tenancy Periods require tenancy_active (boolean)
- Tenants require move_in_date
