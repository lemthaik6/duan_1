# Travel Tour (local dev)

This is a small PHP MVC-style application for managing travel tours.

Quick setup (Laragon / XAMPP / local Apache + PHP):

1. Place project into your webroot (e.g. `C:/laragon/www/duan_1`).
2. Import database: `document/database.sql` into MySQL, create database `travel_tour` (or update `configs/env.php`).
3. Ensure PHP extensions enabled: `pdo_mysql`, `gd`.
4. File permissions: ensure `assets/uploads/` is writable by PHP.

Optional: PHPMailer (recommended for reliable email)
- Install Composer if you don't have it: https://getcomposer.org/
- From project root run:

```powershell
composer require phpmailer/phpmailer
```

- Then set SMTP constants in `configs/env.php` (or create a local config):

```php
// Example SMTP settings (define these in env.php)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'smtp-user');
define('SMTP_PASS', 'smtp-pass');
// optional: 'ssl' or 'tls'
define('SMTP_SECURE', 'tls');
```

CSRF protection
- All important forms include a CSRF token (login/register/contact/admin forms/booking/review). 

Image uploads
- Admin can upload images for tours. Images are validated and resized; thumbnails are generated to `assets/uploads/tours/thumbs/`.

Admin user
- Create a user via registration and set role in DB:

```sql
UPDATE users SET role = 'admin' WHERE id = 1;
```

Next recommended steps
- Integrate PHPMailer + SMTP for production email.
- Add more exhaustive validation, logging and unit tests.
- Implement pagination / filters for public and admin lists.

If you want, I can now:
- Install PHPMailer and wire SMTP (requires composer),
- Add pagination and filters for tours,
- Harden uploads further (virus scanning),
- Add admin moderation for reviews and booking export.

Which would you like next?