# Reservations System

This project is a reservations system designed to synchronize orders. It uses the latest Symfony framework and latest MySQL. The project is containerized with Docker. The main part of the logic is stored in Command. On the first trigger, the Command will synchronize all orders and every consecutive trigger will get the newly created orders from the last Command invoke. 

## Project Overview
- **Purpose**: Synchronize and manage reservations orders.
- **Symfony Version**: 7.1.3.
- **MySQL Version**: 8.4.2
- **Containerization**: Docker

## Prerequisites
Ensure Docker and Docker Compose are installed. You can download and install them from the following links:
- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Setup
1. **Clone the Repository**
    ```bash
    git clone https://github.com/PlDevPetkov/php-task-reservation-tools.git
    cd php-task-reservation-tools
    ```

2. **Copy Environment File**
   Copy `.env.dev` to `.env` and replace the `REPLACE` placeholders with actual values
    ```bash
    cp .env.example .env.dev
    ```

3. **Build and Start the Containers**
   Build and start the project containers using Docker Compose
    ```bash
    docker compose build
    docker compose up -d
    docker compose exec php ./bin/console doctrine:database:create
    docker compose exec php ./bin/console doctrine:migrations:migrate
    ```

## Accessing the Project
Once the project is running, you can access it at: [http://localhost](http://localhost)
- **NOTE: There are not SSL Certificates, so the connection is insecure, accept and proceed**

## Running Commands
To synchronize all POS providers, execute the following command:
```bash
docker compose exec php ./bin/console app:sync-all-pos --trace --env=dev
```
- **--trace: Shows detailed activity of the task.**

## Database Tables
The application uses two primary tables:
- **commands_logs**: This table logs all command executions, including their status and any relevant details.
- **orders**: This table stores all the orders that have been synchronized from the POS providers.

## API Endpoints
List Orders: Retrieve a paginated list of orders by accessing: [http://localhost/api/orders](http://localhost/api/orders)

## Adding a New POS Provider
- **Define a new class in the src/Pos/Providers directory.**
- **Ensure this class extends AbstractProvider.**
- **Implement the getName() method to return unique provider name and retrieveOrders() method to return an array of objects containing provider_id and reservation_id.**
- **Update config/services.yaml to register the new provider in section POS Providers.**
- **Ensure the new provider is added to the $providers array in App\Pos\PosFactory dependencies.**

## Unit and Integration Testing
To run the tests, execute the following command:
```bash
docker compose exec php ./bin/phpunit
```
Tests structure is as follows:
- **Unit Tests -> tests/Unit**
- **Integration Tests -> tests/Integration**

In order to create and test Integration tests, follow these steps:
- **Create reservations_test DB by hand and grant "user" all privileges**
- **Create reservations_test DB by hand**

## Improvements
- **Automate reservations_test DB creation from Docker**
- **asd**
