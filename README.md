# LavaLust Framework

> A lightweight, fast PHP framework built for developers who want clean MVC architecture without unnecessary complexity or performance overhead.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-8892BF)](https://www.php.net/)
[![GitHub Stars](https://img.shields.io/github/stars/ronmarasigan/lavalust?style=flat)](https://github.com/ronmarasigan/lavalust/stargazers)

---

## Overview

**LavaLust** is an open-source PHP framework that follows the **MVC (Model–View–Controller)** architectural pattern. It is designed for developers who need a structured, maintainable, and scalable foundation — without the bloat of heavier modern frameworks.

Whether you are building a simple web application, a REST API, or a teaching project, LavaLust provides the right tools with minimal friction.

---

## Features

| Feature | Description |
|---|---|
| **MVC Architecture** | Clean separation of Models, Views, and Controllers for organized, maintainable code |
| **Built-in Routing** | Flexible URL routing that maps requests to controllers with minimal configuration |
| **Libraries & Helpers** | Reusable components for sessions, forms, validation, and database access |
| **Modular Design** | Scalable structure that supports clean organization as your application grows |
| **REST API Support** | First-class support for building RESTful APIs using LavaLust conventions |
| **ORM-like Models** | Simplified, readable database interaction without a heavy abstraction layer |

---

## Requirements

- PHP 7.4 or higher
- A web server with URL rewriting support (Apache `.htaccess` or Nginx config)
- Composer (optional, for dependency management)

---

## Installation

**Clone the repository:**

```bash
git clone https://github.com/ronmarasigan/lavalust.git
cd lavalust
```

**Or download a release directly:**

```bash
wget https://github.com/ronmarasigan/lavalust/archive/refs/heads/main.zip
unzip main.zip
```

Configure your web server to point to the project root and ensure `mod_rewrite` (Apache) or equivalent is enabled.

---

## Quick Start

### 1. Define a Route

**File:** `app/config/routes.php`

```php
$router->get('/', 'Welcome::index');
$router->get('/about', 'Welcome::about');
$router->post('/users/store', 'Users::store');
```

### 2. Create a Controller

**File:** `app/controllers/Welcome.php`

```php
<?php

class Welcome extends Controller
{
    public function index()
    {
        $data['title'] = 'Home';
        $this->call->view('welcome', $data);
    }

    public function about()
    {
        $this->call->view('about');
    }
}
```

### 3. Create a View

**File:** `app/views/welcome.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
</head>
<body>
    <h1>Welcome to LavaLust Framework</h1>
    <p>Lightweight. Fast. MVC.</p>
</body>
</html>
```

### 4. Create a Model

**File:** `app/models/User_model.php`

```php
<?php

class User_model extends Model
{
    protected $table = 'users';

    public function getAll()
    {
        return $this->db->table($this->table)->get()->getResult();
    }

    public function findById(int $id)
    {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->get()
    }
}
```

---

## Project Structure

```
lavalust/
├── app/
│   ├── config/          # Application configuration (database, routes, etc.)
│   ├── controllers/     # Controller classes
│   ├── models/          # Model classes
│   ├── views/           # View templates
│   └── libraries/       # Custom libraries and helpers
├── scheme/              # Core framework files (do not modify)
├── public/              # Publicly accessible entry point
│   └── index.php
└── runtime/            # Cache, logs, and uploads (must be writable)
```

---

## Configuration

### Database

**File:** `app/config/database.php`

```php
$database['main'] = array(
    'driver'	=> 'mysql',
    'hostname'	=> getenv('DB_HOST') ?: '',
    'port'		=> getenv('DB_PORT') ?: '',
    'username'	=> getenv('DB_USER') ?: (getenv('DB_USERNAME') ?: ''),
    'password'	=> getenv('DB_PASSWORD') ?: '',
    'database'	=> getenv('DB_NAME') ?: '',
    'charset'	=> '',
    'dbprefix'	=> '',
    'ssl_ca'    => getenv('DB_SSL_CA') ?: '',
    // Optional for SQLite
    'path'      => ''
);
```

### Connecting to Aiven MySQL

Copy `.env.example` to `.env` and enter the host, port, database, username, and
password shown in your Aiven service's connection information. Download the
service CA certificate from Aiven and set `DB_SSL_CA` to its local file path.
Keep `.env` and the certificate private; do not commit them.

In Navicat, create a MySQL connection with the same Aiven host, port, database,
username, and password. In the connection's SSL settings, enable SSL and select
the downloaded CA certificate, then test the connection. The PHP application
uses that same CA certificate through `DB_SSL_CA`.

Set `MIGRATION_ENABLED=true` in `.env` only while running migrations in a trusted
development environment. From the project root, run `php lava migration status`
to verify the connection and migration state, then `php lava migration run`.
Do not use `rollback-all` or `refresh` on a database containing data you need.

## Product Management System (Lab 6)

This repository contains the authenticated LavaLust JSON API backed by MySQL.
The React/Vite frontend is maintained and deployed separately. The browser
talks only to this API; database credentials stay on the PHP server.

### API and authentication

The API uses LavaLust's `Api` library for JSON responses and JWT authentication.
All product endpoints require `Authorization: Bearer <access_token>`.

| Method | Endpoint | Access |
|---|---|---|
| `POST` | `/api/auth/login` | Public |
| `POST` | `/api/auth/refresh` | Public; requires a refresh token |
| `POST` | `/api/auth/logout` | Authenticated |
| `GET` | `/api/products` | Authenticated |
| `POST` | `/api/products` | Authenticated |
| `GET` | `/api/products/{id}` | Authenticated |
| `PUT` / `PATCH` | `/api/products/{id}` | Authenticated |
| `DELETE` | `/api/products/{id}` | Authenticated |

Login accepts `identity` (username or email) and `password`. Product create and
update requests use JSON fields `product_name`, `description`, `price`, and
`quantity`. The products table is created by migration
`003_create_products_table`.

### Local setup

1. Copy `.env.example` to `.env`; configure the Aiven connection values and
   `DB_SSL_CA` with the path to Aiven's downloaded CA certificate.
2. Generate two independent signing secrets and set `JWT_SECRET` and
   `REFRESH_TOKEN_KEY` in `.env` (each must be at least 32 characters):

   ```powershell
   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   ```

3. Set `MIGRATION_ENABLED=true`, run `php lava migration run` to create the
   users, refresh-token, and products tables, then set it back to `false`.
4. Create the first administrator using a trusted MySQL console. Generate a
   password hash with `php -r "echo password_hash('replace-with-a-password', PASSWORD_DEFAULT), PHP_EOL;"`,
   then insert it (replace the example values with your own):

   ```sql
   INSERT INTO users (firstname, lastname, username, email, password, role, is_active)
   VALUES ('System', 'Administrator', 'admin', 'admin@example.com', '<paste-generated-hash>', 'admin', 1);
   ```

   There is intentionally no public registration endpoint. Never store the
   plaintext password or commit the generated hash.
5. Configure and run the separate frontend repository, setting its
   `VITE_API_BASE_URL` to this API's base URL. For a frontend served from a
   different origin, set `CORS_ALLOWED_ORIGINS` in this backend's environment
   to the exact frontend origin.

### Render deployment

`render.yaml` describes the Docker-based API service. Create it from the
Blueprint, then set the Aiven `DB_HOST`, `DB_PORT`,
`DB_USER`, `DB_PASSWORD`, `DB_NAME`, and `DB_SSL_CA` environment values. If
using Aiven TLS, upload its CA certificate to Render as a secret file and set
`DB_SSL_CA` to that file's path. Set independent random values for `JWT_SECRET`
and `REFRESH_TOKEN_KEY`; set `CORS_ALLOWED_ORIGINS` to the exact HTTPS frontend
origin. Run migrations against Aiven in a trusted environment, and leave
`MIGRATION_ENABLED=false` on the deployed service. Keep all database, signing,
and certificate values in environment variables or secret files, never in Git.

Deploy the frontend separately and configure its `VITE_API_BASE_URL` to the
deployed API URL. The exercise submission still needs the owner's GitHub
repository URLs, deployed Render URLs, and working screenshots.

Database migrations are available through the LavaLust CLI only. They are not
exposed as public web routes; keep `MIGRATION_ENABLED=false` on the deployed API.

### Base URL

**File:** `app/config/config.php`

```php
$config['base_url'] = 'http://localhost:3000/';
```

---

## Building a REST API

LavaLust supports REST API development out of the box. Controllers can return JSON responses for API endpoints.

```php
<?php

class Api extends Controller
{
    $this->call->library('api');

    public function users()
    {
        $this->api->require_method('GET');
        $auth = $this->api->require_jwt();

        $this->call->model('User_model');
        $users = $this->User_model->getAll();

        $this->api->respond(['data' => $users]);
    }
}
```

Route definition:

```php
$router->get('/api/users', 'Api::users');
```

---

## Philosophy

LavaLust is built on a single principle: **minimal core, maximum control.**

Modern frameworks often add layers of abstraction that benefit large enterprise teams but get in the way of developers who want to understand exactly what their code is doing. LavaLust provides structure and utilities without hiding the underlying logic — making it an excellent choice for:

- **Rapid prototyping** — Get an application running in minutes
- **Learning MVC** — Understand how each architectural layer works
- **Lightweight production apps** — Deploy without dragging in unused dependencies
- **Teaching PHP development** — Clear conventions, readable source code

---

## Documentation

Full documentation is available at **[https://lavalust.netlify.app](https://lavalust.netlify.app)**

Topics covered include:

- Installation and server configuration
- Routing: static, dynamic, and grouped routes
- Controllers and request handling
- Models and query builder
- Views, layouts, and partials
- Built-in libraries (sessions, form validation, file upload)
- Helper functions
- REST API development
- Security best practices

---

## Contributing

Contributions are welcome. To contribute:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Commit your changes: `git commit -m "Add your feature description"`
4. Push to your branch: `git push origin feature/your-feature-name`
5. Open a pull request against `main`

Please ensure your code follows the existing style conventions and includes relevant documentation or comments where appropriate.

---

## Roadmap

- [ ] CLI tool for generating controllers, models, and migrations
- [ ] Middleware support
- [ ] Improved query builder with relationship support
- [ ] Enhanced error handling and debugging tools

---

## License

LavaLust Framework is open-source software licensed under the **[MIT License](https://opensource.org/licenses/MIT)**.

---

## Links

- **GitHub Repository:** [https://github.com/ronmarasigan/lavalust](https://github.com/ronmarasigan/lavalust)
- **Documentation:** [https://lavalust.netlify.app](https://lavalust.netlify.app)
- **Report an Issue:** [https://github.com/ronmarasigan/lavalust/issues](https://github.com/ronmarasigan/lavalust/issues)