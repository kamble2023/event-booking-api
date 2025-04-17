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
bash
git clone https://github.com/your-username/event-booking-api.git
cd event-booking-api

---

## DB settings match the Docker setup:    
- **DB_CONNECTION=mysql**
- **DB_HOST=db**
- **DB_PORT=3306**
- **DB_DATABASE=event_booking**
- **DB_USERNAME=root**
- **DB_PASSWORD=secret**

## Start Docker (Run in bash)
- **docker-compose up -d --build**

## Install Dependencies

- **docker exec -it event_booking_app composer install**
- **docker exec -it event_booking_app php artisan key:generate**
- **docker exec -it event_booking_app php artisan migrate**

## Unit Testing of the API's

- Run the command in bash **php artisan test**