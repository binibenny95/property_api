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

# Prerequisites

- sudo access to a host with docker & docker-compose installed.
- Git installed.

# Usage

1. Clone the repository to your local host

        git clone git@github.com:binibenny95/property_api.git

1. Change directory to repository's root folder

        cd property_api

1. Generate `.env` and `.env.testing` files using the example files provided.

        cp .env_example .env
        cp .env.testing_example .env.testing

    *NOTE: env files contain environment variables & values that are requred for application & DB. These fies are in .gitignore list.*

1. Build and start the containers

        sudo docker-compose up --build -d

1. Validate that the containers are started and running 

        sudo docker ps

1. Install all PHP dependencies defined in composer.json file

        sudo docker-compose exec app composer install

1. Execute DB migration files which will create or update tables & schema in the DB

        sudo docker-compose exec app php artisan migrate

1. Validate the DB migration status

        sudo docker-compose exec app php artisan migrate:status

1. Perform basic php validations 

        sudo docker-compose exec app php artisan --version

        sudo docker-compose exec app php artisan route:list

1. API should be now accessible at http://localhost:8000

# Testing

1. Run complete test cases

        sudo docker-compose exec app php artisan test

1. Run specific test case 

        sudo docker-compose exec app php artisan test tests/Feature/NodeControllerTest.php

# API Documentation

1. Publish configurations to Swagger 

        sudo docker-compose exec app php artisan  vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

1. Generate documentation

        sudo docker-compose exec app php artisan l5-swagger:generate   


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

#### 4. **Create Nodes** 

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
