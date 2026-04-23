# Mini MVC REST Framework — `test_main/api/v1`

A lightweight, dependency-free PHP mini-framework that provides:

- **MVC-style structure** (Controllers / Models / Core classes)
- **RESTful routing** with URL parameters (`{id}`)
- **Bearer token authentication** (no JWT — SHA-256 hashed tokens in MySQL)
- **Middleware pipeline** per route
- **Request validation** helpers
- **JSON responses** throughout

---

## Directory layout

```
test_main/
  api/
    .htaccess                    ← redirects /api → /api/v1/public/
    v1/
      public/
        .htaccess                ← rewrites all requests to index.php
        index.php                ← front controller (only publicly exposed file)
      src/
        Bootstrap.php            ← CORS, router setup, error handling
        Config/
          config.php             ← DB credentials + auth settings
        Core/
          Database.php           ← PDO singleton
          Request.php            ← HTTP request value-object
          Response.php           ← JSON response helper
          Router.php             ← HTTP router with middleware support
          Validator.php          ← input validation helpers
        Middleware/
          AuthMiddleware.php     ← bearer-token validation middleware
        Models/
          TokenModel.php         ← issue / validate / revoke tokens
          UserModel.php          ← look up users in `clientuser` table
        Controllers/
          AuthController.php     ← POST /auth/login, POST /auth/logout
          HealthController.php   ← GET  /health  (protected example)
        Routes/
          api.php                ← route definitions
      database/
        create_api_tokens.sql    ← run once to create the token table
```

---

## Quick-start

### 1. Create the `api_tokens` table

```bash
mysql -u YOUR_USER -p YOUR_DB < test_main/api/v1/database/create_api_tokens.sql
```

### 2. Edit DB credentials

Open `test_main/api/v1/src/Config/config.php` and fill in:

```php
'dsn'  => 'mysql:host=localhost;dbname=YOUR_DB;charset=utf8mb4',
'user' => 'YOUR_DB_USER',
'pass' => 'YOUR_DB_PASS',
```

### 3. Configure Apache

Point the document root (or an `Alias`) to `test_main/api/v1/public/` and
ensure `AllowOverride All` (for `.htaccess`) is enabled:

```apache
Alias /dev/test_main/api/v1 /path/to/test_main/api/v1/public
<Directory /path/to/test_main/api/v1/public>
    AllowOverride All
    Require all granted
</Directory>
```

If the full sub-path (`/dev/test_main/api/v1`) is preserved in
`$_SERVER['REQUEST_URI']` (i.e., Apache does *not* strip it), open
`src/Routes/api.php` and confirm the `$prefix` variable matches:

```php
$prefix = '/dev/test_main/api/v1';
```

If the path *is* stripped (clean alias), set `$prefix = ''`.

---

## Authentication flow

### Login

```
POST https://dev.testsol.local/dev/test_main/api/v1/auth/login
Content-Type: application/json

{
  "username": "john",
  "password": "secret"
}
```

Successful response (200):

```json
{
  "success": true,
  "token_type": "Bearer",
  "access_token": "<64-char hex token>",
  "expires_at": "2025-04-30T11:00:00+00:00",
  "user": {
    "id": 42,
    "client_id": 7,
    "username": "john",
    "level": "admin",
    "email": "john@example.com",
    "phone": "555-1234"
  }
}
```

### Call a protected endpoint

```
GET https://dev.testsol.local/dev/test_main/api/v1/health
Authorization: Bearer <access_token>
```

### Logout

```
POST https://dev.testsol.local/dev/test_main/api/v1/auth/logout
Authorization: Bearer <access_token>
```

---

## Fix: `client_id cannot be null` (SQLSTATE[23000] / 1048)

**Root cause:**  
The `clientuser.clientId` column can be `NULL` in the database for users
that were created before the client relationship was established.  The old
code silently cast PHP `null → int 0`, which some PDO / MySQL version
combinations propagated back as `NULL` when binding, violating the
`api_tokens.client_id NOT NULL` constraint.

**Fix applied in this version:**

| File | Change |
|---|---|
| `UserModel.php` | Added an explicit `AS clientId` alias in the SELECT so the array key is always `'clientId'`, regardless of the actual column name casing in the DB. |
| `AuthController.php` | Reads `$user['clientId']`, checks it is non-null **and** `> 0` **before** calling `TokenModel::issueToken()`.  Returns a clear 422 if the account has no associated client. |
| `TokenModel.php` | Uses `bindValue()` with explicit `PDO::PARAM_INT` / `PDO::PARAM_NULL` constants instead of the `execute([...])` shorthand so the integer type is never silently coerced to SQL `NULL`. |

---

## Adding new endpoints

1. **Create a Controller** in `src/Controllers/`, e.g. `CustomerController.php`.
2. **Add routes** in `src/Routes/api.php`:

   ```php
   $router->add('GET',  $prefix . '/customers',     [CustomerController::class, 'index'], [
       [AuthMiddleware::class, 'requireToken'],
   ]);
   $router->add('POST', $prefix . '/customers',     [CustomerController::class, 'store'], [
       [AuthMiddleware::class, 'requireToken'],
   ]);
   $router->add('GET',  $prefix . '/customers/{id}',[CustomerController::class, 'show'],  [
       [AuthMiddleware::class, 'requireToken'],
   ]);
   ```

3. Inside the controller, access the authenticated context via:

   ```php
   $clientId = $req->attributes['auth']['client_id'];
   $userId   = $req->attributes['auth']['user_id'];
   ```

4. **Never** accept `client_id` from the request body for data-scoping queries;
   always use the value from `$req->attributes['auth']`.
