# Event Booking API

A RESTful API built with Laravel for managing events, attendees, and bookings — with Docker support, validation, testing, and clean architecture.

---

## Features

- CRUD operations for **Events**
- Registration and management of **Attendees**
- Event **Booking System** with:
  - Capacity checks
  - Prevention of duplicate bookings
- Input **Validation** & Clean API Responses
- Dockerized for easy local setup
- Unit and integration tests
- API documentation via Postman

---

## Tech Stack

- **Laravel 10+**
- **MySQL 8**
- **PHP 8.2 (FPM)**
- **Docker & Docker Compose**
- **Nginx**

---

## Project Setup

### 1. Clone the Repository
```bash
git clone https://github.com/kamble2023/event-booking-api.git
cd event-booking-api

### 2. Install dependencies
composer install

#### 3. Create .env file
cp .env.example .env


### 4. Generate key
php artisan key:generate 

### Run migrations
php artisan migrate

### Run the server
php artisan serve

```
---

## DB settings match the Docker setup:    
 ```
  DB_CONNECTION=mysql
  DB_HOST=db
  DB_PORT=3306
  DB_DATABASE=event_booking
  DB_USERNAME=root
  DB_PASSWORD=secret
```
## Start Docker (Run in bash)
- **docker-compose up -d --build**
- **Access the app at: http://localhost:8000**

## Install Dependencies
```
  docker exec -it event_booking_app composer install**
  docker exec -it event_booking_app php artisan key:generate
  docker exec -it event_booking_app php artisan migrate
```
## Authentication

- **API consumers must be authenticated to manage events.**
- **Attendees can register and book without authentication.**
- **Authentication system is not implemented but structure is modular to plug in Laravel Sanctum or Passport.**

## Unit Testing of the API's

- **Run the command in bash** 
  - php artisan test

- **Postman Collection**
  - Import the provided Event Booking API.postman_collection.json into Postman for quick testing.

## Assumptions
- **No authentication middleware was added for brevity.**
- **Event enrichment is simulated using a mock service.**
- **Each attendee can only book one spot per event.**
- **An event has a capacity field that limits bookings.**

