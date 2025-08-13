# PROPERTY API

- A laravel based REST APi for managing Corporations , Buildings , Properties , Tenancy Periods and Tenants
- The Project is completely containerized using Docker.

## Technology Stack

|  |  |
|--------|----------|
| Backend | PHP  |
| Framework | Laravel |
| Databse | MySQL |
| Containers | Docker |
| Authentication | Laravel Sanctum |
| API Documentation | Swagger |
| Testing | PHPUnit|
| API Testing | Postman |
|  |  |



## Prerequisites

- sudo access to a host with docker & docker-compose installed.
- Git installed.

## Usage

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

## Testing

1. Run complete test cases

        sudo docker-compose exec app php artisan test

1. Run specific test case 

        sudo docker-compose exec app php artisan test tests/Feature/NodeControllerTest.php

## API Documentation

1. Publish configurations to Swagger 

        sudo docker-compose exec app php artisan  vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

1. Generate documentation

        sudo docker-compose exec app php artisan l5-swagger:generate   

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


## Business Requirements

### Node Hierarchy

- Corporation → Building
- Building → Property  
- Property → Tenancy Period
- Tenancy Period → Tenant

### Conditions

- Maximum 4 tenants per tenancy period
- Only 1 active tenancy period per property
- Buildings require zip_code
- Properties require monthly_rent
- Tenancy Periods require tenancy_active (boolean)
- Tenants require move_in_date
    
### Field Requirements by Node Type

| Node Type | Required Fields |
|-----------|----------------|
| Corporation | name, type |
| Building | name, type, parent_id, zip_code |
| Property | name, type, parent_id, monthly_rent |
| Tenancy Period | name, type, parent_id, tenancy_active |
| Tenant | name, type, parent_id, move_in_date |

## Code Execution & Validations using Postman

- Files from postman has been imported and kepy under the folder postman
- Please refer to [Postman Steps](Postman_Steps.md) for detailed explanation on executing and validating the code using Postman.

## Further Developments

- Multi factor authentication setup using Laravel Fortify.
- API to handle more granular Authorization Policies as explained below:

    1. Have multiple user roles - Guest User, Tenant, Property Manager, Building Manager, Corporation Manager, Admin.
    1. Guest User should ONLY be able to view Properties that are vacant.
    1. Tenant should ONLY be able to view tenancy Period details of the Property. They should not be able to view other tenant's details.
    1. Managers SHOULD have edit access to their respective node level and below. ie- Building manager should be able to edit details on Buildings, Properties , Tenancy period and tenants and should not be able to edit on Corporation.

- Frontend view for this Project API.

## Docker Setup

- There are 4 different containers as mentioned below

    1. PHP APP Container : Based of php:8.2-fpm base image and it contains composer also. Laravel and other dependencies are installed outside the container image using composer.json file
    1. NGINX Container : Based of nginx:alpine base image. This container will server the Laravel app over HTTP
    1. MySQL DB Container : Based of mysql:8.0 base image.
    1. MySQL DB test Container: A dedicated container hosting test DB to run PHP tests

- Containers are built using docker-compose.

## Tips

- Check the status of containers and make sure that they are UP
        sudo docker ps

- If you perform any changes to .env file especially DB related environment variables , then it is recommended to Shutdown the containers , remove the docker db volumes and then bring the containers back online

        sudo docker-compose down;sudo docker volume rm property_api_db_data;sudo docker volume rm property_api_db_test_data;sudo docker-compose up -d

- If you want to connect to Databases running in containers , use below commands

        sudo docker-compose exec db mysql -u <username> -p <API DB Name>
        sudo docker-compose exec db_test mysql -u <username> -p <API test DB Name>

        



