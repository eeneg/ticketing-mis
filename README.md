# Ticketing System

## Overview

This repository contains a ticketing system designed to handle a variety of requests, including:

- Computer Equipment Assistance, Repairs, and Troubleshooting
- Daily Time Record (DTR) Queries
- Graphic Layout Requests
- ID Requests

The system allows users to submit tickets for these services and track their status through a user-friendly interface. Administrators can manage and resolve tickets, ensuring efficient handling of all types of requests.

## Installation

### Prerequisites
- PHP >= 8.3
- WSL for [Windows](https://learn.microsoft.com/en-us/windows/wsl/install)
- Docker
- Git
- Laravel Sail [Shell Alias](https://laravel.com/docs/11.x/sail#configuring-a-shell-alias)

### Configuration
1. **Clone the Repository**
   ```bash
   git clone https://github.com/eeneg/ticketing-mis.git ticket
   cd ticket
   ```

2. **Install Dependencies**
    ```bash
    composer install --ignore-platform-reqs
    ```

2. **Copy Environment File**
   ```bash
   cp .env.example .env
   ```

3. **Generate Application Key**
   ```
   php artisan key:generate
   ```

4. **Start the Containers**

   Ensure no conflicting ports are running.
   ```
   sail up -d
   ```
   Visit `http://localhost` to access the application.


## ER Diagram
```mermaid
erDiagram
    User }|--|| Office: belongs
    Category ||--|{ Subcategory: has
    Office ||--|{ Category: has
    Category ||--|{ Tag: has
    Subcategory ||--|{ Tag: has
    User ||--|{ Assignee: has
    User ||--|{ Request: makes
    Request ||--|{ Assignee: has
    Request ||--|{ Attachment: contains
    Request ||--|{ Action: has
    Action ||--|{ Attachment: contains
    User ||--|{ Action: responds

User {
    ulid id pk
    string number
    string email
    string position
    string role
    string avatar
    ulid office_id fk
}

Office {
    ulid id pk
    string name
    string address
    string building
    string room
}

Category {
    ulid id pk
    string name
    ulid office_id fk
}

Subcategory {
    ulid id pk
    string name
    ulid category_id fk
}

Tag {
    ulid id pk
    string name
    string taggable_type
    ulid taggable_id fk
}

Request {
    ulid id pk
    ulid category_id fk
    ulid subcategory_id fk
    ulid requestor_id fk
    text remarks
    int priority
    int difficulty
    date target_date
    time target_time
    datetime availability_from
    datetime availability_to
}

Action {
    ulid id pk
    ulid request_id fk
    ulid user_id fk
    string status
    text remarks
    datettime time
}

Assignee {
    ulid id pk
    ulid request_id fk
    ulid user_id fk
    ulid assigner_id fk
    string response
    string responded_at
}

Attachment {
    ulid id pk
    string file
    string attachable_type
    ulid attachable_id fk
}
